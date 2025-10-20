<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\View\ArrayData;

class AuthPageController extends PageController
{
    private static $allowed_actions = [
        'Login',
        'Register',
        'Logout',
        'forgotPassword',
        'resetPassword',
        'googleLogin',
        'googleCallback'
    ];

    private static $url_handlers = [
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
        'forgotpassword' => 'forgotPassword',
        'resetpassword' => 'resetPassword',
        'google-login' => 'googleLogin',
        'google-callback' => 'googleCallback'
    ];

    private $AuthService;

    protected function init()
    {
        parent::init();
        $this->AuthService = new AuthService();
    }


    // === Google Authentication ===
    private function getGoogleConfig()
    {
        return [
            'client_id' => Environment::getEnv('GOOGLE_CLIENT_ID'),
            'client_secret' => Environment::getEnv('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => Director::absoluteBaseURL() . '/auth/google-callback',
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'userinfo_url' => 'https://www.googleapis.com/oauth2/v2/userinfo'
        ];
    }

    public function googleLogin(HTTPRequest $request)
    {
        $config = $this->getGoogleConfig();

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online'
        ];

        $authUrl = $config['auth_url'] . '?' . http_build_query($params);

        return $this->redirect($authUrl);
    }

    public function googleCallback(HTTPRequest $request)
    {
        $code = $request->getVar('code');
        if (!$code) {
            $request->getSession()->set('FlashMessage', [
                'Message' => 'Login dengan Google dibatalkan.',
                'Type' => 'warning'
            ]);
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        try {
            $config = $this->getGoogleConfig();
            $tokenData = $this->AuthService->getGoogleAccessToken($code, $config);

            if (!isset($tokenData['access_token'])) {
                throw new \Exception('Failed to get access token');
            }

            $userInfo = $this->AuthService->getGoogleUserInfo($tokenData['access_token'], $config);

            if (!isset($userInfo['email'])) {
                throw new \Exception('Failed to get user email');
            }

            $email = $userInfo['email'];
            $firstName = $userInfo['given_name'] ?? '';
            $lastName = $userInfo['family_name'] ?? '';
            $googleId = $userInfo['id'];

            $member = Member::get()->filter('Email', $email)->first();

            if (!$member) {
                $member = Member::create();
                $member->FirstName = $firstName;
                $member->Surname = $lastName;
                $member->Email = $email;
                $member->GoogleID = $googleId;
                $member->IsVerified = true;
                $member->write();
                $member->addToGroupByCode('site-users');

                $member->changePassword(bin2hex(random_bytes(16)));

                $request->getSession()->set('FlashMessage', [
                    'Message' => 'Akun berhasil dibuat dengan Google. Selamat datang!',
                    'Type' => 'success'
                ]);
            } else {
                if (!$member->GoogleID) {
                    $member->GoogleID = $googleId;
                    $member->IsVerified = true;
                    $member->write();
                }

                $request->getSession()->set('FlashMessage', [
                    'Message' => 'Masuk berhasil! Selamat datang.',
                    'Type' => 'primary'
                ]);
            }

            Injector::inst()->get(IdentityStore::class)->logIn($member, false, $request);
            return $this->redirect(Director::absoluteBaseURL());

        } catch (\Exception $e) {
            $request->getSession()->set('FlashMessage', [
                'Message' => 'Terjadi kesalahan saat login dengan Google: ' . $e->getMessage(),
                'Type' => 'danger'
            ]);
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }
    }

    // === Manual Authentication ===
    public function Login(HTTPRequest $request)
    {
        $validationResult = null;

        if ($request->isPOST()) {
            $validationResult = $this->AuthService->handleLogin($request);

            if ($validationResult->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Type' => 'primary',
                    'Message' => 'Selamat datang kembali!'
                ]);
                return $this->redirect(Director::absoluteBaseURL());
            } else {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Type' => 'danger',
                    'Message' => $validationResult->getMessages()[0]['message'] ?? 'Login gagal.'
                ]);
                return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
            }
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Login',
            'isLoggedIn' => $this->isLoggedIn(),
            'Member' => $this->getCurrentUser(),
            'FlashMessages' => $this->getFlashMessages(),
        ]);

        return $this->customise($data)->renderWith(['LoginPage', 'Page']);
    }

    public function Register(HTTPRequest $request)
    {
        $validationResult = null;

        if ($request->isPOST()) {
            $validationResult = $this->AuthService->handleRegister($request);

            if ($validationResult->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Type' => 'success',
                    'Message' => 'Registrasi berhasil! Silakan login.'
                ]);
                return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
            } else {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Type' => 'danger',
                    'Message' => $validationResult->getMessages()[0]['message'] ?? 'Register gagal.'
                ]);
                return $this->redirect(Director::absoluteBaseURL() . '/auth/register');
            }
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Register',
            'isLoggedIn' => $this->isLoggedIn(),
            'Member' => $this->getCurrentUser(),
            'FlashMessages' => $this->getFlashMessages(),
        ]);

        return $this->customise($data)->renderWith(['RegisterPage', 'Page']);
    }

    public function Logout(HTTPRequest $request)
    {
        Injector::inst()->get(IdentityStore::class)->logOut($request);
        $this->getRequest()->getSession()->set('FlashMessage', [
            'Type' => 'info',
            'Message' => 'Anda telah keluar.'
        ]);
        return $this->redirect(Director::absoluteBaseURL());
    }

    // === Reset Password ===
    public function forgotPassword(HTTPRequest $request)
    {
        $validationResult = null;

        if ($request->isPOST()) {
            $validationResult = $this->AuthService->processForgotPassword($request);

            if ($validationResult && $validationResult->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Type' => 'success',
                    'Message' => 'Link atur ulang kata sandi telah dikirim ke email Anda.'
                ]);
                return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
            } else {
                $errorMessages = $validationResult ? $validationResult->getMessages() : [];
                $errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';

                if (!empty($errorMessages)) {
                    $errorMessage = $errorMessages[0]['message'] ?? $errorMessage;
                }

                $this->flashMessages = ArrayData::create([
                    'Type' => 'danger',
                    'Message' => $errorMessage
                ]);
            }
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Lupa Sandi',
            'ValidationResult' => $validationResult,
            'FlashMessages' => $this->getFlashMessages() ?: $this->flashMessages,
        ]);

        return $this->customise($data)->renderWith(['ForgotPasswordPage', 'Page']);
    }

    public function resetPassword(HTTPRequest $request)
    {
        $token = $request->getVar('token');
        $validationResult = null;

        if (!$token) {
            $this->getRequest()->getSession()->set('FlashMessage', [
                'Type' => 'danger',
                'Message' => 'Token reset password tidak ditemukan.'
            ]);
            return $this->redirect(Director::absoluteBaseURL() . '/auth/forgotpassword');
        }

        $user = Member::get()->filter('ResetPasswordToken', $token)->first();
        if (!$user || !$user->ResetPasswordExpiry || strtotime($user->ResetPasswordExpiry) < time()) {
            $this->getRequest()->getSession()->set('FlashMessage', [
                'Type' => 'danger',
                'Message' => 'Tautan atur ulang kata sandi tidak valid atau sudah kedaluwarsa.'
            ]);
            return $this->redirect(Director::absoluteBaseURL() . '/auth/forgotpassword');
        }

        if ($request->isPOST()) {
            $validationResult = $this->AuthService->processResetPassword($request, $user);
            if ($validationResult && $validationResult->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Type' => 'success',
                    'Message' => 'Kata sandi berhasil diatur ulang. Silakan login kembali.'
                ]);
                return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
            } else {
                $errorMessages = $validationResult ? $validationResult->getMessages() : [];
                $errorMessage = 'Gagal mengatur ulang kata sandi. Silakan coba lagi.';

                if (!empty($errorMessages)) {
                    $errorMessage = $errorMessages[0]['message'] ?? $errorMessage;
                }

                $this->flashMessages = ArrayData::create([
                    'Type' => 'danger',
                    'Message' => $errorMessage
                ]);
            }
        }

        $data = array_merge($this->getCommonData(), [
            'Title' => 'Reset Sandi',
            'Token' => $token,
            'ValidationResult' => $validationResult,
            'FlashMessages' => $this->getFlashMessages() ?: $this->flashMessages,
        ]);

        return $this->customise($data)->renderWith(['ResetPasswordPage', 'Page']);
    }

    // === COMMON HELPERS ===
    protected function getCurrentUser()
    {
        return Security::getCurrentUser();
    }

    protected function isLoggedIn()
    {
        return $this->getCurrentUser() !== null;
    }
}