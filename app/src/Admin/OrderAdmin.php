<?php

use App\GridField\OrderStatusAction;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;

class OrderAdmin extends ModelAdmin
{
    private static $managed_models = [
        Order::class,
    ];

    private static $url_segment = 'orders';

    private static $menu_title = 'Orders';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);
        $gridName = $this->sanitiseClassName(Order::class);
        $grid = $form->Fields()->dataFieldByName($gridName);

        if ($grid) {
            $config = $grid->getConfig();
            $config->addComponent(new OrderStatusAction());
        }

        return $form;
    }
}
