<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Order extends DataObject
{
    private static $table_name = 'Order';
    private static $db = [
        'TotalHarga' => 'Double',
        'Status' => "Enum('Antrean, Proses, Terkirim', 'Antrean')",
        'NomorInvoice' => 'Varchar(100)',
        'NomorMeja' => 'Varchar(10)',
    ];
    private static $has_one = [
        'Member' => Member::class,
    ];
    private static $has_many = [
        'OrderItems' => OrderItem::class,
        'Payments' => Payment::class,
    ];
    private static $summary_fields = [
        'ID' => 'ID Order',
        'NomorMeja' => 'Nomor Meja',
        'NomorInvoice' => 'Nomor Invoice',
        'Member.Email' => 'Email Member',
        'TotalHarga' => 'Total Harga',
        'Status' => 'Status',
    ];
}