<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class CartItem extends DataObject
{
    private static $table_name = 'CartItem';
    private static $db = [
        'Kuantitas' => 'Int',
    ];
    private static $has_one = [
        'Member' => Member::class,
    ];
    private static $has_many = [
        'Produks' => Produk::class,
    ];
    private static $summary_fields = [
        'Produk.Nama' => 'Nama Produk',
        'Kuantitas' => 'Kuantitas',
        'Produk.Harga' => 'Harga Satuan',
        'Member.Email' => 'Email Member',
    ];
}