<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\SiteConfig\SiteConfig;

class AuthService
{
    // === Auth Service ===
    public function handleLogin(HTTPRequest $request)
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
        $user = $loginHandler->checkLogin($data, $request, $validationResult);

        $result = ValidationResult::create();

        if ($user) {
            if (!$user->inGroup('site-users')) {
                Injector::inst()->get(IdentityStore::class)->logOut($request);
                $result->addError('Anda tidak memiliki izin untuk masuk.');
            } else {
                $loginHandler->performLogin($user, $data, $request);
            }
        } else {
            $result->addError('Email atau password salah.');
        }

        return $result;
    }

    public function handleRegister(HTTPRequest $request)
    {
        $firstName = $request->postVar('register_first_name');
        $lastName = $request->postVar('register_last_name');
        $email = $request->postVar('register_email');
        $password1 = $request->postVar('register_password_1');
        $password2 = $request->postVar('register_password_2');

        $result = ValidationResult::create();

        if (!$firstName) {
            $result->addError('Nama depan harus diisi.');
        }

        if (!$email) {
            $result->addError('Email harus diisi.');
        }

        if (!$password1 || !$password2) {
            $result->addError('Password harus diisi.');
        }

        if ($password1 !== $password2) {
            $result->addError('Password tidak cocok.');
        }

        if (strlen($password1) < 8) {
            $result->addError('Password minimal 8 karakter.');
        }

        if (Member::get()->filter('Email', $email)->exists()) {
            $result->addError('Email sudah terdaftar.');
        }

        if (!$result->isValid()) {
            return $result;
        }

        try {
            $user = Member::create();
            $user->FirstName = $firstName;
            $user->Surname = $lastName;
            $user->Email = $email;
            $user->write();
            $user->addToGroupByCode('site-users');
            $user->changePassword($password1);

            $result->addMessage('Pendaftaran berhasil.');
        } catch (ValidationException $e) {
            $result->addError('Gagal mendaftarkan akun. Silakan coba lagi.');
        }

        return $result;
    }

    // === Google Auth Service ===
    public function getGoogleAccessToken($code, $config)
    {
        $postData = [
            'code' => $code,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($config['token_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to exchange code for token. HTTP Code: ' . $httpCode);
        }

        return json_decode($response, true);
    }

    public function getGoogleUserInfo($accessToken, $config)
    {
        $ch = curl_init($config['userinfo_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to get user info. HTTP Code: ' . $httpCode);
        }

        return json_decode($response, true);
    }

    // === Forgot Password Service ===
    public function processForgotPassword(HTTPRequest $request, $emailParam = null)
    {
        $email = $emailParam ?: $request->postVar('forgot_email');
        $result = ValidationResult::create();

        if (!$email) {
            $result->addError('Email harus diisi.');
            return $result;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result->addError('Format email tidak valid.');
            return $result;
        }

        $user = Member::get()->filter('Email', $email)->first();
        if (!$user) {
            $result->addError('Email tidak ditemukan.');
            return $result;
        }

        try {
            $resetToken = sha1(uniqid() . time() . $email);

            $user->ResetPasswordToken = $resetToken;
            $user->ResetPasswordExpiry = date('Y-m-d H:i:s', time() + 3600);
            $user->write();

            $baseUrl = Environment::getEnv('SS_BASE_URL') ?: Director::absoluteBaseURL();
            $SiteConfig = SiteConfig::current_site_config();
            $CompanyEmail = $SiteConfig->Email ?: 'noreply@' . $_SERVER['HTTP_HOST'];

            $resetLink = rtrim($baseUrl, '/') . '/auth/resetpassword?token=' . $resetToken;

            $email = Email::create()
                ->setTo($user->Email)
                ->setFrom($CompanyEmail)
                ->setSubject('Atur ulang kata sandi')
                ->setHTMLTemplate('ResetPasswordEmail')
                ->setData([
                    'CustomerName' => trim($user->FirstName . ' ' . $user->Surname),
                    'CompanyEmail' => $CompanyEmail,
                    'CustomerEmail' => $user->Email,
                    'ResetLink' => $resetLink
                ]);

            try {
                $email->send();
                $result->addMessage('Link atur ulang sandi telah dikirim ke email Anda.');
            } catch (ValidationException $eror) {
                $result->addError('Gagal mengirim email. Silakan coba lagi.');
            }
        } catch (Exception $e) {
            $result->addError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }

        return $result;
    }

    public function processResetPassword(HTTPRequest $request, Member $user)
    {
        $password1 = $request->postVar('new_password_1');
        $password2 = $request->postVar('new_password_2');

        $result = ValidationResult::create();

        if (!$password1 || !$password2) {
            $result->addError('Kata sandi harus diisi.');
            return $result;
        }

        if ($password1 !== $password2) {
            $result->addError('Kata sandi tidak sama.');
            return $result;
        }

        if (strlen($password1) < 8) {
            $result->addError('Kata sandi minimal 8 karakter.');
            return $result;
        }

        try {
            $user->changePassword($password1);
            $user->ResetPasswordToken = null;
            $user->ResetPasswordExpiry = null;
            $user->write();

            $result->addMessage('Kata sandi berhasil diubah.');
        } catch (ValidationException $e) {
            $result->addError('Kata sandi tidak valid. Periksa kembali kata sandi baru Anda.');
        } catch (Exception $e) {
            $result->addError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }

        return $result;
    }
}