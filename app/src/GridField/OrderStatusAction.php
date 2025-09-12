<?php

namespace App\GridField;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\GridField\AbstractGridFieldComponent;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\ORM\DataObject;

class OrderStatusAction extends AbstractGridFieldComponent implements
    GridField_ColumnProvider,
    GridField_ActionProvider
{

    // Nama action yang bisa ditangani
    public function getActions($gridField)
    {
        return [
            'setstatusMenungguPembayaran',
            'setstatusDibatalkan',
            'setstatusAntrean',
            'setstatusproses',
            'setstatusterkirim',
        ];
    }

    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'grid-field__col-compact'];
    }

    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName === 'Actions') {
            return ['title' => 'Ubah Status'];
        }
        return [];
    }

    public function getColumnContent($gridField, $record, $columnName)
    {
        if (!$record->canEdit()) {
            return null;
        }

        // MenungguPembayaran (biru muda)
        $btnMenunggu = GridField_FormAction::create(
            $gridField,
            'setstatusMenungguPembayaran' . $record->ID,
            'MenungguPembayaran',
            'setstatusMenungguPembayaran',
            ['RecordID' => $record->ID]
        )->setAttribute('style', 'background-color: #17a2b8; color: white; padding: 5px 10px; border-radius: 4px;');

        // Dibatalkan (merah)
        $btnBatal = GridField_FormAction::create(
            $gridField,
            'setstatusDibatalkan' . $record->ID,
            'Dibatalkan',
            'setstatusDibatalkan',
            ['RecordID' => $record->ID]
        )->setAttribute('style', 'background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 4px;');

        // Antrean (kuning)
        $btnAntrean = GridField_FormAction::create(
            $gridField,
            'setstatusAntrean' . $record->ID,
            'Antrean',
            'setstatusAntrean',
            ['RecordID' => $record->ID]
        )->setAttribute('style', 'background-color: #ffc107; color: black; padding: 5px 10px; border-radius: 4px;');

        // Proses (biru tua)
        $btnProses = GridField_FormAction::create(
            $gridField,
            'setstatusproses' . $record->ID,
            'Proses',
            'setstatusproses',
            ['RecordID' => $record->ID]
        )->setAttribute('style', 'background-color: #007bff; color: white; padding: 5px 10px; border-radius: 4px;');

        // Terkirim (hijau)
        $btnTerkirim = GridField_FormAction::create(
            $gridField,
            'setstatusterkirim' . $record->ID,
            'Terkirim',
            'setstatusterkirim',
            ['RecordID' => $record->ID]
        )->setAttribute('style', 'background-color: #28a745; color: white; padding: 5px 10px; border-radius: 4px;');

        // Gabungkan semua tombol
        return $btnMenunggu->Field() . ' ' .
            $btnBatal->Field() . ' ' .
            $btnAntrean->Field() . ' ' .
            $btnProses->Field() . ' ' .
            $btnTerkirim->Field();
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        // $arguments['RecordID'] dari getColumnContent
        $recordID = $arguments['RecordID'] ?? null;
        if (!$recordID) {
            return;
        }

        $order = \Order::get()->byID($recordID);
        if (!$order) {
            return;
        }

        if (!$order->canEdit()) {
            return;
        }

        switch (strtolower($actionName)) {
            case 'setstatusMenungguPembayaran':
                $order->Status = 'MenungguPembayaran';
                $order->write();
                break;
            case 'setstatusDibatalkan':
                $order->Status = 'Dibatalkan';
                $order->write();
                break;
            case 'setstatusAntrean':
                $order->Status = 'Antrean';
                $order->write();
                break;
            case 'setstatusproses':
                $order->Status = 'Proses';
                $order->write();
                break;
            case 'setstatusterkirim':
                $order->Status = 'Terkirim';
                $order->write();
                break;
            default:
                break;
        }

        Controller::curr()->getResponse()->addHeader('X-Status', rawurlencode("Status diubah ke $order->Status"));
    }
}
