<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tanda Terima Reservasi</title>
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
        background-color: #ffffff;
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

      .title-info {
        display: table-cell;
        width: 240px;
        vertical-align: top;
        text-align: center;
        padding: 0 10px;
      }

      .title-info h2 {
        font-size: 16px;
        font-weight: bold;
        margin: 0 0 6px;
      }

      .title-info p {
        margin: 2px 0;
      }

      .company-info {
        display: table-cell;
        width: 200px;
        vertical-align: top;
        text-align: right;
        font-size: 10px;
        line-height: 1.3;
      }

      .company-info p {
        margin: 2px 0;
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

      .reservation-details {
        border: 1px solid #d1d5db;
        border-radius: 4px;
        background-color: #f9fafb;
        padding: 15px;
        margin-bottom: 25px;
        font-size: 10.5px;
      }

      .reservation-details p {
        margin: 5px 0;
      }

      .reservation-details span {
        font-weight: 600;
        color: #111827;
      }

      .status {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: bold;
        color: #065f46;
        background-color: #d1fae5;
      }

      .notes {
        font-size: 9.5px;
        color: #6b7280;
        margin-top: 20px;
      }

      .footer {
        margin-top: 25px;
        font-size: 10.5px;
        text-align: center;
      }

      .footer strong {
        color: #111827;
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
        <div class="title-info">
          <h2>TANDA TERIMA RESERVASI</h2>
          <p><strong>Nomor:</strong> $ReservationNumber</p>
          <p><strong>Tanggal:</strong> $ReservationDate</p>
        </div>
        <div class="company-info">
          <p><strong>$CompanyName</strong></p>
          <% if $CompanyAddress %><p>$CompanyAddress</p><% end_if %>
          <% if $CompanyEmail %><p>$CompanyEmail</p><% end_if %>
        </div>
      </div>

      <!-- Customer Info -->
      <div class="customer-info">
        <h3>Data Pemesan:</h3>
        <p><strong>$CustomerName</strong></p>
        <p>$CustomerEmail</p>
      </div>

      <!-- Reservation Details -->
      <div class="reservation-details">
        <p>Nama Reservasi: <span>$ReservationName</span></p>
        <p>Waktu Mulai: <span>$StartTime</span></p>
        <p>Waktu Selesai: <span>$EndTime</span></p>
        <p>Jumlah Kursi: <span>$JumlahKursi</span></p>
        <p>Total Harga: <span>Rp $FormattedTotal</span></p>
        <p>Status: <span class="status">$Status</span></p>
        <% if $Catatan %>
        <p>Catatan: <span>$Catatan</span></p>
        <% end_if %>
        <% if $ResponsAdmin %>
        <p>Respons Admin: <span>$ResponsAdmin</span></p>
        <% end_if %>
      </div>

      <!-- Notes -->
      <div class="notes">
        <p>* Simpan tanda terima ini sebagai bukti reservasi Anda.</p>
        <p>* Reservasi hanya berlaku sesuai jadwal yang tertera di atas.</p>
        <p>* Hubungi kami melalui email untuk perubahan atau pembatalan.</p>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p>Terima kasih atas kepercayaan Anda!</p>
        <p><strong>Tim $CompanyName</strong></p>
      </div>
    </div>
  </body>
</html>
