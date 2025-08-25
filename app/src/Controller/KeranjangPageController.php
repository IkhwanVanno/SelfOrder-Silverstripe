<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

class KeranjangPageController extends PageController
{
    private static $allowed_actions = [
        'index',
        'addToCart',
        'updateCart',
        'removeFromCart'
    ];
    
    private static $url_segment = 'keranjang';
    
    private static $url_handlers = [
        'add-to-cart' => 'addToCart',
        'update-cart' => 'updateCart',
        'remove-from-cart' => 'removeFromCart',
        '' => 'index'
    ];
    
    protected function init()
    {
        parent::init();
        // You can include any CSS or JS required for the keranjang page here
    }
    
    public function index(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }
        
        $user = $this->getCurrentUser();
        $cartItems = CartItem::get()->filter('MemberID', $user->ID);
 
        $totalHarga = 0;
        foreach ($cartItems as $item) {
            $totalHarga += $item->getSubtotal();
        }
        
        $data = array_merge($this->getCommontData(), [
            'CartItems' => $cartItems,
            'TotalHarga' => $totalHarga
        ]);
        
        return $this->customise($data)->renderWith(['KeranjangPage', 'Page']);
    }
    
    public function addToCart(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }
        
        $produkID = $request->postVar('produk_id');
        $kuantitas = (int)$request->postVar('kuantitas') ?: 1;
        
        if (!$produkID) {
            return $this->redirectBack();
        }
        
        $user = $this->getCurrentUser();
        $produk = Produk::get()->byID($produkID);
        
        if (!$produk) {
            return $this->redirectBack();
        }
        
        $existingItem = CartItem::get()->filter([
            'MemberID' => $user->ID,
            'ProdukID' => $produkID
        ])->first();
        
        if ($existingItem) {
            $existingItem->Kuantitas += $kuantitas;
            $existingItem->write();
        } else {
            $cartItem = CartItem::create();
            $cartItem->MemberID = $user->ID;
            $cartItem->ProdukID = $produkID;
            $cartItem->Kuantitas = $kuantitas;
            $cartItem->write();
        }
        
        return $this->redirectBack();
    }
    
    public function updateCart(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }
        
        $cartItemID = $request->postVar('cart_item_id');
        $action = $request->postVar('action'); // 'increase' or 'decrease'
        
        if (!$cartItemID || !$action) {
            return $this->redirectBack();
        }
        
        $user = $this->getCurrentUser();
        $cartItem = CartItem::get()->filter([
            'ID' => $cartItemID,
            'MemberID' => $user->ID
        ])->first();
        
        if (!$cartItem) {
            return $this->redirectBack();
        }
        
        if ($action === 'increase') {
            $cartItem->Kuantitas++;
            $cartItem->write();
        } elseif ($action === 'decrease') {
            if ($cartItem->Kuantitas > 1) {
                $cartItem->Kuantitas--;
                $cartItem->write();
            } else {
                $cartItem->delete();
            }
        }
        
        return $this->redirectBack();
    }
    
    public function removeFromCart(HTTPRequest $request)
    {
        if (!$this->isLoggedIn()) {
            return $this->redirect(Director::absoluteBaseURL() . '/auth/login');
        }
        
        $cartItemID = $request->postVar('cart_item_id');
        
        if (!$cartItemID) {
            return $this->redirectBack();
        }
        
        $user = $this->getCurrentUser();
        $cartItem = CartItem::get()->filter([
            'ID' => $cartItemID,
            'MemberID' => $user->ID
        ])->first();
        
        if (!$cartItem) {
            return $this->redirectBack();
        }
        
        $cartItem->delete();
        
        return $this->redirectBack();
    }
}