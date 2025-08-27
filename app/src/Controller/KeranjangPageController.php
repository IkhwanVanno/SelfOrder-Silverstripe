<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\ArrayList;
use SilverStripe\SiteConfig\SiteConfig;
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
        'callback',
        'downloadInvoice',
        'sendInvoice',
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

    private $paymentService;
    private $emailService;

    protected function init()
    {
        parent::init();
        $this->paymentService = new PaymentService();
        $this->emailService = new EmailService();
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

        // Get payment methods
        $paymentMethods = $this->paymentService->getPaymentMethods($totalHarga);
        $paymentMethodsList = new ArrayList();

        foreach ($paymentMethods as $method) {
            $paymentMethodsList->push(new ArrayData([
                'paymentMethod' => $method['paymentMethod'],
                'paymentName' => $method['paymentName'],
                'paymentImage' => $method['paymentImage'] ?? '',
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

        // Hitung subtotal dari item keranjang
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->getSubtotal();
        }

        // Hitung biaya tambahan metode pembayaran
        $paymentFee = $this->paymentService->getPaymentFee($paymentMethod, $subtotal);
        $totalAmount = $subtotal + $paymentFee;

        // Buat Order
        $order = Order::create();
        $order->MemberID = $user->ID;
        $order->TotalHarga = $totalAmount;
        $order->Status = 'Antrean';
        $order->NomorInvoice = 'INV-' . date('Ymd') . '-' . sprintf('%06d', rand(1, 999999));
        $order->NomorMeja = $nomorMeja;
        $order->write();

        // Buat Order Items berdasarkan Cart Items
        foreach ($cartItems as $cartItem) {
            $orderItem = OrderItem::create();
            $orderItem->OrderID = $order->ID;
            $orderItem->ProdukID = $cartItem->ProdukID;
            $orderItem->Kuantitas = $cartItem->Kuantitas;
            $orderItem->HargaSatuan = $cartItem->Produk()->Harga;
            $orderItem->write();
        }

        // Buat Payment
        $payment = Payment::create();
        $payment->OrderID = $order->ID;
        $payment->Reference = 'PAY-' . $order->NomorInvoice;
        $payment->TotalHarga = $totalAmount;
        $payment->Status = 'Pending';
        $payment->MetodePembayaran = $paymentMethod;
        $payment->write();

        $order->PaymentID = $payment->ID;
        $order->write();

        $paymentUrl = $this->paymentService->createDuitkuPayment($payment, $paymentMethod, $totalAmount, $user);

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
        $merchantOrderId = $request->getVar('merchantOrderId') ?: $request->postVar('merchantOrderId');
        $resultCode = $request->getVar('resultCode') ?: $request->postVar('resultCode');
        $reference = $request->getVar('reference') ?: $request->postVar('reference');

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
                        $this->emailService->sendInvoiceEmail($order);
                    }

                    $this->setFlashMessage('success', 'Pembayaran berhasil! Pesanan Anda sedang dalam antrean.');
                } else {
                    $payment->Status = 'Failed';
                    $payment->write();
                    $this->setFlashMessage('error', 'Pembayaran gagal atau dibatalkan.');
                }
            } else {
                $this->setFlashMessage('error', 'Data pembayaran tidak ditemukan.');
            }
        } else {
            $this->setFlashMessage('info', 'Status pembayaran tidak dapat dipastikan. Silakan periksa kembali nanti.');
        }

        return $this->redirect(Director::absoluteBaseURL());
    }

    public function callback(HTTPRequest $request)
    {
        $merchantOrderId = $request->postVar('merchantOrderId') ?: $request->getVar('merchantOrderId');
        $resultCode = $request->postVar('resultCode') ?: $request->getVar('resultCode');
        $signature = $request->postVar('signature') ?: $request->getVar('signature');
        $reference = $request->postVar('reference') ?: $request->getVar('reference');

        if (!$merchantOrderId || !$resultCode || !$signature) {
            return $this->httpError(400, 'Invalid callback data');
        }

        $apiKey = Environment::getEnv('DUITKU_API_KEY');
        $merchantCode = Environment::getEnv('DUITKU_MERCHANT_CODE');
        $expectedSignature = md5($merchantCode . $merchantOrderId . $resultCode . $apiKey);

        if ($signature !== $expectedSignature) {
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
                    $this->emailService->sendInvoiceEmail($order);
                }
            } else {
                $payment->Status = 'Failed';
                $payment->write();
            }
        }

        return new HTTPResponse('OK', 200);
    }

    public function downloadInvoice(HTTPRequest $request)
    {
        $orderID = $request->param('ID');
        $order = Order::get()->byID($orderID);

        if (!$order) {
            return $this->httpError(404, 'Order tidak ditemukan');
        }

        $user = $order->Member();
        $siteConfig = SiteConfig::current_site_config();
        $pdfContent = $this->emailService->generateInvoicePDF($order, $user, $siteConfig);
        $this->getRequest()->getSession()->set('FlashMessage', [
            'Type' => 'success',
            'Message' => 'Invoice berhasil diunduh.'
        ]);
        return HTTPResponse::create($pdfContent)
            ->addHeader('Content-Type', 'application/pdf')
            ->addHeader('Content-Disposition', 'attachment; filename="Invoice-' . $order->NomorInvoice . '.pdf"');
    }

    public function sendInvoice(HTTPRequest $request)
    {
        $orderID = $request->param('ID');
        $order = Order::get()->byID($orderID);

        if (!$order) {
            return $this->httpError(404, 'Order tidak ditemukan');
        }

        $this->emailService->sendInvoiceEmail($order);
        $this->getRequest()->getSession()->set('FlashMessage', [
            'Type' => 'success',
            'Message' => 'Email invoice telah dikirim. Silakan periksa kotak Email anda.'
        ]);
        return $this->redirectBack();
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