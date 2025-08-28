<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\View\ArrayData;

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

        $paymentService = new PaymentService();
        $paymentService->checkExpiredPayments();

        $user = $this->getCurrentUser();
        $orderList = Order::get()->filter('MemberID', $user->ID)->sort('Created', 'DESC');

        $session = $this->getRequest()->getSession();
        $flashMessage = $session->get('FlashMessage');
        if ($flashMessage) {
            $session->clear('FlashMessage');
        }

        $data = array_merge($this->getCommonData(), [
            'OrderList' => $orderList,
            'FlashMessage' => $flashMessage ? new ArrayData($flashMessage) : null
        ]);
        return $this->customise($data)->renderWith(['OrderPage', 'Page']);
    }
}