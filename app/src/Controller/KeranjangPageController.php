<?php

use SilverStripe\Control\HTTPRequest;

class KeranjangPageController extends PageController
{
    private static $allowed_actions = [
        'index'
    ];
    private static $url_segment = 'keranjang';
    private static $url_handlers = [
        '' => 'index'
    ];
    protected function init()
    {
        parent::init();
        // You can include any CSS or JS required for the keranjang page here
    }
    public function index(HTTPRequest $request)
    {
        $data = $this->getCommontData();
        return $this->customise($data)->renderWith(['KeranjangPage', 'Page']);
    }
}