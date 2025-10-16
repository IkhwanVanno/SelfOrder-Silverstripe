<?php

use SilverStripe\Reports\Report;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\View\Requirements;

class PaymentReport extends Report
{
    public function title()
    {
        return 'Laporan Payment';
    }

    public function sourceRecords($params, $sort = null, $limit = null)
    {
        return Payment::get()->sort('Created DESC');
    }

    public function parameterFields()
    {
        return false;
    }

    public function getReportField()
    {
        $records = Payment::get();
        $dailyCounts = [];

        foreach ($records as $payment) {
            $date = date('Y-m-d', strtotime($payment->Created));
            if (!isset($dailyCounts[$date])) {
                $dailyCounts[$date] = 0;
            }
            $dailyCounts[$date]++;
        }

        ksort($dailyCounts);

        $labels = json_encode(array_keys($dailyCounts));
        $data = json_encode(array_values($dailyCounts));

        Requirements::javascript('https://cdn.jsdelivr.net/npm/chart.js');
        $html = <<<HTML
            <style>
                .report-container {
                    background: #fff;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                    margin-top: 15px;
                    width: 100%;
                    max-width: 900px;
                    margin-left: auto;
                    margin-right: auto;
                }
                .report-title {
                    font-size: 20px;
                    font-weight: bold;
                    margin-bottom: 15px;
                    color: #333;
                    text-align: center;
                }
                canvas {
                    width: 100% !important;
                    height: 400px !important;
                }
            </style>

            <div class="report-container">
                <div class="report-title">Grafik Jumlah Payment</div>
                <canvas id="paymentChart"></canvas>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('paymentChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: $labels,
                            datasets: [{
                                label: 'Jumlah Payment',
                                data: $data,
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
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
                            },
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                });
            </script>
        HTML;

        return LiteralField::create('PaymentChart', $html);
    }
}