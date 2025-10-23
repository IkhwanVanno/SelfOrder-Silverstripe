<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tanda Terima Reservasi</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f3f4f6;
      margin: 0;
      padding: 20px;
    }

    .receipt-container {
      max-width: 600px;
      margin: 40px auto;
      background-color: #ffffff;
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

    .receipt-details {
      background-color: #f9fafb;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 16px;
      margin-bottom: 20px;
    }

    .receipt-details p {
      font-size: 14px;
      color: #4b5563;
      margin: 6px 0;
    }

    .receipt-details span {
      color: #111827;
      font-weight: 600;
    }

    .status {
      display: inline-block;
      background-color: #d1fae5;
      color: #065f46;
      font-weight: 600;
      font-size: 13px;
      padding: 4px 10px;
      border-radius: 9999px;
      margin-top: 4px;
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

  <div class="receipt-container">
    <% if $LogoCID %>
        <img src="$LogoCID" alt="Logo Perusahaan" class="logo" />
    <% end_if %>

    <h2>Tanda Terima Reservasi</h2>

    <p>Halo <strong>$CustomerName</strong>,</p>

    <p>
      Terima kasih telah melakukan reservasi di <strong>$CompanyName</strong>.
      Berikut adalah detail tanda terima untuk reservasi Anda.
    </p>

    <div class="receipt-details">
      <p>Nomor Reservasi: <span>$ReservationNumber</span></p>
      <p>Nama Reservasi: <span>$ReservationName</span></p>
      <p>Tanggal Reservasi: <span>$ReservationDate</span></p>
      <p>Waktu Mulai: <span>$StartTime</span></p>
      <p>Waktu Selesai: <span>$EndTime</span></p>
      <p>Jumlah Kursi: <span>$JumlahKursi</span></p>
      <p>Total Harga: <span>Rp $FormattedTotal</span></p>
      <p>Status: <span class="status">$Status</span></p>
      <% if $Catatan %>
      <p>Catatan: <span>$Catatan</span></p>
      <% end_if %>
      <% if $ResponsAdmin %>
      <p>Admin: <span>$ResponsAdmin</span></p>
      <% end_if %>
    </div>

    <p>
      Bukti tanda terima ini menunjukkan bahwa reservasi Anda telah diterima dan sedang diproses.
      Jika ada perubahan atau pertanyaan, silakan hubungi kami di 
      <a href="mailto:$CompanyEmail">$CompanyEmail</a>.
    </p>

    <p class="footer">
      Salam hangat,<br/>
      <span>Tim $CompanyName</span>
    </p>
  </div>

</body>
</html>
