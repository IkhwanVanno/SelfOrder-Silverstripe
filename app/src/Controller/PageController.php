<?php

namespace {

    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\Control\HTTPRequest;
    use SilverStripe\Security\Security;

    /**
     * @template T of Page
     * @extends ContentController<T>
     */
    class PageController extends ContentController
    {
        /**
         * An array of actions that can be accessed via a request. Each array element should be an action name, and the
         * permissions or conditions required to allow the user to access it.
         *
         * <code>
         * [
         *     'action', // anyone can access this action
         *     'action' => true, // same as above
         *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
         *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
         * ];
         * </code>
         *
         * @var array
         */
        private static $allowed_actions = [
            'index',
        ];

        protected function getCommontData()
        {
            return [
                'SiteConfig' => $this->SiteConfig,
                'KategoriProduk' => KategoriProduk::get(),
                'Produk' => Produk::get(),
                'CartItem' => CartItem::get(),
                'Order' => Order::get(),
                'OrderItem' => OrderItem::get(),
                'Payment' => Payment::get(),
            ];
        }

        protected function init()
        {
            parent::init();
            // You can include any CSS or JS required by your project here.
            // See: https://docs.silverstripe.org/en/developer_guides/templates/requirements/
        }

        public function index(HTTPRequest $request)
        {
            $kategoriID = $request->getVar('Kategori');

            $produk = Produk::get();

            if ($kategoriID) {
                $produk = $produk->filter('KategoriID', $kategoriID);
            }

            $data = $this->getCommontData();
            $data['Produk'] = $produk;

            return $this->customise($data)->renderWith(['Page', 'App']);
        }

        // Authentication related methods can be added here as well
        protected function getCurrentUser()
        {
            return Security::getCurrentUser();
        }
        protected function isLoggedIn()
        {
            return Security::getCurrentUser() !== null;
        }
    }
}
