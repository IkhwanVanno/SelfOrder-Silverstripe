<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;

class OrderPageController extends PageController
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
        $user = $this->getCurrentUser();
        $orderList = Order::get()->filter('MemberID', $user->ID)->sort('Created', 'DESC');
        $data = array_merge($this->getCommonData(), [
            'OrderList' => $orderList,
        ]);
        return $this->customise($data)->renderWith(['OrderPage', 'Page']);
    }
}