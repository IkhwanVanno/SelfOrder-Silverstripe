<?php

use SilverStripe\Admin\ModelAdmin;

class PaymentReservasiAdmin extends ModelAdmin
{
    private static $managed_models = [
        PaymentReservasi::class,
    ];

    private static $url_segment = 'paymentreservasi';

    private static $menu_title = 'Payment Reservasi';

    private static $menu_icon_class = "font-icon-credit-card";
}