<?php

use SilverStripe\Admin\ModelAdmin;

class PaymentAdmin extends ModelAdmin
{
    private static $managed_models = [
        Payment::class,
    ];

    private static $url_segment = 'payments';

    private static $menu_title = 'Payments';

    private static $menu_icon_class = "font-icon-credit-card";
}