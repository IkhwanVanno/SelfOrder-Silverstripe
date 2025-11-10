<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class FCMToken extends DataObject
{
    private static $table_name = "FCMToken";

    private static $db = [
        "DeviceToken" => "Varchar(255)",
        "DeviceName" => "Varchar(255)",
        "LastUsed" => "Datetime"
    ];

    private static $has_one = [
        "Member" => Member::class
    ];

    private static $summary_fields = [
        "Member.Email" => "Member Email",
        "DeviceToken" => "Device Token",
        "DeviceName" => "Device Name",
        "LastUsed" => "Last Used"
    ];

    private static $indexes = [
        'DeviceToken' => true,
        'MemberID' => true
    ];
}