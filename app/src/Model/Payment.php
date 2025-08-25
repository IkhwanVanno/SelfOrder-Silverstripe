<?php

use SilverStripe\ORM\DataObject;

class Payment extends DataObject
{
    private static $table_name = 'Payment';
    private static $db = [
        'Reference' => 'Varchar(255)',
        'TotalHarga' => 'Double',
        'Status' => "Enum('Pending, Completed, Failed', 'Pending')",
        'MetodePembayaran' => 'Varchar(100)',
        'DuitkuTransactionID' => 'Varchar(255)',
    ];
    private static $has_one = [
        'Order' => Order::class,
    ];
    private static $summary_fields = [
        'Reference' => 'Reference',
        'Order.NomorInvoice' => 'Nomor Invoice',
        'TotalHarga' => 'Total Harga',
        'Status' => 'Status',
        'MetodePembayaran' => 'Metode Pembayaran',
        'DuitkuTransactionID' => 'Duitku Transaction ID',
    ];
}