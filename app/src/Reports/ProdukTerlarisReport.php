<?php

use SilverStripe\Forms\LiteralField;
use SilverStripe\Reports\Report;
use SilverStripe\Forms\FieldList;
use SilverStripe\View\Requirements;

class ProdukTerlarisReport extends Report
{
    public function title()
    {
        return 'Laporan Produk Terlaris';
    }

    public function sourceRecords($params, $sort = null, $limit = null)
    {
        return OrderItem::get()
            ->innerJoin('Order', '"Order"."ID" = "OrderItem"."OrderID"')
            ->sort('Kuantitas DESC');
    }

    public function parameterFields()
    {
        return false;
    }

    public function getReportField()
    {
        $records = OrderItem::get()
            ->innerJoin('Order', '"Order"."ID" = "OrderItem"."OrderID"')
            ->innerJoin('Produk', '"Produk"."ID" = "OrderItem"."ProdukID"');

        $produkCounts = [];

        foreach ($records as $item) {
            $nama = $item->Produk()->Nama ?? '(Tidak Ada)';
            $produkCounts[$nama] = ($produkCounts[$nama] ?? 0) + $item->Kuantitas;
        }

        arsort($produkCounts);

        $labels = json_encode(array_keys($produkCounts));
        $data = json_encode(array_values($produkCounts));

        Requirements::javascript('https://cdn.jsdelivr.net/npm/chart.js');
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
                h3 {
                    text-align: center;
                }
                canvas {
                    width: 100% !important;
                    height: 400px !important;
                }
            </style>

            <div class="chart-container">
                <h3>Produk Terlaris</h3>
                <canvas id="produkChart"></canvas>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('produkChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: $labels,
                            datasets: [{
                                label: 'Jumlah Terjual',
                                data: $data,
                                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: { display: false }
                            },
                            scales: { 
                                x: { 
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                } 
                            }
                        }
                    });
                });
            </script>
        HTML;

        return LiteralField::create('ProdukChart', $html);
    }
}