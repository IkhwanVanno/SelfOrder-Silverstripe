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
        'waktuSelesai' => 'Datetime',
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