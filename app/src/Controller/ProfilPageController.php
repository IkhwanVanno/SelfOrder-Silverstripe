<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;
use SilverStripe\View\ArrayData;

class ProfilPageController extends PageController
{
    private static $allowed_actions = [
        'index',
        'doUpdateProfil',
    ];

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            $this->getRequest()->getSession()->set('FlashMessage', [
                'Type' => 'primary',
                'Message' => 'Silahkan login terlebih dahulu.',
            ]);
            return $this->redirect(Director::absoluteBaseURL() . 'Auth/login');
        }
        $user = $this->getCurrentUser();
        $profilData = Member::get()->byID($user->ID);

        $session = $this->getRequest()->getSession();
        $flash = $session->get('FlashMessage');
        $session->clear('FlashMessage');

        $data = array_merge($this->getCommontData(), [
            'ProfilData' => $profilData,
            'FlashMessage' => $flash ? ArrayData::create($flash) : null,
        ]);
        return $this->customise($data)->renderWith(['ProfilPage', 'Page']);
    }
    public function doUpdateProfil(HTTPRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return $this->redirectBack();
        }

        $data = $request->postVars();

        $user->FirstName = $data['firstname'] ?? $user->FirstName;
        $user->Surname = $data['surname'] ?? $user->Surname;
        $user->Email = $data['email'] ?? $user->Email;

        if (!empty($data['password'])) {
            $newPassword = $data['password'];
            $user->Password = $newPassword;
        }

        try {
            $user->write();

            $this->getRequest()->getSession()->set('FlashMessage', [
                'Type' => 'success',
                'Message' => 'Profil berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            $this->getRequest()->getSession()->set('FlashMessage', [
                'Type' => 'danger',
                'Message' => 'Password tidak valid atau sudah pernah digunakan.',
            ]);
        }

        return $this->redirectBack();
    }

}