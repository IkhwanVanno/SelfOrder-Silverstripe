<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class KeranjangPageController extends PageController
{
    private static $allowed_actions = [
        'index',
        'addToCart',
        'updateCart',
        'removeFromCart',
        'processCheckout',
        'return',
        'callback'
    ];

    private static $url_segment = 'keranjang';

    private static $url_handlers = [
        'add-to-cart' => 'addToCart',
        'update-cart' => 'updateCart',
        'remove-from-cart' => 'removeFromCart',
        'process-checkout' => 'processCheckout',
        'return' => 'return',
        'callback' => 'callback',
        '' => 'index'
    ];

    protected function init()
    {
        parent::init();
    }

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        $totalHarga = 0;
        foreach ($cartItems as $item) {
            $totalHarga += $item->getSubtotal();
        }

        // Get payment methods and convert to ArrayList for template
        $paymentMethods = $this->getPaymentMethods();
        $paymentMethodsList = new ArrayList();

        foreach ($paymentMethods as $method) {
            $paymentMethodsList->push(new ArrayData([
                'paymentMethod' => $method['paymentMethod'],
                'paymentName' => $method['paymentName'],
                'paymentImage' => isset($method['paymentImage']) ? $method['paymentImage'] : '',
                'totalFee' => $method['totalFee']
            ]));
        }

        // Get flash message if exists
        $session = $this->getRequest()->getSession();
        $flashMessage = $session->get('FlashMessage');
        if ($flashMessage) {
            $session->clear('FlashMessage');
        }

        $data = array_merge($this->getCommontData(), [
            'CartItems' => $cartItems,
            'TotalHarga' => $totalHarga,
            'PaymentMethods' => $paymentMethodsList,
            'FlashMessages' => $flashMessage ? new ArrayData($flashMessage) : null
        ]);

        return $this->customise($data)->renderWith(['KeranjangPage', 'Page']);
    }

    public function addToCart(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $produkID = $request->postVar('produk_id');
        $kuantitas = (int) $request->postVar('kuantitas') ?: 1;

        if (!$produkID) {
            return $this->redirectBack();
        }

        $user = $this->getCurrentUser();
        $produk = Produk::get()->byID($produkID);

        if (!$produk) {
            return $this->redirectBack();
        }

        $existingItem = CartItem::get()->filter([
            'MemberID' => $user->ID,
            'ProdukID' => $produkID
        ])->first();

        if ($existingItem) {
            $existingItem->Kuantitas += $kuantitas;
            $existingItem->write();
        } else {
            $cartItem = CartItem::create();
            $cartItem->MemberID = $user->ID;
            $cartItem->ProdukID = $produkID;
            $cartItem->Kuantitas = $kuantitas;
            $cartItem->write();
        }

        return $this->redirectBack();
    }

    public function updateCart(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $cartItemID = $request->postVar('cart_item_id');
        $action = $request->postVar('action');

        if (!$cartItemID || !$action) {
            return $this->redirectBack();
        }

        $user = $this->getCurrentUser();
        $cartItem = CartItem::get()->filter([
            'ID' => $cartItemID,
            'MemberID' => $user->ID
        ])->first();

        if (!$cartItem) {
            return $this->redirectBack();
        }

        if ($action === 'increase') {
            $cartItem->Kuantitas++;
            $cartItem->write();
        } elseif ($action === 'decrease') {
            if ($cartItem->Kuantitas > 1) {
                $cartItem->Kuantitas--;
                $cartItem->write();
            } else {
                $cartItem->delete();
            }
        }

        return $this->redirectBack();
    }

    public function removeFromCart(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $cartItemID = $request->postVar('cart_item_id');

        if (!$cartItemID) {
            return $this->redirectBack();
        }

        $user = $this->getCurrentUser();
        $cartItem = CartItem::get()->filter([
            'ID' => $cartItemID,
            'MemberID' => $user->ID
        ])->first();

        if (!$cartItem) {
            return $this->redirectBack();
        }

        $cartItem->delete();

        return $this->redirectBack();
    }

    public function processCheckout(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        $nomorMeja = $request->postVar('nomor_meja');
        $paymentMethod = $request->postVar('payment_method');

        if (!$nomorMeja || !$paymentMethod) {
            $this->setFlashMessage('error', 'Nomor meja dan metode pembayaran harus diisi');
            return $this->redirectBack();
        }

        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);

        if (!$cartItems->count()) {
            $this->setFlashMessage('error', 'Keranjang kosong');
            return $this->redirectBack();
        }

        // Calculate total
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->getSubtotal();
        }

        // Get payment fee
        $paymentFee = $this->getPaymentFee($paymentMethod, $subtotal);
        $totalAmount = $subtotal + $paymentFee;

        // Create order
        $order = Order::create();
        $order->MemberID = $user->ID;
        $order->TotalHarga = $totalAmount;
        $order->Status = 'Antrean';
        $order->NomorInvoice = 'INV-' . date('Ymd') . '-' . sprintf('%06d', rand(1, 999999));
        $order->NomorMeja = $nomorMeja;
        $order->write();

        // Create order items
        foreach ($cartItems as $cartItem) {
            $orderItem = OrderItem::create();
            $orderItem->OrderID = $order->ID;
            $orderItem->ProdukID = $cartItem->ProdukID;
            $orderItem->Kuantitas = $cartItem->Kuantitas;
            $orderItem->HargaSatuan = $cartItem->Produk()->Harga;
            $orderItem->write();
        }

        // Create payment
        $payment = Payment::create();
        $payment->OrderID = $order->ID;
        $payment->Reference = 'PAY-' . $order->NomorInvoice;
        $payment->TotalHarga = $totalAmount;
        $payment->Status = 'Pending';
        $payment->MetodePembayaran = $paymentMethod;
        $payment->write();

        $paymentUrl = $this->createDuitkuPayment($payment, $paymentMethod, $totalAmount, $user);

        if ($paymentUrl) {
            foreach ($cartItems as $cartItem) {
                $cartItem->delete();
            }

            return $this->redirect($paymentUrl);
        } else {
            $this->setFlashMessage('error', 'Terjadi kesalahan dalam memproses pembayaran');
            return $this->redirectBack();
        }
    }

    public function return(HTTPRequest $request)
    {
        // Handle both GET and POST parameters
        $merchantOrderId = $request->getVar('merchantOrderId') ?: $request->postVar('merchantOrderId');
        $resultCode = $request->getVar('resultCode') ?: $request->postVar('resultCode');
        $reference = $request->getVar('reference') ?: $request->postVar('reference');

        // Log untuk debugging
        error_log("Return URL Parameters:");
        error_log("merchantOrderId: " . $merchantOrderId);
        error_log("resultCode: " . $resultCode);
        error_log("reference: " . $reference);

        if ($merchantOrderId && $resultCode) {
            $payment = Payment::get()->filter('Reference', $merchantOrderId)->first();
            
            if ($payment) {
                if ($resultCode == '00') {
                    $payment->Status = 'Completed';
                    if ($reference) {
                        $payment->DuitkuTransactionID = $reference;
                    }
                    $payment->write();

                    $order = $payment->Order();
                    if ($order) {
                        $order->Status = 'Antrean';
                        $order->write();
                    }

                    $this->setFlashMessage('success', 'Pembayaran berhasil! Pesanan Anda sedang dalam antrean.');
                } else {
                    $payment->Status = 'Failed';
                    $payment->write();

                    $this->setFlashMessage('error', 'Pembayaran gagal atau dibatalkan.');
                }
            } else {
                error_log("Payment not found for reference: " . $merchantOrderId);
                $this->setFlashMessage('error', 'Data pembayaran tidak ditemukan.');
            }
        } else {
            $this->setFlashMessage('info', 'Status pembayaran tidak dapat dipastikan. Silakan periksa kembali nanti.');
        }

        return $this->redirect(Director::absoluteBaseURL());
    }

    public function callback(HTTPRequest $request)
    {
        // Log callback data for debugging
        error_log("Callback received:");
        error_log("POST data: " . json_encode($_POST));
        error_log("GET data: " . json_encode($_GET));

        // Handle both GET and POST parameters
        $merchantOrderId = $request->postVar('merchantOrderId') ?: $request->getVar('merchantOrderId');
        $resultCode = $request->postVar('resultCode') ?: $request->getVar('resultCode');
        $signature = $request->postVar('signature') ?: $request->getVar('signature');
        $reference = $request->postVar('reference') ?: $request->getVar('reference');

        if (!$merchantOrderId || !$resultCode || !$signature) {
            error_log("Missing callback parameters");
            return $this->httpError(400, 'Invalid callback data');
        }

        $apiKey = Environment::getEnv('DUITKU_API_KEY');
        $merchantCode = Environment::getEnv('DUITKU_MERCHANT_CODE');
        
        // Create expected signature
        $expectedSignature = md5($merchantCode . $merchantOrderId . $resultCode . $apiKey);

        error_log("Expected signature: " . $expectedSignature);
        error_log("Received signature: " . $signature);

        if ($signature !== $expectedSignature) {
            error_log("Invalid signature");
            return $this->httpError(400, 'Invalid signature');
        }

        $payment = Payment::get()->filter('Reference', $merchantOrderId)->first();

        if ($payment) {
            if ($resultCode == '00') {
                $payment->Status = 'Completed';
                if ($reference) {
                    $payment->DuitkuTransactionID = $reference;
                }
                $payment->write();

                $order = $payment->Order();
                if ($order) {
                    $order->Status = 'Antrean';
                    $order->write();
                }

                error_log("Payment updated to Completed for: " . $merchantOrderId);
            } else {
                $payment->Status = 'Failed';
                $payment->write();

                error_log("Payment updated to Failed for: " . $merchantOrderId);
            }
        } else {
            error_log("Payment not found for reference: " . $merchantOrderId);
        }

        return new HTTPResponse('OK', 200);
    }

    private function getPaymentMethods()
    {
        $merchantCode = Environment::getEnv('DUITKU_MERCHANT_CODE');
        $apiKey = Environment::getEnv('DUITKU_API_KEY');
        $url = Environment::getEnv('DUITKU_GETPAYMENTMETHOD_URL');

        $amount = 10000;
        $datetime = date('Y-m-d H:i:s');
        $signature = hash('sha256', $merchantCode . $amount . $datetime . $apiKey);

        $params = array(
            'merchantcode' => $merchantCode,
            'amount' => $amount,
            'datetime' => $datetime,
            'signature' => $signature
        );

        $params_string = json_encode($params);

        $url = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params_string)
            )
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            $results = json_decode($request, true);
            if (isset($results['paymentFee'])) {
                return $results['paymentFee'];
            } elseif (isset($results['paymentMethods'])) {
                return $results['paymentMethods'];
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    private function getPaymentFee($paymentMethod, $amount)
    {
        $paymentMethods = $this->getPaymentMethods();

        foreach ($paymentMethods as $method) {
            if ($method['paymentMethod'] == $paymentMethod) {
                return $method['totalFee'];
            }
        }

        return 0;
    }

    private function createDuitkuPayment($payment, $paymentMethod, $amount, $user)
    {
        $merchantCode = Environment::getEnv('DUITKU_MERCHANT_CODE');
        $apiKey = Environment::getEnv('DUITKU_API_KEY');

        $merchantOrderId = $payment->Reference;
        $productDetails = 'Pembayaran Order - ' . $payment->Order()->NomorInvoice;
        $email = $user->Email;
        $phoneNumber = '';
        $additionalParam = '';
        $merchantUserInfo = $user->FirstName . ' ' . $user->Surname;
        $customerVaName = $merchantUserInfo;
        
        // Fix URL generation - pastikan ada slash
        $baseUrl = rtrim(Director::absoluteBaseURL(), '/');
        $callbackUrl = $baseUrl . '/keranjang/callback';
        $returnUrl = $baseUrl . '/keranjang/return';
        
        $expiryPeriod = 10;

        $signature = md5($merchantCode . $merchantOrderId . $amount . $apiKey);

        $params = array(
            'merchantCode' => $merchantCode,
            'paymentAmount' => $amount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => $additionalParam,
            'merchantUserInfo' => $merchantUserInfo,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod
        );

        // Log URLs for debugging
        error_log("Callback URL: " . $callbackUrl);
        error_log("Return URL: " . $returnUrl);

        $params_string = json_encode($params);

        $url = Environment::getEnv('DUITKU_BASE_URL');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params_string)
            )
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $result = json_decode($response, true);
            if (isset($result['paymentUrl'])) {
                return $result['paymentUrl'];
            }
        }

        return false;
    }

    private function setFlashMessage($type, $message)
    {
        $session = $this->getRequest()->getSession();
        $session->set('FlashMessage', [
            'Type' => $type,
            'Message' => $message
        ]);
    }
}