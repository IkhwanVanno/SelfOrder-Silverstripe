<?php

use SilverStripe\ORM\DataObject;

class Reservasi extends DataObject
{
    private static $table_name = 'Reservasi';
    private static $db = [
        'NamaReservasi' => 'Varchar(255)',
        'JumlahKursi' => 'Int',
        'TotalHarga' => 'Double',
        'WaktuMulai' => 'Datetime',
        'waktuSelesai' => 'Datetime',
        'Status' => "Enum('MenungguPersetujuan,Disetujui,Ditolak,MenungguPembayaran,Selesai,Dibatalkan','MenungguPersetujuan')",
        'Catatan' => 'Text',
        'ResponsAdmin' => 'Text',
    ];
    private static $has_one = [
        'PaymentReservasi' => PaymentReservasi::class
    ];
    private static $summary_fields = [
        'NamaReservasi' => 'Nama Reservasi',
        'JumlahKursi' => 'Jumlah Kursi',
        'TotalHarga' => 'Total Harga Reservasi',
        'WaktuMulai' => 'Waktu Mulai',
        'waktuSelesai' => 'Waktu Selesai',
        'Status' => 'Status Reservasi',
        'Catatan' => 'Tambahan',
    ];
}