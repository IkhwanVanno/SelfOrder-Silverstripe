<?php

use Dompdf\Dompdf;
use Dompdf\Options;
use SilverStripe\Control\Email\Email;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

class ReservasiService
{
    // Menghitung total harga reservasi berdasarkan durasi (per jam)
    public function calculateTotalPrice($waktuMulai, $waktuSelesai)
    {
        $siteConfig = SiteConfig::current_site_config();
        $biayaPerJam = $siteConfig->BiayaReservasi ?: 50000;

        $start = new DateTime($waktuMulai);
        $end = new DateTime($waktuSelesai);

        $diff = $start->diff($end);
        $totalJam = $diff->h + ($diff->days * 24);

        // diBulatkan
        if ($diff->i > 0) {
            $totalJam += 1;
        }

        return $totalJam * $biayaPerJam;
    }

    // Create Reservasi
    public function createReservasi($data)
    {
        $user = Security::getCurrentUser();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan. Silakan login terlebih dahulu.'
            ];
        }

        // Validasi waktu
        $waktuMulai = new DateTime($data['WaktuMulai']);
        $waktuSelesai = new DateTime($data['WaktuSelesai']);
        $now = new DateTime();

        if ($waktuMulai < $now) {
            return [
                'success' => false,
                'message' => 'Waktu mulai tidak boleh kurang dari waktu sekarang.'
            ];
        }

        if ($waktuSelesai <= $waktuMulai) {
            return [
                'success' => false,
                'message' => 'Waktu selesai harus lebih besar dari waktu mulai.'
            ];
        }

        // Hitung total harga
        $totalHarga = $this->calculateTotalPrice($data['WaktuMulai'], $data['WaktuSelesai']);

        // Buat reservasi
        try {
            $reservasi = Reservasi::create();
            $reservasi->NamaReservasi = $data['NamaReservasi'];
            $reservasi->JumlahKursi = $data['JumlahKursi'];
            $reservasi->WaktuMulai = $data['WaktuMulai'];
            $reservasi->WaktuSelesai = $data['WaktuSelesai'];
            $reservasi->TotalHarga = $totalHarga;
            $reservasi->Catatan = $data['Catatan'] ?? '';
            $reservasi->Status = 'MenungguPersetujuan';
            $reservasi->MemberID = $user->ID;
            $reservasi->write();

            return [
                'success' => true,
                'message' => 'Reservasi berhasil dibuat. Menunggu persetujuan admin.',
                'reservasi' => $reservasi
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    // Cencel Reservasi
    public function cancelReservasi($reservasiID)
    {
        $reservasi = Reservasi::get()->byID($reservasiID);
        $user = Security::getCurrentUser();

        if (!$reservasi) {
            return [
                'success' => false,
                'message' => 'Reservasi tidak ditemukan.'
            ];
        }

        if ($reservasi->MemberID != $user->ID) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk membatalkan reservasi ini.'
            ];
        }

        if (!in_array($reservasi->Status, ['MenungguPersetujuan', 'Disetujui', 'MenungguPembayaran'])) {
            return [
                'success' => false,
                'message' => 'Reservasi tidak dapat dibatalkan.'
            ];
        }

        try {
            $reservasi->Status = 'Dibatalkan';
            $reservasi->ResponsAdmin = 'Dibatalkan oleh pelanggan pada ' . date('d/m/Y H:i');
            $reservasi->write();

            // Jika ada payment, batalkan juga
            if ($reservasi->PaymentReservasiID) {
                $payment = $reservasi->PaymentReservasi();
                if ($payment && $payment->Status == 'Pending') {
                    $payment->Status = 'Failed';
                    $payment->write();
                }
            }

            return [
                'success' => true,
                'message' => 'Reservasi berhasil dibatalkan.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    // Generate Reservasi Number
    public function generateReservationNumber()
    {
        $prefix = 'RSV';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        return $prefix . '-' . $date . '-' . $random;
    }

    // Send Reservasi Email Recipt
    public function sendReservationReceipt($reservasi)
    {
        $user = $reservasi->Member();
        $siteConfig = SiteConfig::current_site_config();
        $companyEmail = $siteConfig->Email;

        if (!$companyEmail) {
            return [
                'success' => false,
                'message' => 'Email perusahaan tidak dikonfigurasi.'
            ];
        }

        try {
            $emailData = $this->prepareReceiptData($reservasi, $user, $siteConfig);

            $email = Email::create()
                ->setFrom($companyEmail)
                ->setTo($user->Email)
                ->setSubject('Tanda Terima Reservasi - ' . $reservasi->NamaReservasi)
                ->setHTMLTemplate('ReservasiReciptEmail');

            // Attach logo jika ada
            if ($siteConfig->Logo && $siteConfig->Logo->exists()) {
                $logoName = $siteConfig->Logo->Name;
                $fullLogoPath = BASE_PATH . '/public/assets/Uploads/' . $logoName;

                if (file_exists($fullLogoPath)) {
                    $logoData = file_get_contents($fullLogoPath);
                    $imageInfo = getimagesize($fullLogoPath);
                    $logoMimeType = $imageInfo['mime'] ?? 'image/png';
                    $logoExtension = pathinfo($logoName, PATHINFO_EXTENSION);
                    $logoFilename = 'company-logo.' . $logoExtension;

                    $email->addAttachmentFromData(
                        $logoData,
                        $logoFilename,
                        $logoMimeType
                    );
                    $emailData->setField('LogoCID', 'cid:' . $logoFilename);
                }
            }

            $email->setData($emailData);
            $email->send();

            return [
                'success' => true,
                'message' => 'Email tanda terima berhasil dikirim.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ];
        }
    }

    // Generate PDF Reservasi Recipt
    public function generateReceiptPDF($reservasi, $user, $siteConfig)
    {
        $pdfData = $this->prepareReceiptData($reservasi, $user, $siteConfig);

        $viewer = SSViewer::create(['ReservasiReciptPDF']);
        $htmlContent = $viewer->process($pdfData);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    // Download PDF Reservasi Receipt
    public function downloadReceipt($reservasiID)
    {
        $reservasi = Reservasi::get()->byID($reservasiID);
        $user = Security::getCurrentUser();

        if (!$reservasi) {
            return [
                'success' => false,
                'message' => 'Reservasi tidak ditemukan.'
            ];
        }

        if ($reservasi->MemberID != $user->ID) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengunduh tanda terima ini.'
            ];
        }

        if ($reservasi->Status != 'Selesai') {
            return [
                'success' => false,
                'message' => 'Tanda terima hanya tersedia untuk reservasi yang sudah selesai.'
            ];
        }

        try {
            $siteConfig = SiteConfig::current_site_config();
            $pdfContent = $this->generateReceiptPDF($reservasi, $user, $siteConfig);

            return [
                'success' => true,
                'content' => $pdfContent,
                'filename' => 'Tanda-Terima-Reservasi-' . $reservasi->ID . '.pdf'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal membuat PDF: ' . $e->getMessage()
            ];
        }
    }

    // Prepare Receipt Email & PDF Data
    private function prepareReceiptData($reservasi, $user, $siteConfig)
    {
        $reservationNumber = $this->generateReservationNumber();

        // Format tanggal dan waktu
        $waktuMulai = new DateTime($reservasi->WaktuMulai);
        $waktuSelesai = new DateTime($reservasi->WaktuSelesai);

        $reservationDate = $waktuMulai->format('d/m/Y');
        $startTime = $waktuMulai->format('d/m/Y H:i');
        $endTime = $waktuSelesai->format('d/m/Y H:i');

        // Format currency
        $formattedTotal = 'Rp ' . number_format($reservasi->TotalHarga, 0, ',', '.');

        // Status label
        $statusLabels = [
            'MenungguPersetujuan' => 'Menunggu Persetujuan',
            'Disetujui' => 'Disetujui',
            'Ditolak' => 'Ditolak',
            'MenungguPembayaran' => 'Menunggu Pembayaran',
            'Selesai' => 'Selesai',
            'Dibatalkan' => 'Dibatalkan'
        ];

        $data = [
            'ReservationNumber' => $reservationNumber,
            'ReservationName' => $reservasi->NamaReservasi,
            'ReservationDate' => $reservationDate,
            'StartTime' => $startTime,
            'EndTime' => $endTime,
            'JumlahKursi' => $reservasi->JumlahKursi,
            'TotalHarga' => $reservasi->TotalHarga,
            'FormattedTotal' => $formattedTotal,
            'Status' => $statusLabels[$reservasi->Status] ?? $reservasi->Status,
            'Catatan' => $reservasi->Catatan,
            'ResponsAdmin' => $reservasi->ResponsAdmin,
            'CustomerName' => trim($user->FirstName . ' ' . $user->Surname),
            'CustomerEmail' => $user->Email,
            'CompanyName' => $siteConfig->Title ?: 'Perusahaan Kami',
            'CompanyEmail' => $siteConfig->Email,
            'CompanyAddress' => $siteConfig->Address,
        ];

        if ($siteConfig->Logo && $siteConfig->Logo->exists()) {
            $data['Logo'] = $siteConfig->Logo;
            $data['LogoURL'] = $siteConfig->Logo->getAbsoluteURL();
        }

        return new ArrayData($data);
    }

    // Get Reservasi by member
    public function getUserReservations($userID = null)
    {
        if (!$userID) {
            $user = Security::getCurrentUser();
            $userID = $user ? $user->ID : null;
        }

        if (!$userID) {
            return [];
        }

        return Reservasi::get()
            ->filter(['MemberID' => $userID])
            ->sort('Created DESC');
    }

    // Cek expired reservations yang belum dibayar
    public function checkExpiredReservations()
    {
        $now = date('Y-m-d H:i:s');

        // Cek reservasi yang waktu mulainya sudah lewat tapi statusnya masih MenungguPembayaran
        $expiredReservations = Reservasi::get()->filter([
            'Status' => 'MenungguPembayaran' || 'MenungguPersetujuan',
            'WaktuMulai:LessThan' => $now
        ]);

        foreach ($expiredReservations as $reservasi) {
            $reservasi->Status = 'Dibatalkan';
            $reservasi->ResponsAdmin = 'Reservasi dibatalkan otomatis karena pembayaran/persetujuan melewati waktu mulai pada ' . date('d/m/Y H:i');
            $reservasi->write();

            // Update payment jika ada
            if ($reservasi->PaymentReservasiID) {
                $payment = $reservasi->PaymentReservasi();
                if ($payment && $payment->Status == 'Pending') {
                    $payment->Status = 'Failed';
                    $payment->write();
                }
            }
        }

        return count($expiredReservations);
    }
}