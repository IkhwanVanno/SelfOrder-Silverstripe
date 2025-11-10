<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Reservasi extends DataObject
{
    private static $table_name = 'Reservasi';

    private static $db = [
        'NamaReservasi' => 'Varchar(255)',
        'JumlahKursi' => 'Int',
        'TotalHarga' => 'Double',
        'WaktuMulai' => 'Datetime',
        'WaktuSelesai' => 'Datetime',
        'Status' => "Enum('MenungguPersetujuan,Disetujui,Ditolak,MenungguPembayaran,Selesai,Dibatalkan','MenungguPersetujuan')",
        'Catatan' => 'Text',
        'ResponsAdmin' => 'Text',
    ];

    private static $has_one = [
        'PaymentReservasi' => PaymentReservasi::class,
        'Member' => Member::class,
    ];

    private static $summary_fields = [
        'NamaReservasi' => 'Nama Reservasi',
        'Member.Email' => 'Email Pemesan',
        'JumlahKursi' => 'Jumlah Kursi',
        'TotalHarga' => 'Total Harga Reservasi',
        'WaktuMulai' => 'Waktu Mulai',
        'WaktuSelesai' => 'Waktu Selesai',
        'Status' => 'Status Reservasi',
        'Catatan' => 'Tambahan',
    ];

    private static $default_sort = 'Created DESC';

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->isChanged('Status')) {
            $member = $this->Member();

            if (!$member || !$member->exists()) {
                return;
            }

            $tokens = FCMToken::get()
                ->filter('MemberID', $member->ID)
                ->column('DeviceToken');

            if (!empty($tokens)) {
                try {
                    $fcm = new FCMService();
                    $title = "Status Reservasi Diperbarui";
                    $body = $this->getNotificationMessage();
                    $data = [
                        'type' => 'reservation',
                        'reservation_id' => (string) $this->ID,
                        'status' => $this->Status,
                        'nama_reservasi' => $this->NamaReservasi
                    ];

                    $fcm->sendToDevices($tokens, $title, $body, $data);
                } catch (Exception $e) {
                    error_log('FCM notification failed: ' . $e->getMessage());
                }
            }
        }
    }

    private function getNotificationMessage()
    {
        $statusMessages = [
            'MenungguPersetujuan' => 'Reservasi Anda sedang menunggu persetujuan admin.',
            'Disetujui' => 'Reservasi Anda telah disetujui! Silakan lakukan pembayaran.',
            'Ditolak' => 'Mohon maaf, reservasi Anda ditolak.',
            'MenungguPembayaran' => 'Silakan selesaikan pembayaran reservasi Anda.',
            'Selesai' => 'Reservasi Anda telah selesai. Terima kasih!',
            'Dibatalkan' => 'Reservasi Anda telah dibatalkan.'
        ];

        $message = $statusMessages[$this->Status] ?? 'Status reservasi Anda: ' . $this->Status;
        if ($this->ResponsAdmin) {
            $message .= ' Pesan admin: ' . $this->ResponsAdmin;
        }

        return $message;
    }

    public function getFormattedTotal()
    {
        return 'Rp ' . number_format($this->TotalHarga, 0, ',', '.');
    }

    public function getFormattedWaktuMulai()
    {
        if ($this->WaktuMulai) {
            $date = new DateTime($this->WaktuMulai);
            return $date->format('d/m/Y H:i');
        }
        return '-';
    }

    public function getFormattedWaktuSelesai()
    {
        if ($this->WaktuSelesai) {
            $date = new DateTime($this->WaktuSelesai);
            return $date->format('d/m/Y H:i');
        }
        return '-';
    }

    public function getStatusLabel()
    {
        $labels = [
            'MenungguPersetujuan' => 'Menunggu Persetujuan',
            'Disetujui' => 'Disetujui',
            'Ditolak' => 'Ditolak',
            'MenungguPembayaran' => 'Menunggu Pembayaran',
            'Selesai' => 'Selesai',
            'Dibatalkan' => 'Dibatalkan'
        ];

        return $labels[$this->Status] ?? $this->Status;
    }

    public function getStatusColor()
    {
        $colors = [
            'MenungguPersetujuan' => 'yellow',
            'Disetujui' => 'green',
            'Ditolak' => 'red',
            'MenungguPembayaran' => 'blue',
            'Selesai' => 'green',
            'Dibatalkan' => 'gray'
        ];

        return $colors[$this->Status] ?? 'gray';
    }
}