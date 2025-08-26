<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\Security;
use SilverStripe\View\ArrayData;

class AuthPageController extends PageController
{
    private static $allowed_actions = [
        'Login',
        'Register',
        'Logout',
    ];

    private static $url_handlers = [
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
    ];

    // === LOGIN PAGE ===
    public function Login(HTTPRequest $request)
    {
        $result = null;

        if ($request->isPOST()) {
            $result = $this->handleLogin($request);

            if ($result->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Type' => 'primary',
                    'Message' => 'Selamat datang kembali!'
                ]);
                return $this->redirect(Director::absoluteBaseURL());
            } else {
                $this->flashMessages = ArrayData::create([
                    'Type' => 'danger',
                    'Message' => $result->getMessages()[0]['message'] ?? 'Login gagal.'
                ]);
            }
        }

        $data = array_merge($this->getCommontData(), [
            'Title' => 'Login',
            'isLoggedIn' => $this->isLoggedIn(),
            'Member' => $this->getCurrentUser(),
        ]);

        return $this->customise($data)->renderWith(['LoginPage', 'Page']);
    }

    // === REGISTER PAGE ===
    public function Register(HTTPRequest $request)
    {
        $result = null;

        if ($request->isPOST()) {
            $result = $this->handleRegister($request);

            if ($result->isValid()) {
                $this->getRequest()->getSession()->set('FlashMessage', [
                    'Type' => 'success',
                    'Message' => 'Registrasi berhasil! Silakan login.'
                ]);
                return $this->redirect(Director::absoluteBaseURL());
            } else {
                $this->flashMessages = ArrayData::create([
                    'Type' => 'danger',
                    'Message' => $result->getMessages()[0]['message'] ?? 'Registrasi gagal.'
                ]);
            }
        }

        $data = array_merge($this->getCommontData(), [
            'Title' => 'Register',
            'isLoggedIn' => $this->isLoggedIn(),
            'Member' => $this->getCurrentUser(),
        ]);

        return $this->customise($data)->renderWith(['RegisterPage', 'Page']);
    }

    // === LOGOUT ===
    public function Logout(HTTPRequest $request)
    {
        Injector::inst()->get(IdentityStore::class)->logOut($request);
        $this->getRequest()->getSession()->set('FlashMessage', [
            'Type' => 'info',
            'Message' => 'Anda telah keluar.'
        ]);
        return $this->redirect(Director::absoluteBaseURL());
    }

    // === PROSES LOGIN ===
    private function handleLogin(HTTPRequest $request)
    {
        $email = $request->postVar('login_email');
        $password = $request->postVar('login_password');
        $remember = $request->postVar('login_remember');

        $data = [
            'Email' => $email,
            'Password' => $password,
            'Remember' => $remember
        ];

        $authenticator = new MemberAuthenticator();
        $loginHandler = new LoginHandler('auth', $authenticator);

        $validationResult = ValidationResult::create();
        $member = $loginHandler->checkLogin($data, $request, $validationResult);

        $result = ValidationResult::create();

        if ($member) {
            if (!$member->inGroup('site-users')) {
                Injector::inst()->get(IdentityStore::class)->logOut($request);
                $result->addError('Anda tidak memiliki izin untuk masuk.');
            } else {
                $loginHandler->performLogin($member, $data, $request);
            }
        } else {
            $result->addError('Email atau password salah.');
        }

        return $result;
    }

    // === PROSES REGISTER ===
    private function handleRegister(HTTPRequest $request)
    {
        $firstName = $request->postVar('register_first_name');
        $lastName = $request->postVar('register_last_name');
        $email = $request->postVar('register_email');
        $password1 = $request->postVar('register_password_1');
        $password2 = $request->postVar('register_password_2');

        $result = ValidationResult::create();

        if ($password1 !== $password2) {
            $result->addError('Password tidak cocok.');
            return $result;
        }

        if (Member::get()->filter('Email', $email)->exists()) {
            $result->addError('Email sudah terdaftar.');
            return $result;
        }

        $member = Member::create();
        $member->FirstName = $firstName;
        $member->Surname = $lastName;
        $member->Email = $email;
        $member->write();
        $member->addToGroupByCode('site-users');
        $member->changePassword($password1);

        $result->addMessage('Pendaftaran berhasil.');

        return $result;
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
