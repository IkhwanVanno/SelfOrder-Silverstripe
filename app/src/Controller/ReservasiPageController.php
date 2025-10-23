<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;

class ReservasiPageController extends PageController
{
    private static $allowed_actions = [
        'index',
    ];

    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            $this->getRequest()->getSession()->set('FlashMessage', [
                'Type' => 'primary',
                'Message' => 'Silahkan login terlebih dahulu.',
            ]);
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }

        return $this->customise($this->getCommonData())->renderWith(['ReservasiPage', 'page']);
    }

}