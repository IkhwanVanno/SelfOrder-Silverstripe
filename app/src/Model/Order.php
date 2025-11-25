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
        'Status' => "Enum('MenungguPembayaran,Dibatalkan,Antrean,Proses,Terkirim', 'Antrean')",
        'NomorInvoice' => 'Varchar(100)',
        'NomorMeja' => 'Varchar(10)',
        'Created' => 'Datetime',
        'InvoiceSent' => 'Boolean'
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

    private static $searchable_fields = [
        'NomorMeja' => [
            'title' => 'Nomor Meja',
            'filter' => 'PartialMatchFilter'
        ],
        'NomorInvoice' => [
            'title' => 'Nomor Invoice',
            'filter' => 'PartialMatchFilter'
        ],
        'Status' => [
            'title' => 'Status',
            'filter' => 'ExactMatchFilter'
        ],
        'Created' => [
            'title' => 'Tanggal Order - Hingga Sekarang',
            'filter' => 'GreaterThanOrEqualFilter',
        ]
    ];

    private static $default_sort = 'Created DESC';

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->isChanged('Status')) {
            $member = $this->Member();

            if (!$member || !$member->exists()) {
                return;
            }

            $tokens = FCMToken::get()
                ->filter('MemberID', $member->ID)
                ->column('DeviceToken');

            if (!empty($tokens)) {
                try {
                    $fcm = new FCMService();
                    $title = "Status Order Diperbarui";
                    $body = $this->getNotificationMessage();
                    $data = [
                        'type' => 'order',
                        'order_id' => $this->ID,
                        'status' => $this->Status,
                        'nomor_invoice' => $this->NomorInvoice
                    ];

                    $fcm->sendToDevices($tokens, $title, $body, $data);
                } catch (Exception $e) {
                    error_log('FCM notification failed: ' . $e->getMessage());
                }
            }
        }
    }

    public function getNotificationMessage()
    {
        $statusMessages = [
            'MenungguPembayaran' => 'Menunggu pembayaran Anda.',
            'Dibatalkan' => 'Pesanan Anda telah dibatalkan.', 
            'Antrean'=> 'Pesanan Anda sedang dalam antrean.',
            'Proses' => 'Pesanan Anda sedang diproses.',
            'Terkirim'=> 'Pesanan Anda sedang menuju meja anda',
        ];

        $message = $statusMessages[$this->Status] ?? 'Status order Anda: ' . $this->Status;
        return $message;
    }

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