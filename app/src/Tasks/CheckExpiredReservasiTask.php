<?php

use SilverStripe\Dev\BuildTask;

/**
 * Task untuk mengecek dan update status reservasi yang expired
 * 
 * Jalankan dengan: sake dev/tasks/CheckExpiredReservasiTask
 * 
 * Atau setup cron job:
 * /5 * * * * /path/to/sake dev/tasks/CheckExpiredReservasiTask
 */
class CheckExpiredReservasiTask extends BuildTask
{
    private static $segment = 'CheckExpiredReservasiTask';

    protected $title = 'Check Expired Reservasi and Payments';

    protected $description = 'Check and update expired reservations and payments';

    public function run($request)
    {
        echo "Starting expired reservasi check...\n";

        $reservasiService = new ReservasiService();
        $paymentService = new PaymentService();

        // Check expired reservations
        echo "Checking expired reservations...\n";
        $reservasiService->checkExpiredReservations();

        // Check expired payments
        echo "Checking expired payment reservasi...\n";
        $paymentService->checkExpiredPaymentReservasi();

        // Get statistics
        $totalExpiredReservasi = Reservasi::get()->filterAny([
            'Status' => ['Dibatalkan', 'Ditolak'],
            'ResponsAdmin:PartialMatch' => 'otomatis'
        ])->count();

        $totalExpiredPayment = PaymentReservasi::get()->filter([
            'Status' => 'Failed',
            'ExpiryTime:LessThan' => date('Y-m-d H:i:s')
        ])->count();

        echo "\nCompleted!\n";
        echo "Total expired reservations: {$totalExpiredReservasi}\n";
        echo "Total expired payments: {$totalExpiredPayment}\n";
    }
}