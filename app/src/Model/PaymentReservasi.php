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
        'Status' => 'Status',
        'MetodePembayaran' => 'Metode Pembayaran',
        'DuitkuTransactionID' => 'Duitku Transaction ID',
        'PaymentUrl' => 'Text',
        'ExpiryTime' => 'Waktu Kadarluarsa',
        'Created' => 'Tanggal Dibuat',
        'Updated' => 'Terakhir Diupdate',
    ];
    private static $default_sort = 'Created DESC';
}