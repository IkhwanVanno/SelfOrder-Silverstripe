<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice</title>
    <style>
      body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 11px;
        color: #374151;
        margin: 0;
        padding: 20px;
        background-color: #f9fafb;
      }

      .container {
        max-width: 800px;
        margin: 0 auto;
        border: 1px solid #d1d5db;
        background-color: #fff;
        padding: 25px;
      }

      .header {
        display: table;
        width: 100%;
        table-layout: fixed;
        margin-bottom: 30px;
      }

      .logo-container {
        display: table-cell;
        width: 150px;
        vertical-align: top;
      }

      .logo-container img {
        max-height: 50px;
        max-width: 130px;
      }

      .invoice-info {
        display: table-cell;
        width: 240px;
        vertical-align: top;
        text-align: center;
        padding: 0 10px;
        font-size: 11px;
      }

      .invoice-info h2 {
        font-size: 16px;
        font-weight: bold;
        margin: 0 0 6px;
      }

      .invoice-info p {
        margin: 2px 0;
      }

      .company-info {
        display: table-cell;
        width: 200px;
        vertical-align: top;
        text-align: right;
        word-break: break-word;
        max-width: 200px;
        font-size: 10px;
      }

      .company-info p {
        margin: 2px 0;
        line-height: 1.3;
      }

      .customer-info {
        margin-bottom: 25px;
      }

      .customer-info h3 {
        font-weight: bold;
        color: #374151;
        margin-bottom: 8px;
        font-size: 11px;
      }

      table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
        font-size: 10.5px;
      }

      th,
      td {
        border: 1px solid #d1d5db;
        padding: 6px;
        text-align: left;
      }

      th {
        background-color: #f3f4f6;
        font-weight: bold;
      }

      .text-right {
        text-align: right;
      }

      .summary-table {
        width: 50%;
        margin-left: auto;
        border-collapse: collapse;
        font-size: 10.5px;
      }

      .summary-table td {
        border: none;
        padding: 4px 8px;
      }

      .summary-table .total-row {
        border-top: 1px solid #374151;
        font-weight: bold;
        font-size: 11px;
      }

      .notes {
        margin-top: 25px;
        color: #6b7280;
        font-size: 9.5px;
      }

      .currency {
        white-space: nowrap;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <!-- Header -->
      <div class="header">
        <div class="logo-container">
          <% if $LogoURL %>
          <img src="$LogoURL" alt="Logo Perusahaan" />
          <% end_if %>
        </div>
        <div class="invoice-info">
          <h2>INVOICE</h2>
          <p><strong>Nomor:</strong> $InvoiceNumber</p>
          <p><strong>Tanggal:</strong> $InvoiceDate</p>
          <p><strong>Meja:</strong> $TableNumber</p>
        </div>
        <div class="company-info">
          <p><strong>$CompanyName</strong></p>
          <% if $CompanyAddress %>
          <p>$CompanyAddress</p>
          <% end_if %>
          <% if $CompanyEmail %>
          <p>$CompanyEmail</p>
          <% end_if %>
        </div>
      </div>

      <!-- Customer Info -->
      <div class="customer-info">
        <h3>Kepada:</h3>
        <p><strong>$CustomerName</strong></p>
        <p>$CustomerEmail</p>
      </div>

      <!-- Product Table -->
      <table>
        <thead>
          <tr>
            <th>Produk</th>
            <th class="text-right">Harga Satuan</th>
            <th class="text-right">Kuantitas</th>
            <th class="text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          <% loop $OrderItems %>
          <tr>
            <td>$Produk.Nama</td>
            <td class="text-right currency">Rp {$HargaSatuan.Number}</td>
            <td class="text-right">$Kuantitas</td>
            <td class="text-right currency">Rp {$Subtotal.Number}</td>
          </tr>
          <% end_loop %>
        </tbody>
      </table>

      <!-- Summary -->
      <table class="summary-table">
        <tr>
          <td>Subtotal</td>
          <td class="text-right currency">Rp $FormattedSubtotal</td>
        </tr>
        <tr>
          <td>Biaya Pembayaran ($PaymentMethod)</td>
          <td class="text-right currency">Rp $FormattedPaymentFee</td>
        </tr>
        <tr class="total-row">
          <td>Total</td>
          <td class="text-right currency">Rp $FormattedTotal</td>
        </tr>
      </table>

      <!-- Notes -->
      <div class="notes">
        <p>* Mohon simpan invoice ini sebagai bukti transaksi Anda.</p>
        <p>* Pembayaran sudah termasuk PPN jika berlaku.</p>
        <p>* Terima kasih atas kepercayaan Anda!</p>
      </div>
    </div>
  </body>
</html>
