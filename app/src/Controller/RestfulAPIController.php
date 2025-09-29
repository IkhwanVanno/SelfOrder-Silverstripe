<?php

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Core\Convert;
use SilverStripe\Assets\File;
use SilverStripe\Control\Director;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\IdentityStore;
use SilverStripe\ORM\ValidationResult;

class RestfulAPIController extends Controller
{
      private static $url_segment = 'api';

      private static $allowed_actions = [
            'index',
            'register',
            'login',
            'forgotpassword',
            'logout',
            'currentMemberr',
            'siteconfig',
            'member',
            'produk',
            'kategoriproduk',
            'order',
            'orderitem',
            'cartitem',
            'payment',
            'paymentmethods',
            'downloadInvoiceAPI',
            'sendInvoiceAPI',
      ];

      protected function init()
      {
            parent::init();

            // Set CORS headers
            $this->getResponse()->addHeader('Access-Control-Allow-Origin', '*');
            $this->getResponse()->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $this->getResponse()->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $this->getResponse()->addHeader('Content-Type', 'application/json');

            // Handle preflight requests
            if ($this->getRequest()->httpMethod() === 'OPTIONS') {
                  return $this->jsonResponse(['message' => 'OK'], 200);
            }
      }

      protected $authService;
      protected $emailService;

      public function __construct()
      {
            parent::__construct();
            $this->authService = Injector::inst()->get(AuthService::class);
            $this->emailService = Injector::inst()->get(EmailService::class);
      }

      public function index(HTTPRequest $request)
      {
            return $this->jsonResponse([
                  'message' => 'SilverStripe RESTful API',
                  'status' => 'Working',
                  'endpoints' => [
                        'GET /api' => 'API information',
                        'POST /api/register' => 'User Register',
                        'POST /api/login' => 'User login (email, password)',
                        'POST /api/forgotpassword' => 'Forgot password (email)',
                        'POST /api/logout' => 'User logout',
                        'GET /api/currentMemberr' => 'Get current user',
                        'GET|PUT /api/siteconfig' => 'Site configuration',
                        'CRUD /api/member/{id}' => 'Member management',
                        'CRUD /api/produk/{id}' => 'Product management',
                        'CRUD /api/kategoriproduk/{id}' => 'Category management',
                        'CRUD /api/order/{id}' => 'Order management',
                        'CRUD /api/cartitem/{id}' => 'Cart management',
                        'CRUD /api/payment/{id}' => 'Payment management',
                        'POST /api/paymentmethods' => 'Get payment methods',
                        'GET /api/order/ID/pdf' => 'download PDF Invoice',
                        'POST /api/order/ID/send-email' => 'Send Invoice to Email User',
                  ],
                  'available_models' => $this->getAvailableModels(),
                  'note' => 'Use POST method for login with JSON: {"email":"user@example.com","password":"pass123"}'
            ]);
      }

      // ============== AUTHENTICATION (FIXED) ==============
      public function register(HTTPRequest $request)
      {
            if ($request->httpMethod() !== 'POST') {
                  return $this->jsonResponse(['error' => 'Method not allowed'], 405);
            }

            $data = $this->getRequestData($request);

            try {
                  $member = Member::create();
                  $member->FirstName = $data['FirstName'] ?? '';
                  $member->Surname = $data['Surname'] ?? '';
                  $member->Email = $data['Email'] ?? '';
                  $member->changePassword($data['Password'] ?? '');
                  $member->write();
                  $member->addToGroupByCode('site-users');

                  return $this->jsonResponse([
                        'message' => 'Registration successful',
                        'data' => $this->serializeMember($member)
                  ], 201);
            } catch (Exception $e) {
                  return $this->jsonResponse([
                        'error' => 'Registration failed',
                        'details' => $e->getMessage()
                  ], 500);
            }
      }

      public function login(HTTPRequest $request)
      {
            if ($request->httpMethod() !== 'POST') {
                  return $this->jsonResponse(['error' => 'Method not allowed. Use POST.'], 405);
            }

            $data = $this->getRequestData($request);

            if (!isset($data['email']) || !isset($data['password'])) {
                  return $this->jsonResponse([
                        'error' => 'Email and password required',
                        'received_data' => array_keys($data),
                        'expected' => ['email', 'password']
                  ], 400);
            }

            $member = Member::get()->filter('Email', $data['email'])->first();
            if (!$member) {
                  return $this->jsonResponse(['error' => 'User not found'], 401);
            }

            try {
                  $authenticator = new MemberAuthenticator();
                  $loginHandler = new LoginHandler('login', $authenticator);

                  $authData = [
                        'Email' => $data['email'],
                        'Password' => $data['password']
                  ];

                  $validationResult = new ValidationResult();
                  $authenticatedMember = $loginHandler->checkLogin($authData, $request, $validationResult);

                  if (!$authenticatedMember) {
                        return $this->jsonResponse(['error' => 'Invalid credentials'], 401);
                  }

                  $loginHandler->performLogin($authenticatedMember, $authData, $request);
                  return $this->jsonResponse([
                        'message' => 'Login successful',
                        'user' => $this->serializeMember($authenticatedMember)
                  ]);

            } catch (Exception $e) {
                  if ($this->verifyPassword($data['password'], $member)) {
                        Security::setCurrentUser($member);

                        return $this->jsonResponse([
                              'message' => 'Login successful (fallback method)',
                              'user' => $this->serializeMember($member)
                        ]);
                  }

                  return $this->jsonResponse([
                        'error' => 'Authentication failed',
                        'debug' => $e->getMessage()
                  ], 401);
            }
      }

      private function verifyPassword($plainPassword, $member)
      {
            $encryptedPassword = $member->Password;
            $algorithm = $member->PasswordEncryption ?: 'blowfish';
            switch ($algorithm) {
                  case 'blowfish':
                        return password_verify($plainPassword, $encryptedPassword);
                  case 'sha1':
                        return sha1($plainPassword) === $encryptedPassword;
                  case 'md5':
                        return md5($plainPassword) === $encryptedPassword;
                  default:
                        return password_verify($plainPassword, $encryptedPassword);
            }
      }

      // Forgot Password
      public function forgotpassword(HTTPRequest $request)
      {
            $data = $this->getRequestData($request);
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

      public function logout(HTTPRequest $request)
      {
            if ($request->httpMethod() !== 'POST') {
                  return $this->jsonResponse(['error' => 'Method not allowed. Use POST.'], 405);
            }

            try {
                  $identityStore = Injector::inst()->get(IdentityStore::class);
                  $identityStore->logOut($request);

                  return $this->jsonResponse(['message' => 'Logout successful']);
            } catch (Exception $e) {
                  // Fallback logout
                  Security::setCurrentUser(null);
                  return $this->jsonResponse(['message' => 'Logout successful (fallback)']);
            }
      }

      public function currentMemberr(HTTPRequest $request)
      {
            if ($request->httpMethod() !== 'GET') {
                  return $this->jsonResponse(['error' => 'Method not allowed. Use GET.'], 405);
            }

            $currentUser = Security::getCurrentUser();

            if (!$currentUser) {
                  return $this->jsonResponse(['error' => 'Not authenticated'], 401);
            }

            return $this->jsonResponse([
                  'user' => $this->serializeMember($currentUser)
            ]);
      }

      public function createTestUser(HTTPRequest $request)
      {
            if ($request->httpMethod() !== 'POST') {
                  return $this->jsonResponse(['error' => 'Method not allowed'], 405);
            }

            $data = $this->getRequestData($request);

            try {
                  $member = Member::create();
                  $member->FirstName = $data['firstname'] ?? 'Test';
                  $member->Surname = $data['surname'] ?? 'User';
                  $member->Email = $data['email'] ?? 'test@example.com';
                  $member->write();

                  // Set password
                  $password = $data['password'] ?? 'password123';
                  $member->changePassword($password);

                  // Add to default group
                  if (!$member->inGroup('site-users')) {
                        $member->addToGroupByCode('site-users');
                  }

                  return $this->jsonResponse([
                        'message' => 'Test user created successfully',
                        'user' => $this->serializeMember($member),
                        'login_data' => [
                              'email' => $member->Email,
                              'password' => $password
                        ]
                  ], 201);

            } catch (Exception $e) {
                  return $this->jsonResponse([
                        'error' => 'Failed to create user',
                        'details' => $e->getMessage()
                  ], 500);
            }
      }

      // =============== DATABASE ===============
      public function siteconfig(HTTPRequest $request)
      {
            $method = $request->httpMethod();

            if ($method === 'GET') {
                  $siteConfig = SiteConfig::current_site_config();
                  return $this->jsonResponse(['data' => $this->serializeDataObject($siteConfig)]);
            }

            if ($method === 'PUT') {
                  $data = $this->getRequestData($request);
                  $siteConfig = SiteConfig::current_site_config();

                  try {
                        $this->updateDataObjectFromData($siteConfig, $data);
                        $siteConfig->write();

                        return $this->jsonResponse([
                              'message' => 'Site configuration updated successfully',
                              'data' => $this->serializeDataObject($siteConfig)
                        ]);
                  } catch (Exception $e) {
                        return $this->jsonResponse(['error' => 'Update failed', 'details' => $e->getMessage()], 500);
                  }
            }

            return $this->jsonResponse(['error' => 'Method not allowed'], 405);
      }

      public function member(HTTPRequest $request)
      {
            return $this->handleModelCRUD(Member::class, $request);
      }

      public function produk(HTTPRequest $request)
      {
            return $this->handleModelCRUD('Produk', $request);
      }

      public function kategoriproduk(HTTPRequest $request)
      {
            return $this->handleModelCRUD('KategoriProduk', $request);
      }

      public function order(HTTPRequest $request)
      {
            $method = $request->httpMethod();
            $id = $this->extractIdFromRequest($request);

            switch ($method) {
                  case 'GET':
                        if ($id) {
                              return $this->getRecord('Order', $id);
                        } else {
                              return $this->listRecords('Order', $request);
                        }

                  case 'POST':
                        if (!$id) {
                              return $this->createOrderWithPayment($request);
                        }
                        break;

                  case 'PUT':
                        if ($id) {
                              return $this->updateRecord('Order', $id, $request);
                        }
                        break;

                  case 'DELETE':
                        if ($id) {
                              return $this->deleteRecord('Order', $id);
                        }
                        break;
            }

            return $this->jsonResponse(['error' => 'Method not allowed or invalid request'], 405);
      }

      public function orderitem(HTTPRequest $request)
      {
            return $this->handleModelCRUD('OrderItem', $request);
      }

      public function cartitem(HTTPRequest $request)
      {
            return $this->handleModelCRUD('CartItem', $request);
      }

      public function payment(HTTPRequest $request)
      {
            return $this->handleModelCRUD('Payment', $request);
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

      protected function createOrderWithPayment(HTTPRequest $request)
      {
            if (!Security::getCurrentUser()) {
                  return $this->jsonResponse(['error' => 'Authentication required'], 401);
            }

            $data = $this->getRequestData($request);
            $currentUser = Security::getCurrentUser();

            // Validate required fields
            $requiredFields = ['NomorMeja', 'MetodePembayaran', 'Items'];
            foreach ($requiredFields as $field) {
                  if (!isset($data[$field]) || empty($data[$field])) {
                        return $this->jsonResponse([
                              'error' => "Field '$field' is required"
                        ], 400);
                  }
            }

            try {
                  $subtotal = 0;
                  $orderItems = [];

                  foreach ($data['Items'] as $itemData) {
                        if (!isset($itemData['ProductID'], $itemData['Kuantitas'])) {
                              return $this->jsonResponse([
                                    'error' => 'Invalid item data - ProductID and Kuantitas required'
                              ], 400);
                        }

                        $product = Produk::get()->byID($itemData['ProductID']);
                        if (!$product) {
                              return $this->jsonResponse([
                                    'error' => "Product with ID {$itemData['ProductID']} not found"
                              ], 400);
                        }

                        $quantity = (int) $itemData['Kuantitas'];
                        $unitPrice = $product->Harga;
                        $itemSubtotal = $unitPrice * $quantity;
                        $subtotal += $itemSubtotal;

                        $orderItems[] = [
                              'ProductID' => $itemData['ProductID'],
                              'Kuantitas' => $quantity,
                              'HargaSatuan' => $unitPrice,
                              'Product' => $product
                        ];
                  }

                  // Get payment fee
                  $paymentService = new PaymentService();
                  $paymentFee = $paymentService->getPaymentFee($data['MetodePembayaran'], $subtotal);
                  $totalAmount = $subtotal + $paymentFee;

                  // Create Order
                  $order = Order::create();
                  $order->MemberID = $currentUser->ID;
                  $order->TotalHarga = $totalAmount;
                  $order->TotalHargaBarang = $subtotal;
                  $order->PaymentFee = $paymentFee;
                  $order->Status = 'MenungguPembayaran';
                  $order->NomorInvoice = 'INV-' . date('Ymd') . '-' . sprintf('%06d', rand(1, 999999));
                  $order->NomorMeja = $data['NomorMeja'];
                  $order->write();

                  // Create Order Items
                  foreach ($orderItems as $itemData) {
                        $orderItem = OrderItem::create();
                        $orderItem->OrderID = $order->ID;
                        $orderItem->ProdukID = $itemData['ProductID'];
                        $orderItem->Kuantitas = $itemData['Kuantitas'];
                        $orderItem->HargaSatuan = $itemData['HargaSatuan'];
                        $orderItem->write();
                  }

                  // Create Payment
                  $payment = Payment::create();
                  $payment->OrderID = $order->ID;
                  $payment->Reference = 'PAY-' . $order->NomorInvoice;
                  $payment->TotalHarga = $totalAmount;
                  $payment->Status = 'Pending';
                  $payment->MetodePembayaran = $data['MetodePembayaran'];
                  $payment->write();

                  // Update order with payment ID
                  $order->PaymentID = $payment->ID;
                  $order->write();

                  // Create Duitku payment and get payment URL
                  $paymentUrl = $paymentService->createDuitkuPayment(
                        $payment,
                        $data['MetodePembayaran'],
                        $subtotal, // Send subtotal to Duitku, not total (as per your existing logic)
                        $currentUser
                  );

                  if (!$paymentUrl) {
                        // If Duitku payment creation fails, update status
                        $payment->Status = 'Failed';
                        $payment->write();
                        $order->Status = 'Dibatalkan';
                        $order->write();

                        return $this->jsonResponse([
                              'error' => 'Failed to create payment with Duitku',
                              'order_id' => $order->ID
                        ], 500);
                  }

                  // Success response for Flutter
                  return $this->jsonResponse([
                        'success' => true,
                        'message' => 'Order created successfully',
                        'order' => [
                              'ID' => $order->ID,
                              'NomorInvoice' => $order->NomorInvoice,
                              'TotalHarga' => $totalAmount,
                              'TotalHargaBarang' => $subtotal,
                              'PaymentFee' => $paymentFee,
                              'Status' => $order->Status,
                              'NomorMeja' => $order->NomorMeja,
                              'payment_method' => $data['MetodePembayaran'],
                              'payment_url' => $paymentUrl,
                              'payment_reference' => $payment->Reference
                        ]
                  ], 201);

            } catch (ValidationException $e) {
                  return $this->jsonResponse([
                        'error' => 'Validation failed',
                        'details' => $e->getMessage()
                  ], 400);
            } catch (Exception $e) {
                  return $this->jsonResponse([
                        'error' => 'Order creation failed',
                        'details' => $e->getMessage()
                  ], 500);
            }
      }

      public function downloadInvoiceAPI(HTTPRequest $request)
      {
            $orderID = $request->param('ID');
            $order = Order::get()->byID($orderID);

            if (!$order) {
                  return $this->jsonResponse(['error' => 'Order tidak ditemukan'], 404);
            }

            $user = $order->Member();
            $siteConfig = SiteConfig::current_site_config();

            // Generate PDF
            $pdfContent = $this->emailService->generateInvoicePDF($order, $user, $siteConfig);

            // Encode PDF ke Base64 supaya bisa dikirim via JSON
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

      // ============== GENERIC MODEL CRUD ==============

      protected function handleModelCRUD($modelClass, HTTPRequest $request)
      {
            if ($modelClass === 'Member') {
                  $modelClass = Member::class;
            }
            if (!$this->isValidModel($modelClass)) {
                  return $this->jsonResponse(['error' => 'Invalid model'], 400);
            }

            $method = $request->httpMethod();
            $id = $this->extractIdFromRequest($request);

            switch ($method) {
                  case 'GET':
                        if ($id) {
                              return $this->getRecord($modelClass, $id);
                        } else {
                              return $this->listRecords($modelClass, $request);
                        }

                  case 'POST':
                        if (!$id) {
                              return $this->createRecord($modelClass, $request);
                        }
                        break;

                  case 'PUT':
                        if ($id) {
                              return $this->updateRecord($modelClass, $id, $request);
                        }
                        break;

                  case 'DELETE':
                        if ($id) {
                              return $this->deleteRecord($modelClass, $id);
                        }
                        break;
            }

            return $this->jsonResponse(['error' => 'Method not allowed or invalid request'], 405);
      }

      protected function extractIdFromRequest(HTTPRequest $request)
      {
            $id = $request->param('ID');
            if (!$id) {
                  $remaining = $request->remaining();
                  if ($remaining) {
                        $parts = explode('/', trim($remaining, '/'));
                        if (count($parts) > 0 && is_numeric($parts[0])) {
                              $id = $parts[0];
                        }
                  }
            }

            return $id;
      }

      protected function serializeOrder($order)
      {
            if (!$order) {
                  return null;
            }
            $data = $this->serializeDataObject($order);
            try {
                  $orderItems = $order->OrderItems();
                  $data['OrderItems'] = [];

                  if ($orderItems && $orderItems->count() > 0) {
                        foreach ($orderItems as $item) {
                              if ($item && $item->exists()) {
                                    $itemData = [
                                          'ID' => $item->ID,
                                          'Kuantitas' => $item->Kuantitas,
                                          'HargaSatuan' => $item->HargaSatuan,
                                          'Produk' => null
                                    ];
                                    try {
                                          $product = $item->Produk();
                                          if ($product && $product->exists()) {
                                                $itemData['Produk'] = [
                                                      'ID' => $product->ID,
                                                      'Nama' => $product->Nama,
                                                      'Harga' => $product->Harga,
                                                      'Status' => $product->Status
                                                ];
                                          }
                                    } catch (Exception $e) {
                                    }
                                    $data['OrderItems'][] = $itemData;
                              }
                        }
                  }
            } catch (Exception $e) {
                  $data['OrderItems'] = [];
                  error_log('Error serializing OrderItems: ' . $e->getMessage());
            }

            return $data;
      }

      protected function listRecords($modelClass, HTTPRequest $request)
      {
            try {
                  if ($modelClass === 'Order') {
                        $currentUser = Security::getCurrentUser();
                        if (!$currentUser) {
                              return $this->jsonResponse(['error' => 'Authentication required'], 401);
                        }
                        $objects = Order::get()->filter('MemberID', $currentUser->ID);
                  } else {
                        $objects = $modelClass::get();
                        if (in_array($modelClass, ['CartItem', 'Payment'])) {
                              $currentUser = Security::getCurrentUser();
                              if (!$currentUser) {
                                    return $this->jsonResponse(['error' => 'Authentication required'], 401);
                              }
                              $objects = $objects->filter('MemberID', $currentUser->ID);
                        }
                  }

                  $filters = $this->getFilters($request);
                  if ($filters) {
                        $objects = $objects->filter($filters);
                  }

                  $sort = $this->getSort($request);
                  if ($sort) {
                        $objects = $objects->sort($sort);
                  } else {
                        if ($modelClass === 'Order') {
                              $objects = $objects->sort('Created DESC');
                        }
                  }

                  $pagination = $this->getPagination($request);
                  $paginatedObjects = $objects->limit($pagination['limit'], $pagination['offset']);

                  $serializedData = [];
                  foreach ($paginatedObjects as $object) {
                        if ($modelClass === 'Member') {
                              $serializedData[] = $this->serializeMember($object);
                        } else if ($modelClass === 'Order') {
                              $serializedData[] = $this->serializeOrder($object);
                        } else {
                              $serializedData[] = $this->serializeDataObject($object);
                        }
                  }

                  return $this->jsonResponse([
                        'data' => $serializedData,
                        'total' => $objects->count(),
                        'pagination' => $pagination,
                        'user_id' => Security::getCurrentUser() ? Security::getCurrentUser()->ID : null // Debug info
                  ]);

            } catch (Exception $e) {
                  return $this->jsonResponse([
                        'error' => 'Failed to list records',
                        'details' => $e->getMessage()
                  ], 500);
            }
      }
      protected function getRecord($modelClass, $id)
      {
            try {
                  $object = $modelClass::get()->byID($id);

                  if (!$object) {
                        return $this->jsonResponse(['error' => 'Record not found'], 404);
                  }
                  if ($modelClass === 'Member') {
                        $currentUser = Security::getCurrentUser();
                        if (!$currentUser || $currentUser->ID != $id) {
                              return $this->jsonResponse([
                                    'error' => 'Unauthorized to view this profile'
                              ], 403);
                        }
                  }

                  if ($modelClass === 'Member') {
                        $serializedData = $this->serializeMember($object);
                  } else if ($modelClass === 'Order') {
                        $serializedData = $this->serializeOrder($object);
                  } else {
                        $serializedData = $this->serializeDataObject($object);
                  }

                  return $this->jsonResponse(['data' => $serializedData]);

            } catch (Exception $e) {
                  return $this->jsonResponse([
                        'error' => 'Failed to get record',
                        'details' => $e->getMessage()
                  ], 500);
            }
      }

      protected function createRecord($modelClass, HTTPRequest $request)
      {
            $data = $this->getRequestData($request);

            try {
                  $object = $modelClass::create();
                  $this->updateDataObjectFromData($object, $data);

                  if ($modelClass === 'Member' && isset($data['Password'])) {
                        $object->changePassword($data['Password']);
                  }
                  if (in_array($modelClass, ['Order', 'CartItem', 'Payment'])) {
                        $currentUser = Security::getCurrentUser();
                        if (!$currentUser) {
                              return $this->jsonResponse(['error' => 'Not authenticated'], 401);
                        }

                        $object->MemberID = $currentUser->ID;
                  }

                  $object->write();

                  $serializedData = ($modelClass === 'Member') ?
                        $this->serializeMember($object) :
                        $this->serializeDataObject($object);

                  return $this->jsonResponse([
                        'message' => 'Record created successfully',
                        'data' => $serializedData
                  ], 201);
            } catch (ValidationException $e) {
                  return $this->jsonResponse(['error' => 'Validation failed', 'details' => $e->getMessage()], 400);
            } catch (Exception $e) {
                  return $this->jsonResponse(['error' => 'Creation failed', 'details' => $e->getMessage()], 500);
            }
      }

      protected function updateRecord($modelClass, $id, HTTPRequest $request)
      {
            $object = $modelClass::get()->byID($id);

            if (!$object) {
                  return $this->jsonResponse(['error' => 'Record not found'], 404);
            }

            $data = $this->getRequestData($request);

            try {
                  $this->updateDataObjectFromData($object, $data);

                  if ($modelClass === 'Member') {
                        $currentUser = Security::getCurrentUser();
                        if (!$currentUser || $currentUser->ID != $id) {
                              return $this->jsonResponse([
                                    'error' => 'Unauthorized to update this profile'
                              ], 403);
                        }

                        if (isset($data['Password']) && !empty($data['Password'])) {
                              $object->changePassword($data['Password']);
                        }

                        $allowedFields = ['FirstName', 'Surname', 'Email'];
                        foreach ($data as $key => $value) {
                              if (in_array($key, $allowedFields)) {
                                    $object->$key = $value;
                              }
                        }
                  } else {
                        $this->updateDataObjectFromData($object, $data);
                  }

                  $object->write();

                  $serializedData = ($modelClass === 'Member') ?
                        $this->serializeMember($object) :
                        $this->serializeDataObject($object);

                  return $this->jsonResponse([
                        'message' => 'Record updated successfully',
                        'data' => $serializedData
                  ]);
            } catch (ValidationException $e) {
                  return $this->jsonResponse(['error' => 'Validation failed', 'details' => $e->getMessage()], 400);
            } catch (Exception $e) {
                  return $this->jsonResponse(['error' => 'Update failed', 'details' => $e->getMessage()], 500);
            }
      }

      protected function deleteRecord($modelClass, $id)
      {
            $object = $modelClass::get()->byID($id);

            if (!$object) {
                  return $this->jsonResponse(['error' => 'Record not found'], 404);
            }

            try {
                  $object->delete();
                  return $this->jsonResponse(['message' => 'Record deleted successfully']);
            } catch (Exception $e) {
                  return $this->jsonResponse(['error' => 'Delete failed', 'details' => $e->getMessage()], 500);
            }
      }

      // ============== HELPER METHODS ==============

      protected function jsonResponse($data, $statusCode = 200)
      {
            $response = HTTPResponse::create(json_encode($data), $statusCode);
            $response->addHeader('Content-Type', 'application/json');
            return $response;
      }

      protected function getRequestData(HTTPRequest $request)
      {
            $body = $request->getBody();

            if (!empty($body)) {
                  $data = json_decode($body, true);
                  if (json_last_error() === JSON_ERROR_NONE) {
                        return $data;
                  }
            }

            return $request->postVars();
      }

      protected function isValidModel($modelClass)
      {
            if (!$modelClass || !class_exists($modelClass)) {
                  return false;
            }

            return is_subclass_of($modelClass, DataObject::class) || $modelClass === DataObject::class;
      }

      protected function getAvailableModels()
      {
            $models = [];
            $customModels = ['Produk', 'KategoriProduk', 'Order', 'OrderItem', 'CartItem', 'Payment'];

            foreach ($customModels as $class) {
                  if (class_exists($class)) {
                        $models[] = [
                              'class' => $class,
                              'url_segment' => strtolower($class),
                              'endpoints' => [
                                    'list' => "GET /api/v1/" . strtolower($class),
                                    'get' => "GET /api/v1/" . strtolower($class) . "/{id}",
                                    'create' => "POST /api/v1/" . strtolower($class),
                                    'update' => "PUT /api/v1/" . strtolower($class) . "/{id}",
                                    'delete' => "DELETE /api/v1/" . strtolower($class) . "/{id}"
                              ]
                        ];
                  }
            }

            return $models;
      }

      protected function serializeDataObject($object)
      {
            if (!$object) {
                  return null;
            }

            $data = [];
            $dbFields = $object->config()->get('db') ?: [];

            // Add basic fields
            $data['ID'] = $object->ID;
            $data['ClassName'] = $object->ClassName;
            if (property_exists($object, 'Created')) {
                  $data['Created'] = $object->Created;
            }
            if (property_exists($object, 'LastEdited')) {
                  $data['LastEdited'] = $object->LastEdited;
            }

            // Add database fields
            foreach ($dbFields as $fieldName => $fieldType) {
                  $data[$fieldName] = $object->$fieldName;
            }

            // Add has_one relationships
            $hasOne = $object->config()->get('has_one') ?: [];
            foreach ($hasOne as $relationName => $relationClass) {
                  try {
                        $relationObject = $object->$relationName();
                        if ($relationObject && $relationObject->exists()) {
                              // Khusus untuk gambar
                              if ($relationObject instanceof \SilverStripe\Assets\Image) {
                                    if ($relationObject instanceof \SilverStripe\Assets\Image) {
                                          $data[$relationName] = [
                                                'ID' => $relationObject->ID,
                                                'URL' => Director::absoluteURL($relationObject->getURL()),
                                                'Filename' => $relationObject->Filename,
                                                'Title' => $relationObject->Title,
                                          ];
                                    }

                              }
                              // Khusus untuk Member
                              else if ($relationObject instanceof Member) {
                                    $data[$relationName] = $this->serializeMember($relationObject);
                              }
                              // Relasi biasa
                              else {
                                    $data[$relationName] = $this->serializeDataObject($relationObject);
                              }
                        } else {
                              $data[$relationName] = null;
                        }
                  } catch (Exception $e) {
                        $data[$relationName] = null;
                  }
            }


            return $data;
      }

      protected function serializeMember($member)
      {
            if (!$member) {
                  return null;
            }

            $data = $this->serializeDataObject($member);

            // Remove sensitive fields
            unset($data['Password']);
            unset($data['Salt']);
            unset($data['PasswordEncryption']);
            unset($data['ResetPasswordToken']);
            unset($data['ResetPasswordExpiry']);

            // Add member-specific fields
            $data['Groups'] = [];
            try {
                  foreach ($member->Groups() as $group) {
                        $data['Groups'][] = [
                              'ID' => $group->ID,
                              'Title' => $group->Title,
                              'Code' => $group->Code
                        ];
                  }
            } catch (Exception $e) {
                  // Ignore group errors
            }

            return $data;
      }

      protected function updateDataObjectFromData($object, $data)
      {
            $dbFields = $object->config()->get('db') ?: [];

            foreach ($data as $key => $value) {
                  if (isset($dbFields[$key]) && !in_array($key, ['ID', 'Created', 'LastEdited', 'ClassName'])) {
                        $object->$key = $value;
                  }
            }

            // Handle has_one relationships
            $hasOne = $object->config()->get('has_one') ?: [];
            foreach ($hasOne as $relationName => $relationClass) {
                  if (isset($data[$relationName . 'ID'])) {
                        $object->{$relationName . 'ID'} = $data[$relationName . 'ID'];
                  }
            }
      }

      protected function getFilters(HTTPRequest $request)
      {
            $filters = [];

            foreach ($request->getVars() as $key => $value) {
                  if (strpos($key, 'filter_') === 0) {
                        $fieldName = substr($key, 7);
                        $filters[$fieldName] = $value;
                  }
            }

            return $filters;
      }

      protected function getSort(HTTPRequest $request)
      {
            $sort = $request->getVar('sort');
            $direction = $request->getVar('direction') ?: 'ASC';

            if ($sort) {
                  return [$sort => $direction];
            }

            return null;
      }

      protected function getPagination(HTTPRequest $request)
      {
            $limit = (int) $request->getVar('limit') ?: 50;
            $page = (int) $request->getVar('page') ?: 1;
            $offset = ($page - 1) * $limit;

            return [
                  'limit' => $limit,
                  'page' => $page,
                  'offset' => $offset
            ];
      }
}