<?php

use SilverStripe\Admin\ModelAdmin;

class KategoriProdukAdmin extends ModelAdmin
{
    private static $managed_models = [
        KategoriProduk::class,
    ];

    private static $url_segment = 'kategoriproduk';

    private static $menu_title = 'Kategori Produk';

    private static $menu_icon_class = 'font-icon-list';
}