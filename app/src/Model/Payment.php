<?php

use SilverStripe\ORM\DataObject;

class Payment extends DataObject
{
    private static $table_name = 'Payment';

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
        'Order' => Order::class,
    ];

    private static $summary_fields = [
        'Reference' => 'Reference',
        'Order.NomorInvoice' => 'Nomor Invoice',
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

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Created) {
            $this->Created = date('Y-m-d H:i:s');
        }

        $this->Updated = date('Y-m-d H:i:s');
    }
    public function getIsExpired()
    {
        if (!$this->ExpiryTime) {
            return false;
        }

        return strtotime($this->ExpiryTime) < time();
    }
}