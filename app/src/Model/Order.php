<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Order extends DataObject
{
    private static $table_name = 'Order';

    private static $db = [
        'TotalHarga' => 'Double',
        'TotalHargaBarang' => 'Double',
        'PaymentFee' => 'Double',
        'Status' => "Enum('Antrean,Proses,Terkirim', 'Antrean')",
        'NomorInvoice' => 'Varchar(100)',
        'NomorMeja' => 'Varchar(10)',
        'Created' => 'Datetime',
    ];

    private static $has_one = [
        'Member' => Member::class,
        'Payment' => Payment::class,
    ];

    private static $has_many = [
        'OrderItems' => OrderItem::class,
    ];

    private static $summary_fields = [
        'ID' => 'ID Order',
        'NomorMeja' => 'Nomor Meja',
        'NomorInvoice' => 'Nomor Invoice',
        'Member.Email' => 'Email Member',
        'TotalHarga' => 'Total Harga',
        'TotalHargaBarang' => 'Total Harga Barang',
        'PaymentFee' => 'Biaya Pembayaran',
        'Status' => 'Status',
        'Created' => 'Tanggal Order',
    ];

    private static $default_sort = 'Created DESC';

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Created) {
            $this->Created = date('Y-m-d H:i:s');
        }
    }
}