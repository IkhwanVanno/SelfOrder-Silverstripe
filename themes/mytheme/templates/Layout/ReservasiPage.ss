<section class="p-6 min-h-screen">
  <div class="w-full bg-white p-5 rounded-md">
    <!-- Header dan Tombol -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-3">
      <h1 class="text-2xl font-semibold text-gray-700">Daftar Reservasi</h1>
      <button id="openModalBtn"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition">
        <i class="fas fa-plus mr-2"></i>Buat Reservasi
      </button>
    </div>

    <!-- Tabel Reservasi -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full text-sm text-left text-gray-700">
        <thead class="bg-gray-100 text-gray-800 uppercase text-xs font-semibold">
          <tr>
            <th class="px-4 py-3">Nama Reservasi</th>
            <th class="px-4 py-3">Jumlah Kursi</th>
            <th class="px-4 py-3">Total Harga</th>
            <th class="px-4 py-3">Waktu Mulai</th>
            <th class="px-4 py-3">Waktu Selesai</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Catatan</th>
            <th class="px-4 py-3">Respons Admin</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <!-- Contoh Data -->
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">Reservasi Meja 5</td>
            <td class="px-4 py-3">4</td>
            <td class="px-4 py-3">Rp 250.000</td>
            <td class="px-4 py-3">2025-10-23 18:00</td>
            <td class="px-4 py-3">2025-10-23 20:00</td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                MenungguPersetujuan
              </span>
            </td>
            <td class="px-4 py-3">Ulang tahun keluarga</td>
            <td class="px-4 py-3">-</td>

            <td class="px-4 py-3 text-center space-y-2">
              <% if Reservasi.Status == 'MenungguPersetujuan' %>
              <a href="#"
                class="block text-center bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded text-xs font-medium transition">
                <i class="fas fa-times mr-1"></i>Batalkan
              </a>

              <% else_if Reservasi.Status == 'Disetujui' %>
              <button id="openPaymentModal"
                class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded text-xs font-medium transition">
                <i class="fas fa-credit-card mr-1"></i>Bayar Sekarang
              </button>

              <% else_if Reservasi.Status == 'MenungguPembayaran' %>
              <a href="#"
                class="block text-center bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded text-xs font-medium transition">
                <i class="fas fa-wallet mr-1"></i>Lanjutkan Pembayaran
              </a>

              <% else_if Reservasi.Status == 'Selesai' %>
              <a href="#"
                class="block text-center bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded text-xs font-medium transition">
                <i class="fas fa-download mr-1"></i>Unduh Tanda Terima
              </a>
              <a href="#"
                class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded text-xs font-medium transition">
                <i class="fas fa-envelope mr-1"></i>Kirim Tanda Terima
              </a>

              <% else_if Reservasi.Status == 'Ditolak' || 'Dibatalkan' %>
              <button id="openModalBtn"
                class="block w-full text-center bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded text-xs font-medium transition">
                <i class="fas fa-rotate-right mr-1"></i>Silahkan Daftar Ulang
              </button>

              <% end_if %>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Buat Reservasi -->
  <div id="reservasiModal"
    class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-lg p-6 rounded-2xl shadow-lg relative">
      <h2 class="text-xl font-semibold text-gray-700 mb-4">Buat Reservasi</h2>

      <form class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Reservasi</label>
          <input type="text" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Masukkan nama reservasi" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Kursi</label>
          <input type="number" min="1"
            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Jumlah kursi" />
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
            <input type="datetime-local"
              class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
            <input type="datetime-local"
              class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
          <textarea rows="3"
            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Tambahkan catatan..."></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-3">
          <button type="button" id="closeModalBtn"
            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
            Batal
          </button>
          <button type="submit"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
            Simpan
          </button>
        </div>

        <button id="closeIcon"
          class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
      </form>
    </div>
  </div>

  <!-- Modal Pembayaran -->
  <div id="paymentModal"
    class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-md p-6 rounded-2xl shadow-lg relative">
      <h2 class="text-xl font-semibold text-gray-700 mb-4">Pembayaran Reservasi</h2>

      <div class="space-y-3 mb-5">
        <div class="flex justify-between text-sm text-gray-600">
          <span>Total Harga:</span>
          <span class="font-semibold text-gray-800">Rp 250.000</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600">
          <span>Payment Fee:</span>
          <span class="font-semibold text-gray-800">Rp 2.500</span>
        </div>
        <div class="flex justify-between border-t pt-2 font-semibold text-gray-800">
          <span>Total Pembayaran:</span>
          <span>Rp 252.500</span>
        </div>
      </div>

      <div class="mb-5">
        <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
        <select class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
          <option>Pilih Metode</option>
          <option>Duitku</option>
          <option>Transfer Bank</option>
          <option>QRIS</option>
        </select>
      </div>

      <div class="flex justify-end gap-2">
        <button id="closePaymentBtn"
          class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
          Batal
        </button>
        <button
          class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition">
          <i class="fas fa-money-bill-wave mr-1"></i>Bayar Sekarang
        </button>
      </div>

      <button id="closePaymentIcon"
        class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
    </div>
  </div>

  <!-- Script Modal -->
  <script>
    // Reservasi modal
    const reservasiModal = document.getElementById('reservasiModal');
    const openBtns = document.querySelectorAll('#openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    const closeIcon = document.getElementById('closeIcon');

    openBtns.forEach(btn => {
      btn.addEventListener('click', () => reservasiModal.classList.remove('hidden'));
    });
    closeBtn.addEventListener('click', () => reservasiModal.classList.add('hidden'));
    closeIcon.addEventListener('click', () => reservasiModal.classList.add('hidden'));
    window.addEventListener('click', e => {
      if (e.target === reservasiModal) reservasiModal.classList.add('hidden');
    });

    // Payment modal
    const paymentModal = document.getElementById('paymentModal');
    const openPaymentBtn = document.getElementById('openPaymentModal');
    const closePaymentBtn = document.getElementById('closePaymentBtn');
    const closePaymentIcon = document.getElementById('closePaymentIcon');

    openPaymentBtn?.addEventListener('click', () => paymentModal.classList.remove('hidden'));
    closePaymentBtn.addEventListener('click', () => paymentModal.classList.add('hidden'));
    closePaymentIcon.addEventListener('click', () => paymentModal.classList.add('hidden'));
    window.addEventListener('click', e => {
      if (e.target === paymentModal) paymentModal.classList.add('hidden');
    });
  </script>
</section>
