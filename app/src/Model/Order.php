<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\View\Requirements;

class Order extends DataObject
{
    private static $table_name = 'Order';

    private static $db = [
        'TotalHarga' => 'Double',
        'TotalHargaBarang' => 'Double',
        'PaymentFee' => 'Double',
        'Status' => "Enum('MenungguPembayaran,Dibatalkan,Antrean,Proses,Terkirim', 'Antrean')",
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
        'NomorMeja' => 'Meja',
        'NomorInvoice' => 'Invoice',
        'MemberName' => 'Nama Pemesan',
        'ItemList' => 'Daftar Pesanan',
        'TotalHarga' => 'Total Harga',
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

    public function getMemberName()
    {
        return $this->Member()->FirstName . ' ' . $this->Member()->Surname;
    }

    public function getItemList()
    {
        $items = $this->OrderItems();
        if ($items->count() === 0)
            return '-';

        $result = [];
        foreach ($items as $item) {
            $result[] = "{$item->Kuantitas}x {$item->Produk()->Nama}";
        }

        return implode(', ', $result);
    }

}