<?php

use SilverStripe\Admin\ModelAdmin;

class ProdukAdmin extends ModelAdmin
{
    private static $managed_models = [
        Produk::class,
    ];

    private static $url_segment = 'produk';

    private static $menu_title = 'Produk';

    private static $menu_icon_class = 'font-icon-list';
}