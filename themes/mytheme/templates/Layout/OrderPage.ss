<main class="min-h-screen bg-light">
  <!-- Body -->
  <div class="p-6">
      <% if $OrderList %>
      <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-200 divide-y divide-gray-200 rounded-lg overflow-hidden shadow-sm">
              <thead class="bg-primary text-white">
                  <tr>
                      <th class="px-4 py-3 text-left text-sm font-medium">Order ID</th>
                      <th class="px-4 py-3 text-left text-sm font-medium">No. Invoice</th>
                      <th class="px-4 py-3 text-left text-sm font-medium">No. Meja</th>
                      <th class="px-4 py-3 text-left text-sm font-medium">Metode Pembayaran</th>
                      <th class="px-4 py-3 text-left text-sm font-medium">Total Harga</th>
                      <th class="px-4 py-3 text-left text-sm font-medium">Status Pesanan</th>
                      <th class="px-4 py-3 text-left text-sm font-medium">Aksi</th>
                  </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm bg-white">
              <% loop $OrderList %>
              <tr class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="px-4 py-3 text-gray-800 font-medium">#$ID</td>
                  <td class="px-4 py-3 text-gray-800 font-mono">{$NomorInvoice}</td>
                  <td class="px-4 py-3 text-gray-800 font-medium">{$NomorMeja}</td>
                  <td class="px-4 py-3 text-gray-800">{$Payment.MetodePembayaran}</td>
                  <td class="px-4 py-3 text-gray-800 font-semibold">Rp {$TotalHarga}</td>
                  <td class="px-4 py-3">
                      <% if $Status == 'MenungguPembayaran' %>
                          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                              <i class="fas fa-hourglass-half fa-spin mr-1"></i>Menunggu Pembayaran
                          </span>
                      <% else_if $Status == 'Antrean' %>
                          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                              <i class="fas fa-hourglass-half fa-spin mr-1"></i>Antrean
                          </span>
                      <% else_if $Status == 'Proses' %>
                          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                              <i class="fas fa-cog fa-spin mr-1"></i>Proses
                          </span>
                      <% else_if $Status == 'Terkirim' %>
                          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                              <i class="fas fa-check-double mr-1"></i>Terkirim
                          </span>
                      <% else_if $Status == 'Dibatalkan' %>
                          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                              <i class="fas fa-exclamation-triangle mr-1"></i>Dibatalkan
                          </span>
                      <% end_if %>
                  </td>
                  <td class="px-4 py-3 space-y-2">
                      <!-- Tombol Aksi sesuai status pembayaran -->
                      <% if $Payment.Status == 'Pending' %>
                        <% if $Payment.ExpiryTime %>
                          <% if $Payment.IsExpired %>
                            <span class="text-red-500 text-sm font-medium">
                              <i class="fas fa-exclamation-triangle mr-1"></i>Pembayaran Kedaluwarsa
                            </span>
                          <% else %>
                            <% if $Payment.PaymentUrl %>
                              <a href="$Payment.PaymentUrl" target="_blank"
                                class="block text-center bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded text-xs font-medium transition-colors duration-200">
                                <i class="fa-solid fa-link mr-1"></i>Bayar Sekarang
                              </a>
                              <p class="text-xs text-gray-600 mt-1">
                                Expired: {$Payment.ExpiryTime}
                              </p>
                            <% else %>
                              <span class="text-yellow-600 text-sm font-medium">
                                <i class="fas fa-clock mr-1"></i>Menunggu Payment URL
                              </span>
                            <% end_if %>
                          <% end_if %>
                        <% else %>
                          <span class="text-yellow-600 text-sm font-medium">
                            <i class="fas fa-clock mr-1"></i>Memproses Pembayaran
                          </span>
                        <% end_if %>
                      <% else_if $Payment.Status == 'Completed' %>
                        <a href="$BaseHref/keranjang/downloadInvoice/$ID"
                          class="block text-center bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded text-xs font-medium transition-colors duration-200">
                          <i class="fas fa-download mr-1"></i>Unduh Kuitansi
                        </a>
                        <a href="$BaseHref/keranjang/sendInvoice/$ID"
                          class="block text-center bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded text-xs font-medium transition-colors duration-200">
                          <i class="fas fa-envelope mr-1"></i>Kirim Kuitansi
                        </a>
                      <% else_if $Payment.Status == 'Failed' %>
                        <span class="text-red-500 text-sm font-medium">
                          <i class="fas fa-exclamation-triangle mr-1"></i>Pembayaran Gagal/Kedaluwarsa
                        </span>
                      <% end_if %>
                      <!-- Tombol Detail -->
                      <span onclick="openModal('modal-$ID')" 
                        class="block text-center bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded text-xs font-medium transition-colors duration-200">
                        <i class="fas fa-info-circle mr-1"></i>Lihat Detail
                      </span>
                      <div id="modal-$ID" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                        <div class="bg-white w-full max-w-xl rounded-lg shadow-lg p-6 relative overflow-y-auto max-h-[90vh]">
                          <button onclick="closeModal('modal-$ID')" 
                            class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl">
                            &times;
                          </button>

                          <h2 class="text-lg font-bold mb-4">Detail Pesanan</h2>
                          <!-- Informasi -->
                          <div class="text-sm space-y-2 mb-4">
                            <p><strong>Invoice:</strong> $NomorInvoice</p>
                            <p><strong>No. Meja:</strong> $NomorMeja</p>
                            <p><strong>Status Pesanan:</strong> $Status</p>
                            <p><strong>Item Total:</strong> $TotalHargaBarang</p>
                            <p><strong>Payment Fee:</strong> $PaymentFee</p>
                            <p><strong>Total Amount:</strong> $TotalHarga</p>
                            <p><strong>Date:</strong> $Created.Format('d-m-Y H:i')</p>
                          </div>

                          <!-- List Pesanan -->
                          <div class="mb-4">
                            <p class="font-semibold mb-1">List Pesanan:</p>
                            <ul class="list-disc list-inside text-sm">
                              <% loop $OrderItems %>
                                <li>$Produk.Nama $Kuantitas x</li>
                              <% end_loop %>
                            </ul>
                          </div>

                          <!-- Info Customer -->
                          <div class="text-sm mb-4">
                            <p><strong>Nama:</strong> $Member.FirstName $Member.Surname</p>
                            <p><strong>Email:</strong> $Member.Email</p>
                          </div>

                          <!-- Info Pembayaran -->
                          <div class="text-sm">
                            <p><strong>Metode Pembayaran:</strong> $Payment.MetodePembayaran</p>
                            <p><strong>Status Pembayaran:</strong> $Payment.Status</p>
                            <p><strong>Reference:</strong> $Payment.Reference</p>
                          </div>
                        </div>
                      </div>

                    </td>
                  </tr>
                  <% end_loop %>
              </tbody>
          </table>
      </div>
      <% else %>
      <div class="text-center py-12">
          <i class="fas fa-clipboard-list text-6xl mb-4 text-accent"></i>
          <h4 class="text-2xl font-bold text-primary mb-2">Belum Ada Order</h4>
          <p class="text-gray-600 mb-6">Anda belum memiliki order apapun</p>
          <a href="$BaseHref" class="inline-block bg-secondary text-white font-bold px-8 py-3 rounded-full hover:bg-secondary/90 transition-colors duration-200">
              <i class="fas fa-shopping-bag mr-2"></i>Mulai Belanja
          </a>
      </div>
      <% end_if %>
  </div>
</main>
<script>
  function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
  }

  function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
  }
</script>