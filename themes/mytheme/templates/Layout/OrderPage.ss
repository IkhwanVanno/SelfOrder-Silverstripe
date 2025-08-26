<div>
  <h1 class="text-2xl font-bold text-gray-800 mb-6">Daftar Order</h1>

  <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-200 divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">No. Meja</th>
          <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">No. Invoice</th>
          <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Total Harga</th>
          <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Status</th>
          <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Tanggal Order</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 text-sm">
        <!-- Contoh baris order -->
        <% loop OrderList %>
        <tr>
          <td class="px-4 py-3 text-gray-800 font-medium">$NomorMeja</td>
          <td class="px-4 py-3 text-gray-800">$NomorInvoice</td>
          <td class="px-4 py-3 text-gray-800">RP $TotalHarga</td>
          <td class="px-4 py-3">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
              $Status
            </span>
          </td>
          <td class="px-4 py-3 text-gray-700">$Created</td>
        <% end_loop %>
        </tr>
        <!-- Tambahkan baris lainnya sesuai data -->
      </tbody>
    </table>
  </div>
</div>
