<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\Security;

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

    public function Login(HTTPRequest $request)
    {
        $result = null;
        if ($request->isPOST()) {
            $result = $this->processLogin($request);
            if ($result->isValid()) {
                return $this->redirect(Director::absoluteBaseURL());
            }
        }

        $data = array_merge($this->getCommontData(), [
            'LoginResult' => $result,
            'isLoggedIn' => Security::getCurrentUser() !== null,
            'Member' => Security::getCurrentUser(),
        ]);

        return $this->customise($data)->renderWith(['LoginPage', 'Page']);
    }

    public function Register(HTTPRequest $request)
    {
        $result = null;
        if ($request->isPOST()) {
            $result = $this->processRegister($request);
            if ($result->isValid()) {
                return $this->redirect(Director::absoluteBaseURL());
            }
        }

        $data = array_merge($this->getCommontData(), [
            'RegisterResult' => $result,
            'isLoggedIn' => Security::getCurrentUser() !== null,
            'Member' => Security::getCurrentUser(),
        ]);

        return $this->customise($data)->renderWith(['RegisterPage', 'Page']);
    }

    public function Logout(HTTPRequest $request)
    {
        Injector::inst()->get(IdentityStore::class)->logOut($request);
        return $this->redirect(Director::absoluteBaseURL());
    }

    private function processLogin(HTTPRequest $request)
    {
        $email = $request->postVar('login_email');
        $password = $request->postVar('login_password');
        $rememberMe = $request->postVar('login_remember');

        $result = ValidationResult::create();
        $authenticator = new MemberAuthenticator();
        $loginHandler = new LoginHandler('auth', $authenticator);

        $data = [
            'Email' => $email,
            'Password' => $password,
            'Remember' => $rememberMe
        ];

        if ($member = $loginHandler->checkLogin($data, $request, $result)) {
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

    private function processRegister(HTTPRequest $request)
    {
        $firstName = $request->postVar('register_first_name');
        $lastName = $request->postVar('register_last_name');
        $userEmail = $request->postVar('register_email');
        $password1 = $request->postVar('register_password_1');
        $password2 = $request->postVar('register_password_2');

        $result = ValidationResult::create();

        if ($password1 !== $password2) {
            $result->addError('Passwords do not match.');
            return $result;
        }

        if (Member::get()->filter('Email', $userEmail)->exists()) {
            $result->addError('Email already exists.');
            return $result;
        }

        $member = Member::create();
        $member->FirstName = $firstName;
        $member->Surname = $lastName;
        $member->Email = $userEmail;
        $member->write();
        $member->addToGroupByCode('site-users');
        $member->changePassword($password1);
        $result->addMessage('Registrasi berhasil!');
        return $result;
    }

}
