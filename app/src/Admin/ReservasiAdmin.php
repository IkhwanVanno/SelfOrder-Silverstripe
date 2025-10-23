<?php

use SilverStripe\Admin\ModelAdmin;

class ReservasiAdmin extends ModelAdmin
{
    private static $managed_models = [
        Reservasi::class,
    ];

    private static $url_segment = 'reservasi';

    private static $menu_title = 'Reservasi';

    private static $menu_icon_class = "font-icon-p-event-alt";
}