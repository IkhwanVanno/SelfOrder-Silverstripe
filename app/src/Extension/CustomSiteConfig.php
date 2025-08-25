<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

class CustomSiteConfig extends DataExtension
{
    private static $table_name = 'CustomSiteConfig';
    private static $db = [
        "Email" => "Varchar(255)",
        "Phone" => "Varchar(20)",
        "Address" => "Text",
        "CompanyName" => "Varchar(255)",
        "Credit" => "Varchar(255)",
    ];
    private static $has_one = [
        'Logo' => Image::class,
    ];
    private static $owns = [
        'Logo',
    ];
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', TextField::create('CompanyName', 'Nama Perusahaan'));
        $fields->addFieldToTab('Root.Main', TextField::create('Email', 'Email Perusahaan'));
        $fields->addFieldToTab('Root.Main', TextField::create('Phone', 'Nomor Telepon Perusahaan'));
        $fields->addFieldToTab('Root.Main', TextField::create('Address', 'Alamat Perusahaan'));
        $fields->addFieldToTab('Root.Main', TextField::create('Credit', 'Credit Footer'));
        $fields->addFieldToTab('Root.Main', UploadField::create('Logo', 'Logo Perusahaan'));
    }
}