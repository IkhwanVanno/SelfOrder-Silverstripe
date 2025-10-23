<?php

use SilverStripe\ORM\DataObject;

class PaymentReservasi extends DataObject
{
    private static $table_name = 'PaymentReservasi';

    private static $db = [
        'Reference' => 'Varchar(255)',
        'TotalHarga' => 'Double',
        'Status' => "Enum('Pending,Completed,Failed', 'Pending')",
        'MetodePembayaran' => 'Varchar(100)',
        'DuitkuTransactionID' => 'Varchar(255)',
        'PaymentUrl' => 'Text',
        'ExpiryTime' => 'Datetime',
        'Created' => 'Datetime',
        'Updated' => 'Datetime',
    ];

    private static $belongs_to = [
        'Reservasi' => Reservasi::class
    ];

    private static $summary_fields = [
        'Reference' => 'Reference',
        'Reservasi.NamaReservasi' => 'Nama Reservasi',
        'TotalHarga' => 'Total Harga',
        'FormattedTotal' => 'Total Harga (Formatted)',
        'Status' => 'Status',
        'MetodePembayaran' => 'Metode Pembayaran',
        'DuitkuTransactionID' => 'Duitku Transaction ID',
        'ExpiryTime' => 'Waktu Kadaluarsa',
        'Created' => 'Tanggal Dibuat',
        'Updated' => 'Terakhir Diupdate',
    ];

    private static $default_sort = 'Created DESC';

    private static $searchable_fields = [
        'Reference',
        'Status',
        'MetodePembayaran',
        'DuitkuTransactionID'
    ];

    public function getFormattedTotal()
    {
        return 'Rp ' . number_format($this->TotalHarga, 0, ',', '.');
    }

    public function getFormattedExpiryTime()
    {
        if ($this->ExpiryTime) {
            $date = new DateTime($this->ExpiryTime);
            return $date->format('d/m/Y H:i');
        }
        return '-';
    }

    public function isExpired()
    {
        if (!$this->ExpiryTime) {
            return false;
        }

        $now = new DateTime();
        $expiry = new DateTime($this->ExpiryTime);

        return $now > $expiry && $this->Status == 'Pending';
    }

    public function getStatusColor()
    {
        $colors = [
            'Pending' => 'yellow',
            'Completed' => 'green',
            'Failed' => 'red'
        ];

        return $colors[$this->Status] ?? 'gray';
    }

    public function getStatusLabel()
    {
        $labels = [
            'Pending' => 'Menunggu Pembayaran',
            'Completed' => 'Selesai',
            'Failed' => 'Gagal'
        ];

        return $labels[$this->Status] ?? $this->Status;
    }
}