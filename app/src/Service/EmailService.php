<?php

use Dompdf\Dompdf;
use Dompdf\Options;
use SilverStripe\Control\Email\Email;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

class EmailService
{
    private $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    public function sendInvoiceEmail($order)
    {
        $user = $order->Member();
        $siteConfig = SiteConfig::current_site_config();
        $companyEmail = $siteConfig->Email;

        if (!$companyEmail) {
            error_log("Company email not configured");
            return false;
        }

        $emailData = $this->prepareInvoiceData($order, $user, $siteConfig);
        $pdfContent = $this->generateInvoicePDF($order, $user, $siteConfig);

        $tmpFile = TEMP_FOLDER . '/Invoice-' . $order->NomorInvoice . '.pdf';
        file_put_contents($tmpFile, $pdfContent);

        $email = Email::create()
            ->setFrom($companyEmail)
            ->setTo($user->Email)
            ->setSubject('Invoice Pembayaran - ' . $order->NomorInvoice)
            ->setHTMLTemplate('InvoiceEmail');

        $email->addAttachment($tmpFile, 'Invoice-' . $order->NomorInvoice . '.pdf', 'application/pdf');

        if ($siteConfig->Logo && $siteConfig->Logo->exists()) {
            $logoName = $siteConfig->Logo->Name;
            $fullLogoPath = BASE_PATH . '/public/assets/Uploads/' . $logoName;

            if (file_exists($fullLogoPath)) {
                $logoData = file_get_contents($fullLogoPath);
                $imageInfo = getimagesize($fullLogoPath);
                $logoMimeType = $imageInfo['mime'] ?? 'image/png';
                $logoExtension = pathinfo($logoName, PATHINFO_EXTENSION);
                $logoFilename = 'company-logo.' . $logoExtension;

                $email->addAttachmentFromData(
                    $logoData,
                    $logoFilename,
                    $logoMimeType,
                );
                $emailData->setField('LogoCID', 'cid:' . $logoFilename);
            }
        }

        $email->setData($emailData);
        $email->send();
    }

    public function generateInvoicePDF($order, $user, $siteConfig)
    {
        $pdfData = $this->prepareInvoiceData($order, $user, $siteConfig);

        $viewer = SSViewer::create(['InvoicePDF']);
        $htmlContent = $viewer->process($pdfData);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function prepareInvoiceData($order, $user, $siteConfig)
    {
        $orderItems = $order->OrderItems();
        $subtotal = 0;

        foreach ($orderItems as $item) {
            $subtotal += $item->getSubtotal();
        }

        $payment = $order->Payment();

        if (!$payment) {
            $payment = Payment::get()->filter('OrderID', $order->ID)->first();
        }

        $paymentFee = 0;
        $paymentMethod = '';

        if ($payment) {
            $paymentFee = $order->TotalHarga - $subtotal;
            $paymentMethod = $payment->MetodePembayaran;
        }

        $invoiceDate = date('Y-m-d');
        if ($order->Created) {
            $invoiceDate = date('Y-m-d', strtotime($order->Created));
        }

        // Format currency values
        $formattedSubtotal = number_format($subtotal, 0, ',', '.');
        $formattedPaymentFee = number_format($paymentFee, 0, ',', '.');
        $formattedTotal = number_format($order->TotalHarga, 0, ',', '.');

        $data = [
            'CustomerName' => trim($user->FirstName . ' ' . $user->Surname),
            'CustomerEmail' => $user->Email,
            'CompanyName' => $siteConfig->Title ?: 'Perusahaan Kami',
            'CompanyEmail' => $siteConfig->Email,
            'CompanyAddress' => $siteConfig->Address,
            'InvoiceNumber' => $order->NomorInvoice,
            'InvoiceDate' => $invoiceDate,
            'OrderItems' => $orderItems,
            'Subtotal' => $subtotal,
            'FormattedSubtotal' => $formattedSubtotal,
            'PaymentFee' => $paymentFee,
            'FormattedPaymentFee' => $formattedPaymentFee,
            'Total' => $order->TotalHarga,
            'FormattedTotal' => $formattedTotal,
            'TableNumber' => $order->NomorMeja,
            'PaymentMethod' => $paymentMethod,
        ];

        if ($siteConfig->Logo && $siteConfig->Logo->exists()) {
            $data['Logo'] = $siteConfig->Logo;
            $data['LogoURL'] = $siteConfig->Logo->getAbsoluteURL();
        }

        return new ArrayData($data);
    }
}