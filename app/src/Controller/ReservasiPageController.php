<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;

class ReservasiPageController extends PageController
{
    private static $allowed_actions = [
        'index',
        'createReservasi',
        'cancel',
        'download',
        'sendEmail',
        'payment',
        'callback',
        'returnUrl',
    ];

    private static $url_segment = 'reservasi';

    private static $url_handlers = [
        'create' => 'createReservasi',
        'cancel/$ID' => 'cancel',
        'download/$ID' => 'download',
        'send-email/$ID' => 'sendEmail',
        'payment/$ID' => 'payment',
        'callback' => 'callback',
        'return' => 'returnUrl',
        '' => 'index'
    ];

    private $reservasiService;
    private $paymentService;

    protected function init()
    {
        parent::init();
        $this->reservasiService = new ReservasiService();
        $this->paymentService = new PaymentService();

        // Check expired reservations and payments
        $this->reservasiService->checkExpiredReservations();
        $this->paymentService->checkExpiredPaymentReservasi();
    }

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('primary', 'Silahkan login terlebih dahulu.');
            return $this->redirect(Director::absoluteBaseURL() . 'auth/login');
        }

        $user = Security::getCurrentUser();
        $userReservations = $this->reservasiService->getUserReservations($user->ID);

        // Get flash message
        $session = $this->getRequest()->getSession();
        $flashMessage = $session->get('FlashMessage');
        if ($flashMessage) {
            $session->clear('FlashMessage');
        }

        // Prepare payment methods for modal
        $siteConfig = SiteConfig::current_site_config();
        $defaultAmount = $siteConfig->BiayaReservasi ?: 50000;
        $paymentMethods = $this->paymentService->getPaymentMethods($defaultAmount);
        $paymentMethodsList = new ArrayList();

        foreach ($paymentMethods as $method) {
            $paymentMethodsList->push(new ArrayData([
                'paymentMethod' => $method['paymentMethod'],
                'paymentName' => $method['paymentName'],
                'paymentImage' => $method['paymentImage'] ?? '',
                'totalFee' => $method['totalFee']
            ]));
        }

        $data = array_merge($this->getCommonData(), [
            'UserReservations' => $userReservations,
            'SiteConfig' => $siteConfig,
            'PaymentMethods' => $paymentMethodsList,
            'FlashMessage' => $flashMessage ? new ArrayData($flashMessage) : null
        ]);

        return $this->customise($data)->renderWith(['ReservasiPage', 'Page']);
    }

    // Create new reservation
    public function createReservasi(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('danger', 'Silahkan login terlebih dahulu.');
            return $this->redirectBack();
        }

        if (!$request->isPOST()) {
            return $this->redirectBack();
        }

        $data = [
            'NamaReservasi' => $request->postVar('nama_reservasi'),
            'JumlahKursi' => $request->postVar('jumlah_kursi'),
            'WaktuMulai' => $request->postVar('waktu_mulai'),
            'WaktuSelesai' => $request->postVar('waktu_selesai'),
            'Catatan' => $request->postVar('catatan')
        ];

        // Validasi input
        if (
            empty($data['NamaReservasi']) || empty($data['JumlahKursi']) ||
            empty($data['WaktuMulai']) || empty($data['WaktuSelesai'])
        ) {
            $this->setFlashMessage('danger', 'Semua field wajib diisi.');
            return $this->redirectBack();
        }

        $result = $this->reservasiService->createReservasi($data);

        if ($result['success']) {
            $this->setFlashMessage('success', $result['message']);
        } else {
            $this->setFlashMessage('danger', $result['message']);
        }

        return $this->redirectBack();
    }

    // Cancel reservation
    public function cancel(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('danger', 'Silahkan login terlebih dahulu.');
            return $this->redirectBack();
        }

        $reservasiID = $request->param('ID');

        if (!$reservasiID) {
            $this->setFlashMessage('danger', 'ID Reservasi tidak ditemukan.');
            return $this->redirectBack();
        }

        $result = $this->reservasiService->cancelReservasi($reservasiID);

        if ($result['success']) {
            $this->setFlashMessage('success', $result['message']);
        } else {
            $this->setFlashMessage('danger', $result['message']);
        }

        return $this->redirectBack();
    }

    // Download PDF Receipt
    public function download(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . 'auth/login');
        }

        $reservasiID = $request->param('ID');

        if (!$reservasiID) {
            $this->setFlashMessage('danger', 'ID Reservasi tidak ditemukan.');
            return $this->redirectBack();
        }

        $result = $this->reservasiService->downloadReceipt($reservasiID);

        if (!$result['success']) {
            $this->setFlashMessage('danger', $result['message']);
            return $this->redirectBack();
        }

        $response = HTTPResponse::create();
        $response->addHeader('Content-Type', 'application/pdf');
        $response->addHeader('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"');
        $response->setBody($result['content']);

        return $response;
    }

    // Send Email Receipt
    public function sendEmail(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('danger', 'Silahkan login terlebih dahulu.');
            return $this->redirectBack();
        }

        $reservasiID = $request->param('ID');

        if (!$reservasiID) {
            $this->setFlashMessage('danger', 'ID Reservasi tidak ditemukan.');
            return $this->redirectBack();
        }

        $reservasi = Reservasi::get()->byID($reservasiID);
        $user = Security::getCurrentUser();

        if (!$reservasi || $reservasi->MemberID != $user->ID) {
            $this->setFlashMessage('danger', 'Reservasi tidak ditemukan.');
            return $this->redirectBack();
        }

        if ($reservasi->Status != 'Selesai') {
            $this->setFlashMessage('danger', 'Email tanda terima hanya dapat dikirim untuk reservasi yang sudah selesai.');
            return $this->redirectBack();
        }

        $result = $this->reservasiService->sendReservationReceipt($reservasi);

        if ($result['success']) {
            $this->setFlashMessage('success', $result['message']);
        } else {
            $this->setFlashMessage('danger', $result['message']);
        }

        return $this->redirectBack();
    }

    // Process Payment
    public function payment(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            $this->setFlashMessage('danger', 'Silahkan login terlebih dahulu.');
            return $this->redirectBack();
        }

        if (!$request->isPOST()) {
            return $this->redirectBack();
        }

        $reservasiID = $request->param('ID');
        $paymentMethod = $request->postVar('payment_method');

        if (!$reservasiID || !$paymentMethod) {
            $this->setFlashMessage('danger', 'Data tidak lengkap.');
            return $this->redirectBack();
        }

        $reservasi = Reservasi::get()->byID($reservasiID);
        $user = Security::getCurrentUser();

        if (!$reservasi || $reservasi->MemberID != $user->ID) {
            $this->setFlashMessage('danger', 'Reservasi tidak ditemukan.');
            return $this->redirectBack();
        }

        if ($reservasi->Status != 'Disetujui') {
            $this->setFlashMessage('danger', 'Reservasi belum disetujui atau sudah dibayar.');
            return $this->redirectBack();
        }

        $paymentFee = $this->paymentService->getPaymentFee($paymentMethod, $reservasi->TotalHarga);
        $totalAmount = $reservasi->TotalHarga + $paymentFee;

        $payment = PaymentReservasi::create();
        $payment->Reference = 'RSV-' . date('YmdHis') . '-' . $reservasi->ID;
        $payment->TotalHarga = $totalAmount;
        $payment->MetodePembayaran = $this->paymentService->getPaymentMethodName($paymentMethod);
        $payment->Status = 'Pending';
        $payment->write();

        $reservasi->PaymentReservasiID = $payment->ID;
        $reservasi->Status = 'MenungguPembayaran';
        $reservasi->write();

        $paymentUrl = $this->paymentService->createDuitkuPaymentReservasi(
            $payment,
            $paymentMethod,
            $reservasi->TotalHarga,
            $user,
            $reservasi
        );

        if (!$paymentUrl) {
            $this->setFlashMessage('danger', 'Gagal membuat pembayaran. Silakan coba lagi.');
            return $this->redirectBack();
        }

        return $this->redirect($paymentUrl);
    }

    // Callback dari Duitku setelah pembayaran
    public function callback(HTTPRequest $request)
    {
        $merchantCode = $request->postVar('merchantCode') ?? $request->getVar('merchantCode');
        $amount = $request->postVar('amount') ?? $request->getVar('amount');
        $merchantOrderId = $request->postVar('merchantOrderId') ?? $request->getVar('merchantOrderId');
        $resultCode = $request->postVar('resultCode') ?? $request->getVar('resultCode');
        $reference = $request->postVar('reference') ?? $request->getVar('reference');
        $signature = $request->postVar('signature') ?? $request->getVar('signature');

        if (!$merchantCode || !$amount || !$merchantOrderId || !$signature) {
            return new HTTPResponse('Bad Parameter', 400);
        }

        $apiKey = Environment::getEnv('DUITKU_API_KEY');
        $expectedSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);

        if ($signature !== $expectedSignature) {
            return new HTTPResponse('Bad Signature', 400);
        }

        $payment = PaymentReservasi::get()->filter('Reference', $merchantOrderId)->first();

        if (!$payment) {
            return new HTTPResponse('Payment not found', 404);
        }

        if ($resultCode == '00') {
            $payment->Status = 'Completed';
            $payment->DuitkuTransactionID = $reference ?? '';
            $payment->write();

            $reservasi = $payment->Reservasi();
            if ($reservasi) {
                $reservasi->Status = 'Selesai';
                $reservasi->write();

                $this->reservasiService->sendReservationReceipt($reservasi);
            }
        } else {
            $payment->Status = 'Failed';
            $payment->write();

            $reservasi = $payment->Reservasi();
            if ($reservasi) {
                $reservasi->Status = 'Dibatalkan';
                $reservasi->ResponsAdmin = 'Pembayaran gagal atau dibatalkan';
                $reservasi->write();
            }
        }

        return new HTTPResponse('OK', 200);
    }

    // Return URL setelah pembayaran dari Duitku
    public function returnUrl(HTTPRequest $request)
    {
        $merchantOrderId = $request->getVar('merchantOrderId');
        $resultCode = $request->getVar('resultCode');

        if (!$merchantOrderId) {
            $this->setFlashMessage('danger', 'Data pembayaran tidak valid.');
            return $this->redirect($this->Link());
        }

        $payment = PaymentReservasi::get()->filter('Reference', $merchantOrderId)->first();

        if (!$payment) {
            $this->setFlashMessage('danger', 'Pembayaran tidak ditemukan.');
            return $this->redirect($this->Link());
        }

        if ($resultCode == '00') {
            $this->setFlashMessage('success', 'Pembayaran berhasil! Reservasi Anda telah dikonfirmasi. Tanda terima telah dikirim ke email Anda.');
        } else {
            $this->setFlashMessage('warning', 'Pembayaran sedang diproses. Silakan tunggu konfirmasi atau cek email Anda.');
        }

        return $this->redirect($this->Link());
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