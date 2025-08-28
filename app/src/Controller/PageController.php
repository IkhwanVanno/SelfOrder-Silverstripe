<?php

namespace {

    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\Control\HTTPRequest;
    use SilverStripe\Security\Security;
    use SilverStripe\View\ArrayData;

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

        protected function getCommonData()
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

        // === Flash message handling ===
        protected $flashMessages = null;

        public function getFlashMessages()
        {
            $session = $this->getRequest()->getSession();
            $flash = $session->get('FlashMessage');

            if ($flash) {
                $session->clear('FlashMessage');
                return ArrayData::create($flash);
            }

            return null;
        }

        protected function init()
        {
            parent::init();
        }

        // === HOME PAGE ===
        public function index(HTTPRequest $request)
        {
            $kategoriID = $request->getVar('Kategori');

            $produk = Produk::get()->filter('Is_Active', 1);

            if ($kategoriID) {
                $produk = $produk->filter('KategoriID', $kategoriID);
            }

            $data = $this->getCommonData();
            $data['Produk'] = $produk;

            return $this->customise($data)->renderWith(['Page', 'App']);
        }

        // === USER AUTHENTICATION HELPERS ===
        protected function getCurrentUser()
        {
            return Security::getCurrentUser();
        }

        protected function isLoggedIn()
        {
            return Security::getCurrentUser() !== null;
        }

        // === CART HELPERS ===
        public function getCartQuantity($produkID)
        {
            if (!$this->isLoggedIn()) {
                return 0;
            }

            $user = $this->getCurrentUser();
            $cartItem = CartItem::get()->filter([
                'MemberID' => $user->ID,
                'ProdukID' => $produkID
            ])->first();

            return $cartItem ? $cartItem->Kuantitas : 0;
        }
    }
}