<main class="min-h-screen bg-white p-6">
  <div>
    <!-- Flash Message -->
    <% if $FlashMessage %>
    <div class="mb-4 p-4 rounded-lg 
      <% if $FlashMessage.Type == 'success' %>bg-green-100 text-green-800 border border-green-200
      <% else_if $FlashMessage.Type == 'danger' %>bg-red-100 text-red-800 border border-red-200
      <% else_if $FlashMessage.Type == 'warning' %>bg-yellow-100 text-yellow-800 border border-yellow-200
      <% else %>bg-blue-100 text-blue-800 border border-blue-200<% end_if %>">
      <i class="fas fa-<% if $FlashMessage.Type == 'success' %>check-circle<% else_if $FlashMessage.Type == 'danger' %>exclamation-circle<% else_if $FlashMessage.Type == 'warning' %>exclamation-triangle<% else %>info-circle<% end_if %> mr-2"></i>
      {$FlashMessage.Message}
    </div>
    <% end_if %>

    <div>
      <!-- Header dan Tombol -->
      <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-3">
        <h1 class="text-2xl font-semibold text-gray-700">Daftar Reservasi</h1>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition">
          <i class="fas fa-plus mr-2"></i>Buat Reservasi
        </button>
      </div>

      <!-- Info Biaya -->
      <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-800">
          <i class="fas fa-info-circle mr-2"></i>
          Biaya reservasi: <strong>Rp $SiteConfig.BiayaReservasi/jam</strong>
        </p>
      </div>

      <!-- Tabel Reservasi -->
      <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-primary text-white uppercase text-xs font-semibold">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">Nama Reservasi</th>
              <th class="px-4 py-3">Kursi</th>
              <th class="px-4 py-3">Total Harga</th>
              <th class="px-4 py-3">Waktu Mulai</th>
              <th class="px-4 py-3">Waktu Selesai</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <% if $UserReservations %>
              <% loop $UserReservations %>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">#{$ID}</td>
                <td class="px-4 py-3">$NamaReservasi</td>
                <td class="px-4 py-3">$JumlahKursi</td>
                <td class="px-4 py-3 font-semibold">$FormattedTotal</td>
                <td class="px-4 py-3">$FormattedWaktuMulai</td>
                <td class="px-4 py-3">$FormattedWaktuSelesai</td>
                <td class="px-4 py-3">
                  <% if $Status == 'MenungguPersetujuan' %>
                    <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                      <i class="fas fa-clock mr-1"></i>Menunggu Persetujuan
                    </span>
                  <% else_if $Status == 'Disetujui' %>
                    <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                      <i class="fas fa-check mr-1"></i>Disetujui
                    </span>
                  <% else_if $Status == 'Ditolak' %>
                    <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                      <i class="fas fa-times mr-1"></i>Ditolak
                    </span>
                  <% else_if $Status == 'MenungguPembayaran' %>
                    <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                      <i class="fas fa-credit-card mr-1"></i>Menunggu Pembayaran
                    </span>
                  <% else_if $Status == 'Selesai' %>
                    <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                      <i class="fas fa-check-double mr-1"></i>Selesai
                    </span>
                  <% else_if $Status == 'Dibatalkan' %>
                    <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">
                      <i class="fas fa-ban mr-1"></i>Dibatalkan
                    </span>
                  <% end_if %>
                </td>
                <td class="px-4 py-3">
                  <div class="space-y-2">
                    <!-- Tombol Detail -->
                    <button onclick="openDetail{$ID}()" 
                      class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded text-xs font-medium transition">
                      <i class="fas fa-info-circle mr-1"></i>Detail
                    </button>

                    <!-- Aksi berdasarkan status -->
                    <% if $Status == 'MenungguPersetujuan' %>
                      <a href="$BaseHref/reservasi/cancel/$ID"
                        onclick="return confirm('Yakin ingin membatalkan reservasi ini?')"
                        class="block w-full text-center bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded text-xs font-medium transition">
                        <i class="fas fa-times mr-1"></i>Batalkan
                      </a>
                    <% end_if %>

                    <% if $Status == 'Disetujui' %>
                      <button onclick="openPayment{$ID}()"
                        class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded text-xs font-medium transition">
                        <i class="fas fa-credit-card mr-1"></i>Bayar Sekarang
                      </button>
                      <a href="$BaseHref/reservasi/cancel/$ID"
                        onclick="return confirm('Yakin ingin membatalkan reservasi ini?')"
                        class="block w-full text-center bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded text-xs font-medium transition">
                        <i class="fas fa-times mr-1"></i>Batalkan
                      </a>
                    <% end_if %>

                    <% if $Status == 'MenungguPembayaran' %>
                      <% if $PaymentReservasi.PaymentUrl %>
                        <a href="$PaymentReservasi.PaymentUrl" target="_blank"
                          class="block w-full text-center bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded text-xs font-medium transition">
                          <i class="fas fa-wallet mr-1"></i>Lanjutkan Pembayaran
                        </a>
                        <% if $PaymentReservasi.ExpiryTime %>
                          <p class="text-xs text-gray-600 text-center">
                            Expired: $PaymentReservasi.FormattedExpiryTime
                          </p>
                        <% end_if %>
                      <% end_if %>
                      <a href="$BaseHref/reservasi/cancel/$ID"
                        onclick="return confirm('Yakin ingin membatalkan reservasi ini?')"
                        class="block w-full text-center bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded text-xs font-medium transition">
                        <i class="fas fa-times mr-1"></i>Batalkan
                      </a>
                    <% end_if %>

                    <% if $Status == 'Selesai' %>
                      <a href="$BaseHref/reservasi/download/$ID"
                        class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded text-xs font-medium transition">
                        <i class="fas fa-download mr-1"></i>Unduh Tanda Terima
                      </a>
                      <a href="$BaseHref/reservasi/send-email/$ID"
                        class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded text-xs font-medium transition">
                        <i class="fas fa-envelope mr-1"></i>Kirim Email
                      </a>
                    <% end_if %>
                  </div>

                  <!-- Modal Detail -->
                  <div id="detail{$ID}" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                    <div class="bg-white w-full max-w-xl rounded-lg shadow-lg p-6 relative overflow-y-auto max-h-[90vh]">
                      <button onclick="closeDetail{$ID}()" 
                        class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl">
                        &times;
                      </button>

                      <h2 class="text-lg font-bold mb-4">Detail Reservasi #{$ID}</h2>
                      
                      <div class="text-sm space-y-2">
                        <p><strong>Nama Reservasi:</strong> $NamaReservasi</p>
                        <p><strong>Jumlah Kursi:</strong> $JumlahKursi</p>
                        <p><strong>Total Harga:</strong> $FormattedTotal</p>
                        <p><strong>Waktu Mulai:</strong> $FormattedWaktuMulai</p>
                        <p><strong>Waktu Selesai:</strong> $FormattedWaktuSelesai</p>
                        <p><strong>Status:</strong> $StatusLabel</p>
                        <% if $Catatan %>
                          <p><strong>Catatan:</strong> $Catatan</p>
                        <% end_if %>
                        <% if $ResponsAdmin %>
                          <p><strong>Respons Admin:</strong> $ResponsAdmin</p>
                        <% end_if %>
                        <% if $PaymentReservasi %>
                          <hr class="my-3">
                          <p><strong>Metode Pembayaran:</strong> $PaymentReservasi.MetodePembayaran</p>
                          <p><strong>Status Pembayaran:</strong> $PaymentReservasi.StatusLabel</p>
                          <% if $PaymentReservasi.DuitkuTransactionID %>
                            <p><strong>ID Transaksi:</strong> $PaymentReservasi.DuitkuTransactionID</p>
                          <% end_if %>
                        <% end_if %>
                      </div>
                    </div>
                  </div>

                  <!-- Modal Payment -->
                  <% if $Status == 'Disetujui' %>
                  <div id="payment{$ID}" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                    <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6 relative">
                      <button onclick="closePayment{$ID}()" 
                        class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl">
                        &times;
                      </button>

                      <h2 class="text-lg font-bold mb-4">Pembayaran Reservasi</h2>
                      
                      <div class="mb-4 p-3 bg-gray-50 rounded-lg space-y-2">
                        <div class="flex justify-between text-sm">
                          <span>Total Harga:</span>
                          <span class="font-semibold">$FormattedTotal</span>
                        </div>
                        <p class="text-xs text-gray-600">
                          <i class="fas fa-info-circle mr-1"></i>Biaya payment akan ditambahkan sesuai metode yang dipilih
                        </p>
                      </div>

                      <form action="$BaseHref/reservasi/payment/$ID" method="POST">
                        <div class="mb-4">
                          <label class="block text-sm font-medium text-gray-700 mb-2">
                            Metode Pembayaran <span class="text-red-500">*</span>
                          </label>
                          <select name="payment_method" required
                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            <% loop $Top.PaymentMethods %>
                              <option value="$paymentMethod">
                                $paymentName (Fee: Rp {$totalFee})
                              </option>
                            <% end_loop %>
                          </select>
                        </div>

                        <div class="flex justify-end gap-2">
                          <button type="button" onclick="closePayment{$ID}()"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                            Batal
                          </button>
                          <button type="submit"
                            class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition">
                            <i class="fas fa-money-bill-wave mr-1"></i>Bayar Sekarang
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>

                  <script>
                    function openPayment{$ID}() {
                      document.getElementById('payment{$ID}').classList.remove('hidden');
                    }
                    function closePayment{$ID}() {
                      document.getElementById('payment{$ID}').classList.add('hidden');
                    }
                    function openDetail{$ID}() {
                      document.getElementById('detail{$ID}').classList.remove('hidden');
                    }
                    function closeDetail{$ID}() {
                      document.getElementById('detail{$ID}').classList.add('hidden');
                    }
                  </script>
                  <% else %>
                  <script>
                    function openDetail{$ID}() {
                      document.getElementById('detail{$ID}').classList.remove('hidden');
                    }
                    function closeDetail{$ID}() {
                      document.getElementById('detail{$ID}').classList.add('hidden');
                    }
                  </script>
                  <% end_if %>
                </td>
              </tr>
              <% end_loop %>
            <% else %>
              <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                  <i class="fas fa-calendar-times text-4xl mb-2 text-gray-400"></i>
                  <p>Belum ada reservasi. Klik tombol "Buat Reservasi" untuk membuat reservasi baru.</p>
                </td>
              </tr>
            <% end_if %>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal Buat Reservasi -->
  <div id="createModal"
    class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-lg p-6 rounded-2xl shadow-lg relative overflow-y-auto max-h-[90vh]">
      <button onclick="document.getElementById('createModal').classList.add('hidden')"
        class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl">
        &times;
      </button>

      <h2 class="text-xl font-semibold text-gray-700 mb-4">Buat Reservasi Baru</h2>

      <form action="$BaseHref/reservasi/create" method="POST" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Nama Reservasi <span class="text-red-500">*</span>
          </label>
          <input type="text" name="nama_reservasi" required
            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Contoh: Reservasi Ulang Tahun" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Jumlah Kursi <span class="text-red-500">*</span>
          </label>
          <input type="number" name="jumlah_kursi" min="1" required
            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Jumlah kursi yang dipesan" />
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Waktu Mulai <span class="text-red-500">*</span>
            </label>
            <input type="datetime-local" name="waktu_mulai" id="waktuMulai" required
              class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Waktu Selesai <span class="text-red-500">*</span>
            </label>
            <input type="datetime-local" name="waktu_selesai" id="waktuSelesai" required
              class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
          </div>
        </div>

        <div id="estimasiHarga" class="p-3 bg-blue-50 rounded-lg hidden">
          <p class="text-sm text-blue-800">
            <i class="fas fa-calculator mr-1"></i>
            Estimasi Total: <span id="totalEstimasi" class="font-bold">-</span>
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
          <textarea name="catatan" rows="3"
            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Tambahkan catatan khusus (opsional)"></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-3">
          <button type="button" 
            onclick="document.getElementById('createModal').classList.add('hidden')"
            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
            Batal
          </button>
          <button type="submit"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
            <i class="fas fa-save mr-1"></i>Simpan Reservasi
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Set minimum datetime to now
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const minDateTime = now.toISOString().slice(0, 16);
    document.getElementById('waktuMulai').min = minDateTime;
    document.getElementById('waktuSelesai').min = minDateTime;

    // Calculate estimation
    const biayaPerJam = parseFloat('$SiteConfig.BiayaReservasi') || 0;

    function calculateEstimation() {
      const waktuMulai = document.getElementById('waktuMulai').value;
      const waktuSelesai = document.getElementById('waktuSelesai').value;

      if (waktuMulai && waktuSelesai) {
        const start = new Date(waktuMulai);
        const end = new Date(waktuSelesai);
        const diff = (end - start) / (1000 * 60 * 60); // in hours

        if (diff > 0) {
          const totalJam = Math.ceil(diff);
          const total = totalJam * biayaPerJam;
          document.getElementById('totalEstimasi').textContent = 
            'Rp ' + total.toLocaleString('id-ID') + ' (' + totalJam + ' jam)';
          document.getElementById('estimasiHarga').classList.remove('hidden');
        } else {
          document.getElementById('estimasiHarga').classList.add('hidden');
        }
      }
    }

    document.getElementById('waktuMulai').addEventListener('change', calculateEstimation);
    document.getElementById('waktuSelesai').addEventListener('change', calculateEstimation);

    // Close modal when clicking outside
    document.getElementById('createModal').addEventListener('click', function(e) {
      if (e.target === this) {
        this.classList.add('hidden');
      }
    });
  </script>
</main>