<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Invoice Terima Kasih</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f3f4f6;
      margin: 0;
      padding: 20px;
    }

    .invoice-container {
      max-width: 600px;
      margin: 40px auto;
      background-color: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      padding: 30px;
    }

    .logo {
      display: block;
      margin: 0 auto 20px auto;
      max-width: 120px;
    }

    h2 {
      font-size: 24px;
      color: #1f2937;
      margin-bottom: 16px;
      text-align: center;
    }

    p {
      color: #374151;
      margin-bottom: 14px;
      line-height: 1.5;
    }

    .invoice-details {
      background-color: #f9fafb;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 16px;
      margin-bottom: 20px;
    }

    .invoice-details p {
      font-size: 14px;
      color: #4b5563;
      margin: 6px 0;
    }

    .invoice-details span {
      color: #111827;
      font-weight: 600;
    }

    a {
      color: #2563eb;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    .footer {
      margin-top: 30px;
      font-size: 14px;
    }

    .footer span {
      font-weight: bold;
      color: #111827;
    }
  </style>
</head>
<body>

  <div class="invoice-container">
    <% if $LogoCID %>
        <img src="$LogoCID" alt="Logo Perusahaan" class="logo" />
    <% end_if %>

    <h2>Terima Kasih atas Pembelian Anda!</h2>

    <p>Halo <strong>$CustomerName</strong>,</p>

    <p>
      Kami mengucapkan terima kasih karena telah mempercayai kami untuk kebutuhan Anda.
      Berikut adalah detail invoice untuk pesanan Anda.
    </p>

    <div class="invoice-details">
      <p>Nomor Invoice: <span>$InvoiceNumber</span></p>
      <p>Tanggal: <span>$InvoiceDate</span></p>
      <p>Nomor Meja: <span>$TableNumber</span></p>
      <% if $PaymentMethodName %>
      <p>Metode Pembayaran: <span>$PaymentMethodName</span></p>
      <% end_if %>
      <p>Total: <span>Rp $FormattedTotal</span></p>
    </div>

    <p>
      Invoice lengkap terlampir dalam file PDF. Jika ada pertanyaan, silakan hubungi kami di 
      <a href="mailto:$CompanyEmail">$CompanyEmail</a>.
    </p>

    <p class="footer">
      Salam hangat,<br/>
      <span>Tim $CompanyName</span>
    </p>
  </div>

</body>
</html>