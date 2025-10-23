<?php

use SilverStripe\Core\Environment;
use SilverStripe\Control\Director;

class PaymentService
{
    public function getPaymentMethods($amount = 10000)
    {
        $merchantCode = Environment::getEnv('DUITKU_MERCHANT_CODE');
        $apiKey = Environment::getEnv('DUITKU_API_KEY');

        $datetime = date('Y-m-d H:i:s');
        $signature = hash('sha256', $merchantCode . $amount . $datetime . $apiKey);

        $params = [
            'merchantcode' => $merchantCode,
            'amount' => $amount,
            'datetime' => $datetime,
            'signature' => $signature
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($params))
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $results = json_decode($response, true);
            return $results['paymentFee'] ?? $results['paymentMethods'] ?? [];
        }

        return [];
    }

    public function getPaymentFee($paymentMethod, $amount)
    {
        $paymentMethods = $this->getPaymentMethods($amount);

        foreach ($paymentMethods as $method) {
            if ($method['paymentMethod'] == $paymentMethod) {
                return $method['totalFee'];
            }
        }

        return 0;
    }

    public function getPaymentMethodName($paymentMethod)
    {
        $paymentMethods = $this->getPaymentMethods();

        foreach ($paymentMethods as $method) {
            if ($method['paymentMethod'] == $paymentMethod) {
                return $method['paymentName'];
            }
        }

        return $paymentMethod;
    }

    public function createDuitkuPayment($payment, $paymentMethod, $amount, $user)
    {
        $merchantCode = Environment::getEnv('DUITKU_MERCHANT_CODE');
        $apiKey = Environment::getEnv('DUITKU_API_KEY');

        $merchantOrderId = $payment->Reference;
        $productDetails = 'Pembayaran Order - ' . $payment->Order()->NomorInvoice;
        $email = $user->Email;
        $phoneNumber = '';
        $additionalParam = '';
        $merchantUserInfo = trim($user->FirstName . ' ' . $user->Surname);
        $customerVaName = $merchantUserInfo;

        $baseUrl = Environment::getEnv('SS_BASE_URL'); 
        $callbackUrl = $baseUrl . '/keranjang/callback';
        $returnUrl = $baseUrl . '/keranjang/return';
        $expiryPeriod = 10; // dalam menit

        $signature = md5($merchantCode . $merchantOrderId . $amount . $apiKey);

        $params = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $amount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => $additionalParam,
            'merchantUserInfo' => $merchantUserInfo,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Environment::getEnv('DUITKU_BASE_URL'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($params))
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $result = json_decode($response, true);
            if (isset($result['paymentUrl'])) {
                $payment->PaymentUrl = $result['paymentUrl'];
                $payment->ExpiryTime = date('Y-m-d H:i:s', strtotime("+{$expiryPeriod} minutes"));
                $payment->Status = 'Pending';
                $payment->write();

                return $result['paymentUrl'];
            }
        }

        return false;
    }

    public function createDuitkuPaymentReservasi($payment, $paymentMethod, $amount, $user, $reservasi)
    {
        $merchantCode = Environment::getEnv('DUITKU_MERCHANT_CODE');
        $apiKey = Environment::getEnv('DUITKU_API_KEY');

        $merchantOrderId = $payment->Reference;
        $productDetails = 'Pembayaran Reservasi - ' . $reservasi->NamaReservasi;
        $email = $user->Email;
        $phoneNumber = '';
        $additionalParam = '';
        $merchantUserInfo = trim($user->FirstName . ' ' . $user->Surname);
        $customerVaName = $merchantUserInfo;

        $baseUrl = Environment::getEnv('SS_BASE_URL');
        $callbackUrl = $baseUrl . '/reservasi/callback';
        $returnUrl = $baseUrl . '/reservasi/return';
        $expiryPeriod = 60;

        $signature = md5($merchantCode . $merchantOrderId . $amount . $apiKey);

        $params = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $amount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => $additionalParam,
            'merchantUserInfo' => $merchantUserInfo,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Environment::getEnv('DUITKU_BASE_URL'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($params))
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $result = json_decode($response, true);
            if (isset($result['paymentUrl'])) {
                $payment->PaymentUrl = $result['paymentUrl'];
                $payment->DuitkuTransactionID = $result['reference'] ?? '';
                $payment->ExpiryTime = date('Y-m-d H:i:s', strtotime("+{$expiryPeriod} minutes"));
                $payment->Status = 'Pending';
                $payment->write();

                return $result['paymentUrl'];
            }
        }

        return false;
    }

    // Tambahkan method baru untuk mengecek expired payments
    public function checkExpiredPayments()
    {
        $expiredPayments = Payment::get()->filter([
            'Status' => 'Pending',
            'ExpiryTime:LessThan' => date('Y-m-d H:i:s')
        ]);

        foreach ($expiredPayments as $payment) {
            $payment->Status = 'Failed';
            $payment->write();

            $order = $payment->Order();
            if ($order) {
                $order->Status = 'Dibatalkan';
                $order->write();
            }
        }
    }

    // Check expired payment reservasi
    public function checkExpiredPaymentReservasi()
    {
        $expiredPayments = PaymentReservasi::get()->filter([
            'Status' => 'Pending',
            'ExpiryTime:LessThan' => date('Y-m-d H:i:s')
        ]);

        foreach ($expiredPayments as $payment) {
            $payment->Status = 'Failed';
            $payment->write();

            $reservasi = $payment->Reservasi();
            if ($reservasi) {
                $reservasi->Status = 'Dibatalkan';
                $reservasi->ResponsAdmin = 'Pembayaran expired';
                $reservasi->write();
            }
        }
    }
}