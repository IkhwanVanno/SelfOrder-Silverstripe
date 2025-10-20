<?php

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Security;
use SilverStripe\Security\Member;
use SilverStripe\Security\IdentityStore;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SiteConfig\SiteConfig;

class RestfulAPIController extends Controller
{
    private static $url_segment = 'api';

    private static $allowed_actions = [
        'index',
        'login',
        'register',
        'logout',
        'googleAuth',
        'member',
        'updateMember',
        'updatePassword',
        'siteconfig',
        'products',
        'product',
        'categories',
        'cart',
        'cartItem',
        'orders',
        'order',
        'createOrder',
        'payment',
        'paymentmethods',
        'downloadInvoiceAPI',
        'sendInvoiceAPI',
        'forgotpassword',
    ];

    private static $url_handlers = [
        'login' => 'login',
        'register' => 'register',
        'logout' => 'logout',
        'google-auth' => 'googleAuth',
        'member/password' => 'updatePassword',
        'member' => 'member',
        'siteconfig' => 'siteconfig',
        'products/$ID!' => 'product',
        'products' => 'products',
        'categories' => 'categories',
        'cart/$ID!' => 'cartItem',
        'cart' => 'cart',
        'orders/$ID/pdf' => 'downloadInvoiceAPI',
        'orders/$ID/send-email' => 'sendInvoiceAPI',
        'orders/$ID!' => 'order',
        'orders' => 'orders',
        'payment' => 'payment',
        'paymentmethods' => 'paymentmethods',
        'forgotpassword' => 'forgotpassword',
        '' => 'index',
    ];

    private $authService;
    private $paymentService;
    private $emailService;

    protected function init()
    {
        parent::init();
        $this->authService = new AuthService();
        $this->paymentService = new PaymentService();
        $this->emailService = new EmailService();
    }

    public function index(HTTPRequest $request)
    {
        return $this->jsonResponse([
            'message' => 'SilverStripe Self-Order API',
            'status' => 'operational',
            'endpoints' => [
                'authentication' => [
                    'POST /api/google-auth' => 'Firebase Google Auth',
                    'POST /api/login' => 'Login user',
                    'POST /api/register' => 'Register new user',
                    'POST /api/logout' => 'Logout current user',
                ],
                'member' => [
                    'GET /api/member' => 'Get current member profile',
                    'PUT /api/member' => 'Update member profile',
                    'PUT /api/member/password' => 'Update member password',
                ],
                'siteconfig' => [
                    'GET /api/siteconfig' => 'Get siteconfig data'
                ],
                'products' => [
                    'GET /api/products' => 'Get all products',
                    'GET /api/products?category_id=x' => 'Get products by category',
                    'GET /api/products?filter=populer' => 'Get products sorted by popularity',
                    'GET /api/products?filter=harga_terendah' => 'Get products sorted by lowest price',
                    'GET /api/products?filter=harga_tertinggi' => 'Get products sorted by highest price',
                    'GET /api/products?category_id=x&filter=harga_terendah' => 'Combine category and price filter',
                    'GET /api/products/{id}' => 'Get single product',
                ],
                'categories' => [
                    'GET /api/categories' => 'Get all categories',
                ],
                'cart' => [
                    'GET /api/cart' => 'Get cart items',
                    'POST /api/cart' => 'Add item to cart',
                    'PUT /api/cart/{id}' => 'Update cart item',
                    'DELETE /api/cart/{id}' => 'Remove cart item',
                ],
                'orders' => [
                    'GET /api/orders' => 'Get all orders',
                    'GET /api/orders/{id}' => 'Get single order',
                    'GET /api/orders/{id}/pdf' => 'Download PDF Invoice',
                    'POST /api/orders' => 'Create new order',
                    'POST /api/orders/{id}/send-email' => 'Send Invoice to Email User',
                ],
                'payment' => [
                    'POST /api/payment' => 'Create payment',
                ],
                'paymentmethods' => [
                    'POST /api/paymentmethods' => 'Get payment methods',
                ],
                'forgotpassword' => [
                    'POST /api/forgotpassword' => 'Forgot password'
                ],
            ],
        ]);
    }

    // ========== AUTHENTICATION ==========
    // * GOOGLE AUTH *
    // * Firebase *
    public function googleAuth(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $data = json_decode($request->getBody(), true);

        if (!isset($data['email'])) {
            return $this->jsonResponse(['error' => 'Email is required'], 400);
        }

        try {
            $email = $data['email'];
            $displayName = $data['display_name'] ?? '';
            $photoUrl = $data['photo_url'] ?? '';

            $nameParts = explode(' ', $displayName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            $member = Member::get()->filter('Email', $email)->first();

            if (!$member) {
                $member = Member::create();
                $member->FirstName = $firstName;
                $member->Surname = $lastName;
                $member->Email = $email;
                $member->IsVerified = true;
                $member->write();
                $member->addToGroupByCode('site-users');
                $member->changePassword(bin2hex(random_bytes(16)));
            } else {
                if (!$member->IsVerified) {
                    $member->IsVerified = true;
                    $member->write();
                }
            }

            Injector::inst()->get(IdentityStore::class)->logIn($member, false);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Google login successful',
                'user' => [
                    'id' => $member->ID,
                    'email' => $member->Email,
                    'first_name' => $member->FirstName,
                    'surname' => $member->Surname,
                ]
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Google authentication failed'], 500);
        }
    }

    // * MANUAL AUTH *
    public function login(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Gunakan metode POST'], 405);
        }

        $data = json_decode($request->getBody(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->jsonResponse(['error' => 'Email dan password diperlukan'], 400);
        }

        $member = Member::get()->filter('Email', $data['email'])->first();

        if (!$member || !password_verify($data['password'], $member->Password)) {
            return $this->jsonResponse(['error' => 'Email atau password salah'], 401);
        }

        // Login berhasil: set current user dan buat session
        Injector::inst()->get(IdentityStore::class)->logIn($member, false);

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => [
                'id' => $member->ID,
                'email' => $member->Email,
                'nama' => $member->FirstName . ' ' . $member->Surname,
            ]
        ]);
    }

    public function register(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $data = json_decode($request->getBody(), true);

        if (!isset($data['email'], $data['password'], $data['first_name'])) {
            return $this->jsonResponse(['error' => 'Email, password, and first name are required'], 400);
        }

        if (strlen($data['password']) < 8) {
            return $this->jsonResponse(['error' => 'Password must be at least 8 characters'], 400);
        }

        if (Member::get()->filter('Email', $data['email'])->exists()) {
            return $this->jsonResponse(['error' => 'Email already registered'], 400);
        }

        try {
            $member = Member::create();
            $member->FirstName = $data['first_name'];
            $member->Surname = $data['surname'] ?? '';
            $member->Email = $data['email'];
            $member->write();
            $member->addToGroupByCode('site-users');
            $member->changePassword($data['password']);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'id' => $member->ID,
                    'email' => $member->Email,
                    'first_name' => $member->FirstName,
                    'surname' => $member->Surname,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return $this->jsonResponse(['error' => 'Registration failed. Please try again.'], 500);
        }
    }

    public function logout(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = Security::getCurrentUser();

        if (!$member) {
            return $this->jsonResponse(['error' => 'Not logged in'], 401);
        }

        Injector::inst()->get(IdentityStore::class)->logOut($request);

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    // ========== Data Model/Extension ==========
    // * MEMBER *
    public function member(HTTPRequest $request)
    {
        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        if ($request->isGET()) {
            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'id' => $member->ID,
                    'email' => $member->Email,
                    'first_name' => $member->FirstName,
                    'surname' => $member->Surname,
                ]
            ]);
        }

        if ($request->isPUT()) {
            $data = json_decode($request->getBody(), true);

            if (isset($data['first_name'])) {
                $member->FirstName = $data['first_name'];
            }
            if (isset($data['surname'])) {
                $member->Surname = $data['surname'];
            }
            if (isset($data['email'])) {
                $existingMember = Member::get()
                    ->filter('Email', $data['email'])
                    ->exclude('ID', $member->ID)
                    ->first();

                if ($existingMember) {
                    return $this->jsonResponse(['error' => 'Email already in use'], 400);
                }
                $member->Email = $data['email'];
            }

            try {
                $member->write();
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => [
                        'id' => $member->ID,
                        'email' => $member->Email,
                        'first_name' => $member->FirstName,
                        'surname' => $member->Surname,
                    ]
                ]);
            } catch (ValidationException $e) {
                return $this->jsonResponse(['error' => 'Failed to update profile'], 500);
            }
        }

        return $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }

    public function updatePassword(HTTPRequest $request)
    {
        if (!$request->isPUT()) {
            return $this->jsonResponse(['error' => 'Only PUT method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $data = json_decode($request->getBody(), true);

        if (!isset($data['new_password'])) {
            return $this->jsonResponse(['error' => 'New password is required'], 400);
        }

        if (strlen($data['new_password']) < 8) {
            return $this->jsonResponse(['error' => 'New password must be at least 8 characters'], 400);
        }

        try {
            $member->Password = $data['new_password'];
            $member->write();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);
        } catch (ValidationException $e) {
            return $this->jsonResponse(['error' => 'Password update failed'], 400);
        }
    }

    // * SITECONFIG *
    public function siteconfig(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $siteconfig = SiteConfig::current_site_config();
        if (!$siteconfig) {
            return $this->jsonResponse(['error' => 'SiteConfig not found'], 404);
        }

        $data = [
            'title' => $siteconfig->Title,
            'tagline' => $siteconfig->Tagline,
            'email' => $siteconfig->Email,
            'phone' => $siteconfig->Phone,
            'address' => $siteconfig->Address,
            'companyname' => $siteconfig->CompanyName,
            'credit' => $siteconfig->Credit,
            'logo_url' => $siteconfig->Logo()->exists() ? $siteconfig->Logo()->getAbsoluteURL() : null,
        ];

        return $this->jsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    // * PRODUCTS *
    public function products(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $categoryId = $request->getVar('category_id');
        $filter = $request->getVar('filter');
        $page = (int) $request->getVar('page') ?: 1;
        $limit = (int) $request->getVar('limit') ?: 6;

        $products = Produk::get();
        if ($categoryId) {
            $products = $products->filter('KategoriID', $categoryId);
        }

        if ($filter) {
            switch ($filter) {
                case 'harga_tertinggi':
                    $products = $products->sort('Harga DESC');
                    break;
                case 'harga_terendah':
                    $products = $products->sort('Harga ASC');
                    break;
                case 'populer':
                    $productIds = $products->column('ID');

                    if (!empty($productIds)) {
                        $records = OrderItem::get()->filter('ProdukID', $productIds);
                        $produkCounts = [];

                        foreach ($productIds as $id) {
                            $produkCounts[$id] = 0;
                        }

                        foreach ($records as $item) {
                            $produkID = $item->ProdukID;
                            if (isset($produkCounts[$produkID])) {
                                $produkCounts[$produkID] += $item->Kuantitas;
                            }
                        }

                        arsort($produkCounts);
                        $sortedIds = array_keys($produkCounts);
                        $products = $products->filter('ID', $sortedIds);
                    }
                    break;
            }
        }

        $total = $products->count();
        $offset = ($page - 1) * $limit;
        $pageProducts = $products->limit($limit, $offset);

        $data = [];
        foreach ($pageProducts as $product) {
            $data[] = [
                'id' => $product->ID,
                'nama' => $product->Nama,
                'deskripsi' => $product->Deskripsi,
                'harga' => $product->Harga,
                'status' => $product->Status,
                'kategori_id' => $product->KategoriID,
                'kategori_nama' => $product->Kategori()->Nama,
                'image_url' => $product->Image()->exists() ? $product->Image()->getAbsoluteURL() : null,
            ];
        }

        $totalPages = ceil($total / $limit);

        return $this->jsonResponse([
            'success' => true,
            'message' => "Daftar produk halaman $page",
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                '$total_pages' => $totalPages,
            ]
        ]);
    }

    public function product(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $id = $request->param('ID');
        $product = Produk::get()->byID($id);

        if (!$product) {
            return $this->jsonResponse(['error' => 'Product not found'], 404);
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'id' => $product->ID,
                'nama' => $product->Nama,
                'deskripsi' => $product->Deskripsi,
                'harga' => $product->Harga,
                'status' => $product->Status,
                'kategori_id' => $product->KategoriID,
                'kategori_nama' => $product->Kategori()->Nama,
                'image_url' => $product->Image()->exists() ? $product->Image()->getAbsoluteURL() : null,
            ]
        ]);
    }

    // * CATEGORIES *
    public function categories(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $categories = KategoriProduk::get();
        $data = [];

        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->ID,
                'nama' => $category->Nama,
                'image_url' => $category->Image()->exists() ? $category->Image()->getAbsoluteURL() : null,
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    // * CART *
    public function cart(HTTPRequest $request)
    {
        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        if ($request->isGET()) {
            $cartItems = CartItem::get()->filter('MemberID', $member->ID);
            $data = [];
            $totalHarga = 0;

            foreach ($cartItems as $item) {
                $subtotal = $item->getSubtotal();
                $totalHarga += $subtotal;

                $data[] = [
                    'id' => $item->ID,
                    'produk_id' => $item->ProdukID,
                    'produk_nama' => $item->Produk()->Nama,
                    'produk_harga' => $item->Produk()->Harga,
                    'produk_image_url' => $item->Produk()->Image()->exists() ? $item->Produk()->Image()->getAbsoluteURL() : null,
                    'kuantitas' => $item->Kuantitas,
                    'subtotal' => $subtotal,
                ];
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $data,
                'total_harga' => $totalHarga
            ]);
        }

        if ($request->isPOST()) {
            $data = json_decode($request->getBody(), true);

            if (!isset($data['produk_id'])) {
                return $this->jsonResponse(['error' => 'Product ID is required'], 400);
            }

            $produk = Produk::get()->byID($data['produk_id']);
            if (!$produk) {
                return $this->jsonResponse(['error' => 'Product not found'], 404);
            }

            $kuantitas = $data['kuantitas'] ?? 1;

            $existingItem = CartItem::get()->filter([
                'MemberID' => $member->ID,
                'ProdukID' => $data['produk_id']
            ])->first();

            if ($existingItem) {
                $existingItem->Kuantitas += $kuantitas;
                $existingItem->Kuantitas = max(1, $existingItem->Kuantitas);
                $existingItem->write();
                $cartItem = $existingItem;
            } else {
                $cartItem = CartItem::create();
                $cartItem->MemberID = $member->ID;
                $cartItem->ProdukID = $data['produk_id'];
                $cartItem->Kuantitas = $kuantitas;
                $cartItem->write();
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => [
                    'id' => $cartItem->ID,
                    'produk_id' => $cartItem->ProdukID,
                    'produk_nama' => $cartItem->Produk()->Nama,
                    'produk_harga' => $cartItem->Produk()->Harga,
                    'produk_image_url' => $cartItem->Produk()->Image()->exists() ?
                        $cartItem->Produk()->Image()->getAbsoluteURL() : null,
                    'kuantitas' => $cartItem->Kuantitas,
                    'subtotal' => $cartItem->getSubtotal(),
                ]
            ], 201);
        }

        return $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }

    public function cartItem(HTTPRequest $request)
    {
        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $id = $request->param('ID');
        $cartItem = CartItem::get()->filter([
            'ID' => $id,
            'MemberID' => $member->ID
        ])->first();

        if (!$cartItem) {
            return $this->jsonResponse(['error' => 'Cart item not found'], 404);
        }

        if ($request->isPUT()) {
            $data = json_decode($request->getBody(), true);

            if (isset($data['kuantitas'])) {
                if ($data['kuantitas'] < 1) {
                    return $this->jsonResponse(['error' => 'Quantity must be at least 1'], 400);
                }
                $cartItem->Kuantitas = $data['kuantitas'];
                $cartItem->write();
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Cart item updated',
                'data' => [
                    'id' => $cartItem->ID,
                    'produk_id' => $cartItem->ProdukID,
                    'produk_nama' => $cartItem->Produk()->Nama,
                    'produk_harga' => $cartItem->Produk()->Harga,
                    'produk_image_url' => $cartItem->Produk()->Image()->exists() ?
                        $cartItem->Produk()->Image()->getAbsoluteURL() : null,
                    'kuantitas' => $cartItem->Kuantitas,
                    'subtotal' => $cartItem->getSubtotal(),
                ]
            ]);
        }

        if ($request->isDELETE()) {
            $cartItem->delete();
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Cart item removed'
            ]);
        }

        return $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }

    // * ORDERS *
    public function orders(HTTPRequest $request)
    {
        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        if ($request->isGET()) {
            $this->paymentService->checkExpiredPayments();

            $orders = Order::get()
                ->filter('MemberID', $member->ID)
                ->sort('Created', 'DESC');

            $data = [];
            foreach ($orders as $order) {
                $orderItems = [];
                foreach ($order->OrderItems() as $item) {
                    $orderItems[] = [
                        'id' => $item->ID,
                        'produk_id' => $item->ProdukID,
                        'produk_nama' => $item->Produk()->Nama,
                        'kuantitas' => $item->Kuantitas,
                        'harga_satuan' => $item->HargaSatuan,
                        'subtotal' => $item->getSubtotal(),
                    ];
                }

                $data[] = [
                    'id' => $order->ID,
                    'nomor_invoice' => $order->NomorInvoice,
                    'nomor_meja' => $order->NomorMeja,
                    'total_harga_barang' => $order->TotalHargaBarang,
                    'payment_fee' => $order->PaymentFee,
                    'total_harga' => $order->TotalHarga,
                    'status' => $order->Status,
                    'created' => $order->Created,
                    'items' => $orderItems,
                    'payment' => $order->Payment()->exists() ? [
                        'id' => $order->Payment()->ID,
                        'reference' => $order->Payment()->Reference,
                        'metode_pembayaran' => $order->Payment()->MetodePembayaran,
                        'status' => $order->Payment()->Status,
                        'paymenturl' => $order->Payment()->PaymentUrl,
                    ] : null
                ];
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $data
            ]);
        }

        if ($request->isPOST()) {
            $data = json_decode($request->getBody(), true);

            if (!isset($data['nomor_meja'], $data['payment_method'])) {
                return $this->jsonResponse(['error' => 'Table number and payment method are required'], 400);
            }

            $cartItems = CartItem::get()->filter('MemberID', $member->ID);
            if (!$cartItems->count()) {
                return $this->jsonResponse(['error' => 'Cart is empty'], 400);
            }

            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item->getSubtotal();
            }

            $paymentFee = $this->paymentService->getPaymentFee($data['payment_method'], $subtotal);
            $totalAmount = $subtotal + $paymentFee;

            $order = Order::create();
            $order->MemberID = $member->ID;
            $order->TotalHarga = $totalAmount;
            $order->TotalHargaBarang = $subtotal;
            $order->PaymentFee = $paymentFee;
            $order->Status = 'MenungguPembayaran';
            $order->NomorInvoice = 'INV-' . date('Ymd') . '-' . sprintf('%06d', rand(1, 999999));
            $order->NomorMeja = $data['nomor_meja'];
            $order->write();

            $orderItems = [];
            foreach ($cartItems as $cartItem) {
                $orderItem = OrderItem::create();
                $orderItem->OrderID = $order->ID;
                $orderItem->ProdukID = $cartItem->ProdukID;
                $orderItem->Kuantitas = $cartItem->Kuantitas;
                $orderItem->HargaSatuan = $cartItem->Produk()->Harga;
                $orderItem->write();

                $orderItems[] = [
                    'id' => $orderItem->ID,
                    'produk_id' => $orderItem->ProdukID,
                    'produk_nama' => $orderItem->Produk()->Nama,
                    'kuantitas' => $orderItem->Kuantitas,
                    'harga_satuan' => $orderItem->HargaSatuan,
                    'subtotal' => $orderItem->getSubtotal(),
                ];
            }

            $payment = Payment::create();
            $payment->OrderID = $order->ID;
            $payment->Reference = 'PAY-' . $order->NomorInvoice;
            $payment->TotalHarga = $totalAmount;
            $payment->Status = 'Pending';
            $payment->MetodePembayaran = $data['payment_method'];
            $payment->write();

            $order->PaymentID = $payment->ID;
            $order->write();

            $paymentUrl = $this->paymentService->createDuitkuPayment(
                $payment,
                $data['payment_method'],
                $subtotal,
                $member
            );

            if ($paymentUrl) {
                foreach ($cartItems as $cartItem) {
                    $cartItem->delete();
                }

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => [
                        'id' => $order->ID,
                        'nomor_invoice' => $order->NomorInvoice,
                        'nomor_meja' => $order->NomorMeja,
                        'total_harga' => $order->TotalHarga,
                        'total_harga_barang' => $order->TotalHargaBarang,
                        'payment_fee' => $order->PaymentFee,
                        'status' => $order->Status,
                        'created' => $order->Created,
                        'items' => $orderItems,
                        'payment' => [
                            'id' => $payment->ID,
                            'reference' => $payment->Reference,
                            'metode_pembayaran' => $payment->MetodePembayaran,
                            'status' => $payment->Status,
                            'total_harga' => $payment->TotalHarga,
                            'paymenturl' => $paymentUrl,
                        ]
                    ]
                ], 201);
            } else {
                return $this->jsonResponse(['error' => 'Failed to create payment'], 500);
            }
        }

        return $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }

    public function order(HTTPRequest $request)
    {
        if (!$request->isGET()) {
            return $this->jsonResponse(['error' => 'Only GET method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $id = $request->param('ID');
        $order = Order::get()->filter([
            'ID' => $id,
            'MemberID' => $member->ID
        ])->first();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        $orderItems = [];
        foreach ($order->OrderItems() as $item) {
            $orderItems[] = [
                'id' => $item->ID,
                'produk_id' => $item->ProdukID,
                'produk_nama' => $item->Produk()->Nama,
                'kuantitas' => $item->Kuantitas,
                'harga_satuan' => $item->HargaSatuan,
                'subtotal' => $item->getSubtotal(),
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'id' => $order->ID,
                'nomor_invoice' => $order->NomorInvoice,
                'nomor_meja' => $order->NomorMeja,
                'total_harga_barang' => $order->TotalHargaBarang,
                'payment_fee' => $order->PaymentFee,
                'total_harga' => $order->TotalHarga,
                'status' => $order->Status,
                'created' => $order->Created,
                'items' => $orderItems,
                'payment' => $order->Payment()->exists() ? [
                    'id' => $order->Payment()->ID,
                    'reference' => $order->Payment()->Reference,
                    'metode_pembayaran' => $order->Payment()->MetodePembayaran,
                    'status' => $order->Payment()->Status,
                    'payment_url' => $order->Payment()->PaymentUrl,
                    'expiry_time' => $order->Payment()->ExpiryTime,
                    'total_harga' => $order->Payment()->TotalHarga, // TAMBAHAN
                ] : null
            ]
        ]);
    }

    // * PAYMENT *
    public function payment(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->jsonResponse(['error' => 'Only POST method allowed'], 405);
        }

        $member = $this->requireAuth();
        if ($member instanceof HTTPResponse)
            return $member;

        $data = json_decode($request->getBody(), true);

        if (!isset($data['order_id'], $data['payment_method'])) {
            return $this->jsonResponse(['error' => 'Order ID and payment method are required'], 400);
        }

        $order = Order::get()->filter([
            'ID' => $data['order_id'],
            'MemberID' => $member->ID
        ])->first();

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        if ($order->Status !== 'MenungguPembayaran') {
            return $this->jsonResponse(['error' => 'Order cannot be paid'], 400);
        }

        $payment = $order->Payment();
        if (!$payment) {
            return $this->jsonResponse(['error' => 'Payment not found'], 404);
        }

        $paymentUrl = $this->paymentService->createDuitkuPayment(
            $payment,
            $data['payment_method'],
            $order->TotalHargaBarang,
            $member
        );

        if ($paymentUrl) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => [
                    'payment_url' => $paymentUrl,
                ]
            ]);
        } else {
            return $this->jsonResponse(['error' => 'Failed to create payment'], 500);
        }
    }

    // ========== Feature/Methods Etc ==========
    public function downloadInvoiceAPI(HTTPRequest $request)
    {
        $orderID = $request->param('ID');
        $order = Order::get()->byID($orderID);

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order tidak ditemukan'], 404);
        }

        $user = $order->Member();
        $siteConfig = SiteConfig::current_site_config();
        $pdfContent = $this->emailService->generateInvoicePDF($order, $user, $siteConfig);
        $pdfBase64 = base64_encode($pdfContent);

        return $this->jsonResponse([
            'success' => true,
            'orderID' => $order->ID,
            'NomorInvoice' => $order->NomorInvoice,
            'pdf_base64' => $pdfBase64
        ]);
    }

    public function sendInvoiceAPI(HTTPRequest $request)
    {
        $orderID = $request->param('ID');
        $order = Order::get()->byID($orderID);

        if (!$order) {
            return $this->jsonResponse(['error' => 'Order tidak ditemukan'], 404);
        }

        try {
            $this->emailService->sendInvoiceEmail($order);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Email invoice telah dikirim ke ' . $order->Member()->Email
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Gagal mengirim email',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function paymentmethods(HTTPRequest $request)
    {
        if ($request->httpMethod() !== 'POST') {
            return $this->jsonResponse(['error' => 'Method not allowed. Use GET.'], 405);
        }

        $data = json_decode($request->getBody(), true);
        $amount = $data['amount'];

        if (!$amount || !is_numeric($amount)) {
            return $this->jsonResponse(['error' => 'Valid amount parameter required'], 400);
        }

        try {
            $paymentService = new PaymentService();
            $paymentMethods = $paymentService->getPaymentMethods((int) $amount);

            if (empty($paymentMethods)) {
                return $this->jsonResponse([
                    'error' => 'No payment methods available',
                    'data' => []
                ], 200);
            }

            $formattedMethods = [];
            foreach ($paymentMethods as $method) {
                $formattedMethods[] = [
                    'paymentMethod' => $method['paymentMethod'],
                    'paymentName' => $method['paymentName'],
                    'paymentImage' => $method['paymentImage'] ?? '',
                    'totalFee' => $method['totalFee'] ?? 0,
                    'paymentGroup' => $method['paymentGroup'] ?? 'other'
                ];
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $formattedMethods,
                'count' => count($formattedMethods)
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'error' => 'Failed to fetch payment methods',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function forgotpassword(HTTPRequest $request)
    {
        $data = json_decode($request->getBody(), true);
        $email = $data['email'] ?? '';

        if (empty($email)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Email wajib diisi.'
            ], 422);
        }

        $validationResult = $this->authService->processForgotPassword($request, $email);
        if ($validationResult && $validationResult->isValid()) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Link atur ulang kata sandi telah dikirim ke email Anda.'
            ], 200);
        }

        $errorMessages = $validationResult ? $validationResult->getMessages() : [];
        $errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
        if (!empty($errorMessages)) {
            $errorMessage = $errorMessages[0]['message'] ?? $errorMessage;
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => $errorMessage
        ], 400);
    }

    // ========== HELPER METHODS ==========
    private function requireAuth()
    {
        $member = Security::getCurrentUser();

        if (!$member) {
            return $this->jsonResponse(['error' => 'Authentication required'], 401);
        }

        return $member;
    }

    private function jsonResponse($data, $status = 200)
    {
        $response = new HTTPResponse(json_encode($data), $status);
        $response->addHeader('Content-Type', 'application/json');
        $response->addHeader('Access-Control-Allow-Origin', '*');
        $response->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->addHeader('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}