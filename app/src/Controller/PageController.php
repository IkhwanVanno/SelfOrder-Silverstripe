<?php

namespace {

    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\Control\HTTPRequest;
    use SilverStripe\Security\Security;
    use SilverStripe\View\ArrayData;
    use SilverStripe\ORM\ArrayList;
    use SilverStripe\ORM\Queries\SQLSelect;

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
            $filter = $request->getVar('Filter');

            $produk = Produk::get();

            if ($kategoriID) {
                $produk = $produk->filter('KategoriID', $kategoriID);
            }

            if ($filter) {
                switch ($filter) {
                    case 'populer':
                        $produk = $this->getPopularProducts($produk);
                        break;
                    case 'harga_tertinggi':
                        $produk = $produk->sort('Harga DESC');
                        break;
                    case 'harga_terendah':
                        $produk = $produk->sort('Harga ASC');
                        break;
                }
            }

            $data = $this->getCommonData();
            $data['Produk'] = $produk;

            return $this->customise($data)->renderWith(['Page', 'App']);
        }

        // === Get Popular Products ===
        protected function getPopularProducts($produk)
        {
            $filteredProdukIDs = $produk->column('ID');

            if (empty($filteredProdukIDs)) {
                return $produk;
            }

            $records = OrderItem::get()->filter('ProdukID', $filteredProdukIDs);
            $produkCounts = [];

            foreach ($filteredProdukIDs as $id) {
                $produkCounts[$id] = 0;
            }

            foreach ($records as $item) {
                $produkID = $item->ProdukID;
                if (isset($produkCounts[$produkID])) {
                    $produkCounts[$produkID] += $item->Kuantitas;
                }
            }

            arsort($produkCounts);
            $sortedIDs = array_keys($produkCounts);

            if (empty($sortedIDs)) {
                return $produk;
            }

            $allProduks = $produk->filter('ID', $sortedIDs);
            $produkMap = [];

            foreach ($allProduks as $p) {
                $produkMap[$p->ID] = $p;
            }

            $sortedProduks = [];
            foreach ($sortedIDs as $id) {
                if (isset($produkMap[$id])) {
                    $sortedProduks[] = $produkMap[$id];
                }
            }

            $list = new ArrayList($sortedProduks);
            return $list;
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