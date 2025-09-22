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
      private static $url_segment = 'api/v1';

      private static $allowed_actions = [
            'index',
            'register',
            'login',
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

      public function index(HTTPRequest $request)
      {
            return $this->jsonResponse([
                  'message' => 'SilverStripe RESTful API v1 - Fixed Version',
                  'status' => 'Working',
                  'endpoints' => [
                        'GET /api/v1' => 'API information',
                        'POST /api/v1/register' => 'User Register',
                        'POST /api/v1/login' => 'User login (email, password)',
                        'POST /api/v1/logout' => 'User logout',
                        'GET /api/v1/currentMemberr' => 'Get current user',
                        'GET|PUT /api/v1/siteconfig' => 'Site configuration',
                        'CRUD /api/v1/member/{id}' => 'Member management',
                        'CRUD /api/v1/produk/{id}' => 'Product management',
                        'CRUD /api/v1/kategoriproduk/{id}' => 'Category management',
                        'CRUD /api/v1/order/{id}' => 'Order management',
                        'CRUD /api/v1/cartitem/{id}' => 'Cart management',
                        'CRUD /api/v1/payment/{id}' => 'Payment management',
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

                  // Optional: Masukkan ke grup
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

            // Find member by email
            $member = Member::get()->filter('Email', $data['email'])->first();

            if (!$member) {
                  return $this->jsonResponse(['error' => 'User not found'], 401);
            }

            // Alternative authentication method using password verification
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

                  // Perform login
                  $loginHandler->performLogin($authenticatedMember, $authData, $request);

                  return $this->jsonResponse([
                        'message' => 'Login successful',
                        'user' => $this->serializeMember($authenticatedMember)
                  ]);

            } catch (Exception $e) {
                  // Fallback: Manual password verification (less secure but works)
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

      /**
       * Manual password verification as fallback
       */
      private function verifyPassword($plainPassword, $member)
      {
            // Get the encrypted password from member
            $encryptedPassword = $member->Password;
            $algorithm = $member->PasswordEncryption ?: 'blowfish';

            // Simple verification for common algorithms
            switch ($algorithm) {
                  case 'blowfish':
                        return password_verify($plainPassword, $encryptedPassword);
                  case 'sha1':
                        return sha1($plainPassword) === $encryptedPassword;
                  case 'md5':
                        return md5($plainPassword) === $encryptedPassword;
                  default:
                        // For other algorithms, try password_verify
                        return password_verify($plainPassword, $encryptedPassword);
            }
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

      // ============== SIMPLE USER CREATION FOR TESTING ==============

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

      // ============== SITECONFIG ==============

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

      // ============== MODEL HANDLERS ==============

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
            return $this->handleModelCRUD('Order', $request);
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
            // Try to get ID from URL parameter first
            $id = $request->param('ID');

            // If not found, try to extract from remaining URL
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

      protected function listRecords($modelClass, HTTPRequest $request)
      {
            try {
                  $objects = $modelClass::get();

                  // Apply filters
                  $filters = $this->getFilters($request);
                  if ($filters) {
                        $objects = $objects->filter($filters);
                  }

                  // Apply sorting
                  $sort = $this->getSort($request);
                  if ($sort) {
                        $objects = $objects->sort($sort);
                  }

                  // Apply pagination
                  $pagination = $this->getPagination($request);
                  $paginatedObjects = $objects->limit($pagination['limit'], $pagination['offset']);

                  $serializedData = [];
                  foreach ($paginatedObjects as $object) {
                        if ($modelClass === 'Member') {
                              $serializedData[] = $this->serializeMember($object);
                        } else {
                              $serializedData[] = $this->serializeDataObject($object);
                        }
                  }

                  return $this->jsonResponse([
                        'data' => $serializedData,
                        'total' => $objects->count(),
                        'pagination' => $pagination
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

                  $serializedData = ($modelClass === 'Member') ?
                        $this->serializeMember($object) :
                        $this->serializeDataObject($object);

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
                              $data[$relationName] = [
                                    'ID' => $relationObject->ID,
                                    'Title' => method_exists($relationObject, 'getTitle') ? $relationObject->getTitle() : $relationObject->Name ?? $relationObject->ID,
                                    'ClassName' => $relationObject->ClassName
                              ];

                              if ($relationObject instanceof File) {
                                    $data[$relationName]['URL'] = $relationObject->getAbsoluteURL();
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