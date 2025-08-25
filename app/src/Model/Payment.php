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
    private static $has_many = [
        'Order' => Order::class,
    ];
    private static $summary_fields = [
    ];
}