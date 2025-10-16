<?php

use SilverStripe\Forms\LiteralField;
use SilverStripe\Reports\Report;
use SilverStripe\View\Requirements;

class OrderReport extends Report
{
    public function title()
    {
        return 'Laporan Order';
    }

    public function sourceRecords($params, $sort, $limit)
    {
        return Order::get()->sort('Created DESC');
    }

    public function columns()
    {
        return [
            'NomorInvoice' => 'Nomor Invoice',
            'TotalHarga' => 'Total Harga',
            'Status' => 'Status',
            'Created' => 'Tanggal Order'
        ];
    }

    public function parameterFields()
    {
        return false;
    }

    public function getReportField()
    {
        Requirements::javascript('https://cdn.jsdelivr.net/npm/chart.js');

        $orders = Order::get();
        $orderCounts = [];

        foreach ($orders as $order) {
            $tanggal = date('Y-m-d', strtotime($order->Created));
            if (!isset($orderCounts[$tanggal])) {
                $orderCounts[$tanggal] = 0;
            }
            $orderCounts[$tanggal]++;
        }

        ksort($orderCounts);

        $labels = json_encode(array_keys($orderCounts));
        $values = json_encode(array_values($orderCounts));

        $html = <<<HTML
        <style>
            .chart-container {
                width: 100%;
                max-width: 900px;
                margin: 30px auto;
                background: #fff;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            }
            canvas {
                width: 100% !important;
                height: 400px !important;
            }
        </style>

        <div class="chart-container">
            <h3 style="text-align:center;">Grafik Jumlah Order Berdasarkan Waktu</h3>
            <canvas id="orderChart"></canvas>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('orderChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: $labels,
                        datasets: [{
                            label: 'Jumlah Order',
                            data: $values,
                            backgroundColor: '#36A2EB',
                            borderColor: '#1d8adb',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            });
        </script>
        HTML;

        return LiteralField::create('OrderChart', $html);
    }
}