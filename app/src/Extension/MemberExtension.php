<?php

use SilverStripe\ORM\DataExtension;

class MemberExtension extends DataExtension
{
    private static $table_name = 'MemberExtension';
    private static $db = [
        'ResetPasswordToken' => 'Varchar(255)',
        'ResetPasswordExpiry' => 'Datetime',
    ];
}