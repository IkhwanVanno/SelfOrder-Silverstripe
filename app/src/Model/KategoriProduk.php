<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class KategoriProduk extends DataObject
{
    private static $table_name = 'KategoriProduk';
    private static $db = [
        'Nama' => 'Varchar(255)',
    ];
    private static $has_many = [
        'Produks' => Produk::class,
    ];
    private static $has_one = [
        'Image' => Image::class,
    ];
    private static $owns = [
        'Image',
    ];
    private static $summary_fields = [
        'Nama' => 'Nama',
        'Image.CMSThumbnail' => 'Gambar',
    ];
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', TextField::create('Nama', 'Nama Kategori'));
        $fields->addFieldToTab('Root.Main', UploadField::create('Image', 'Gambar Kategori'));
        return $fields;
    }
}