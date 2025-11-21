<style>
.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.375rem;
}
.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}
.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}
</style>
<main class="min-h-screen bg-light p-6">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-primary text-center py-6">
                <h3 class="text-white font-bold text-2xl md:text-3xl">
                    <i class="fas fa-shopping-cart mr-3"></i>Keranjang Anda
                </h3>
            </div>
            
            <!-- Body -->
            <div class="p-6 md:p-8">
                <!-- Flash Messages -->
                <% if FlashMessages %>
                <div class="alert alert-{$FlashMessages.Type} rounded-lg shadow-sm mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>{$FlashMessages.Message}</span>
                        <button type="button" class="ml-auto text-lg hover:opacity-70" onclick="this.parentElement.parentElement.remove()">×</button>
                    </div>
                </div>
                <% end_if %>

                <div id="cart-items">
                <% if CartItems %>
                    <div class="space-y-4 mb-8">
                      <% loop CartItems %>
                      <div class="bg-light rounded-xl p-4 md:p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                          <!-- Product Image -->
                          <div class="w-20 h-20 md:w-24 md:h-24 flex-shrink-0">
                            <img src="$Produk.Image.URL" class="w-full h-full object-cover rounded-lg shadow-sm" alt="$Produk.Nama">
                          </div>
                          
                          <!-- Product Info -->
                          <div class="flex-1 min-w-0">
                            <h6 class="font-bold text-primary text-lg mb-2 truncate">$Produk.Nama</h6>
                            <p class="font-semibold text-secondary text-lg">Rp {$Produk.Harga}</p>
                          </div>
                          
                          <!-- Quantity Controls -->
                          <div class="flex items-center justify-center md:justify-start w-full md:w-auto">
                            <div class="flex items-center bg-white rounded-full border-2 border-accent p-2 shadow-sm">
                              <!-- Decrease Button -->
                              <form method="post" action="$BaseHref/keranjang/update-cart" class="inline">
                                  <input type="hidden" name="cart_item_id" value="$ID">
                                  <input type="hidden" name="action" value="decrease">
                                  <button type="submit" class="w-8 h-8 bg-accent text-primary rounded-full flex items-center justify-center hover:bg-accent/80 transition-colors duration-200">
                                    <i class="fas fa-minus text-sm"></i>
                                  </button>
                              </form>
                              
                              <span class="mx-4 font-bold text-primary text-lg min-w-[2rem] text-center">$Kuantitas</span>
                              
                              <!-- Increase Button -->
                              <form method="post" action="$BaseHref/keranjang/update-cart" class="inline">
                                  <input type="hidden" name="cart_item_id" value="$ID">
                                  <input type="hidden" name="action" value="increase">
                                  <button type="submit" class="w-8 h-8 bg-secondary text-white rounded-full flex items-center justify-center hover:bg-secondary/80 transition-colors duration-200">
                                    <i class="fas fa-plus text-sm"></i>
                                  </button>
                              </form>
                            </div>
                          </div>
                          
                          <!-- Subtotal & Remove -->
                          <div class="flex items-center justify-between md:flex-col md:items-end gap-4 w-full md:w-auto">
                            <div class="text-right">
                              <strong class="text-primary text-lg">Rp {$Subtotal}</strong>
                            </div>
                            
                            <form method="post" action="$BaseHref/keranjang/remove-from-cart" class="inline" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                                <input type="hidden" name="cart_item_id" value="$ID">
                                <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-colors duration-200">
                                  <i class="fas fa-trash text-lg"></i>
                                </button>
                            </form>
                          </div>
                        </div>
                      </div>
                      <% end_loop %>
                    </div>
                <% else %>
                    <div class="text-center py-12">
                      <i class="fas fa-shopping-cart text-6xl mb-4 text-accent"></i>
                      <h4 class="text-2xl font-bold text-primary mb-2">Keranjang Anda Kosong</h4>
                      <p class="text-gray-600 mb-6">Silakan tambahkan produk ke keranjang terlebih dahulu</p>
                      <a href="$BaseHref" class="inline-block bg-secondary text-white font-bold px-8 py-3 rounded-full hover:bg-secondary/90 transition-colors duration-200">
                        <i class="fas fa-shopping-bag mr-2"></i>Mulai Belanja
                      </a>
                    </div>
                <% end_if %>
                </div>

                <% if CartItems %>
                <form method="post" action="$BaseHref/keranjang/process-checkout" id="checkoutForm">
                    <hr class="my-8 border-gray-200">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                      <!-- Form Section -->
                      <div class="lg:col-span-2 space-y-6">
                        <!-- Table Number -->
                        <div>
                            <label for="nomorMeja" class="block font-bold text-primary mb-2">
                              <i class="fas fa-table mr-2"></i>Nomor Meja <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-accent focus:border-secondary focus:outline-none transition-colors duration-200" 
                                   id="nomorMeja" 
                                   name="nomor_meja" 
                                   placeholder="Masukkan nomor meja" 
                                   required>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label for="metodePembayaran" class="block font-bold text-primary mb-2">
                              <i class="fas fa-credit-card mr-2"></i>Metode Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select class="w-full px-4 py-3 rounded-xl border-2 border-accent focus:border-secondary focus:outline-none transition-colors duration-200" 
                                    id="metodePembayaran" 
                                    name="payment_method" 
                                    required 
                                    onchange="calculateTotal()">
                                <option value="">Pilih metode pembayaran</option>
                                <% if PaymentMethods %>
                                    <% loop PaymentMethods %>
                                    <option value="$paymentMethod" data-fee="$totalFee">
                                        $paymentName 
                                        <% if totalFee %>
                                            (Fee: Rp {$totalFee})
                                        <% end_if %>
                                    </option>
                                    <% end_loop %>
                                <% else %>
                                    <option value="" disabled>Metode pembayaran tidak tersedia</option>
                                <% end_if %>
                            </select>
                        </div>
                      </div>
                      
                      <!-- Summary Section -->
                      <div class="lg:col-span-1">
                        <div class="bg-primary rounded-2xl p-6 text-white sticky top-6">
                            <h5 class="font-bold mb-4 text-xl">
                              <i class="fas fa-calculator mr-2"></i>Ringkasan Pembayaran
                            </h5>
                            <div class="space-y-3 mb-4">
                                <div class="flex justify-between">
                                    <span>Total Belanja:</span>
                                    <span id="subtotal" class="font-bold">Rp {$TotalHarga}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Biaya Admin:</span>
                                    <span id="paymentFee" class="font-bold">Rp 0</span>
                                </div>
                            </div>
                            <hr class="border-secondary mb-4">
                            <div class="flex justify-between text-lg">
                                <span class="font-bold">Total Pembayaran:</span>
                                <span id="totalPayment" class="font-bold text-accent">Rp {$TotalHarga}</span>
                            </div>
                            
                            <!-- Checkout Button -->
                            <div class="mt-6">
                                <button type="submit" target="_blank" class="w-full bg-secondary text-white font-bold py-4 px-6 rounded-xl hover:bg-secondary/90 transition-all duration-200 hover:shadow-lg">
                                  <i class="fas fa-arrow-right mr-2"></i>Lanjutkan Pembayaran
                                </button>
                            </div>
                        </div>
                      </div>
                    </div>
                </form>
                <% end_if %>
            </div>
        </div>
    </div>
</main>

<script>
function calculateTotal() {
    const subtotal = {$TotalHarga};
    const paymentMethodSelect = document.getElementById('metodePembayaran');
    const selectedOption = paymentMethodSelect.options[paymentMethodSelect.selectedIndex];
    
    let paymentFee = 0;
    if (selectedOption && selectedOption.dataset.fee) {
        paymentFee = parseInt(selectedOption.dataset.fee);
    }
    
    const total = subtotal + paymentFee;
    
    document.getElementById('paymentFee').textContent = 'Rp ' + paymentFee.toLocaleString('id-ID');
    document.getElementById('totalPayment').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

// Format numbers on page load
document.addEventListener('DOMContentLoaded', function() {
    const subtotal = {$TotalHarga};
    document.getElementById('subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('totalPayment').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
});
</script>