<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class Produk extends DataObject
{
    private static $table_name = 'Produk';
    private static $db = [
        'Nama' => 'Varchar(255)',
        'Deskripsi' => 'Text',
        'Stok' => 'Int',
        'Harga' => 'Double',
        'Is_Active' => 'Boolean',

    ];
    private static $has_one = [
        'Kategori' => KategoriProduk::class,
        'CartItem' => CartItem::class,
        'Image' => Image::class,
    ];
    private static $owns = [
        'Image',
    ];
    private static $summary_fields = [
        'Image.CMSThumbnail' => 'Gambar',
        'Kategori.Nama' => 'Kategori',
        'Nama' => 'Nama',
        'Deskripsi' => 'Deskripsi',
        'Stok' => 'Stok',
        'Harga' => 'Harga',
        'Is_Active' => 'Aktif',
    ];
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', UploadField::create('Image', 'Gambar Produk'));
        $fields->addFieldToTab('Root.Main', DropdownField::create('KategoriID', 'Kategori', KategoriProduk::get()->map('ID', 'Nama')));
        $fields->addFieldToTab('Root.Main', TextField::create('Nama', 'Nama Produk'));
        $fields->addFieldToTab('Root.Main', TextField::create('Deskripsi', 'Deskripsi Produk'));
        $fields->addFieldToTab('Root.Main', TextField::create('Stok', 'Stok Produk'));
        $fields->addFieldToTab('Root.Main', TextField::create('Harga', 'Harga Produk'));
        $fields->addFieldToTab('Root.Main', TextField::create('Is_Active', 'Status Produk (1=Aktif, 0=Nonaktif)'));
        return $fields;
    }
}