<?php

use SilverStripe\ORM\DataObject;

class OrderItem extends DataObject
{
    private static $table_name = 'OrderItem';

    private static $db = [
        'Kuantitas' => 'Int',
        'HargaSatuan' => 'Double',
    ];

    private static $has_one = [
        'Order' => Order::class,
        'Produk' => Produk::class,
    ];

    private static $summary_fields = [
        'Order.ID' => 'ID Order',
        'Produk.Nama' => 'Nama Produk',
        'Kuantitas' => 'Kuantitas',
        'HargaSatuan' => 'Harga Satuan',
        'FormattedSubtotal' => 'Subtotal'
    ];

    public function getSubtotal()
    {
        return $this->Kuantitas * $this->HargaSatuan;
    }

    public function getFormattedSubtotal()
    {
        return number_format($this->getSubtotal(), 0, ',', '.');
    }

    public function getFormattedHargaSatuan()
    {
        return number_format($this->HargaSatuan, 0, ',', '.');
    }
}