<main class="container py-4">
<h2 class="mb-4">Keranjang Anda</h2>

<!-- Flash Messages -->
<% if FlashMessages %>
<div class="alert alert-{$FlashMessages.Type} alert-dismissible fade show" role="alert">
    {$FlashMessages.Message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<% end_if %>

<div id="cart-items">
<% if CartItems %>
    <% loop CartItems %>
    <div class="row mb-3 align-items-center border-bottom pb-3">
        <div class="col-2">
            <img src="$Produk.Image.URL" class="img-fluid" alt="$Produk.Nama" style="max-width: 100px;">
        </div>
        <div class="col-3">
            <h6>$Produk.Nama</h6>
            <p class="mb-0">Rp {$Produk.Harga}</p>
        </div>
        <div class="col-3 d-flex align-items-center">
            <!-- Tombol kurangi -->
            <form method="post" action="$BaseHref/keranjang/update-cart" style="display: inline;">
                <input type="hidden" name="cart_item_id" value="$ID">
                <input type="hidden" name="action" value="decrease">
                <button type="submit" class="btn btn-outline-danger btn-sm">−</button>
            </form>
            
            <span class="mx-2">$Kuantitas</span>
            
            <!-- Tombol tambah -->
            <form method="post" action="$BaseHref/keranjang/update-cart" style="display: inline;">
                <input type="hidden" name="cart_item_id" value="$ID">
                <input type="hidden" name="action" value="increase">
                <button type="submit" class="btn btn-outline-success btn-sm">+</button>
            </form>
        </div>
        <div class="col-3 text-end">
            <strong>Subtotal: Rp {$Subtotal}</strong>
        </div>
        <div class="col-1 text-end">
            <!-- Tombol hapus -->
            <form method="post" action="$BaseHref/keranjang/remove-from-cart" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                <input type="hidden" name="cart_item_id" value="$ID">
                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
            </form>
        </div>
    </div>
    <% end_loop %>
<% else %>
    <div class="alert alert-info">
        <p>Keranjang Anda masih kosong.</p>
        <a href="$BaseHref" class="btn btn-primary">Mulai Belanja</a>
    </div>
<% end_if %>
</div>

<% if CartItems %>
<form method="post" action="$BaseHref/keranjang/process-checkout" id="checkoutForm">
    <!-- Nomor Meja -->
    <div class="mt-4">
        <label for="nomorMeja" class="form-label">Nomor Meja <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="nomorMeja" name="nomor_meja" placeholder="Masukkan nomor meja" required>
    </div>

    <!-- Metode Pembayaran -->
    <div class="mt-4">
        <label for="metodePembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
        <select class="form-select" id="metodePembayaran" name="payment_method" required onchange="calculateTotal()">
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
        
        <!-- Debug info - hapus setelah testing -->
        <% if PaymentMethods %>
        <small class="text-muted">Ditemukan {$PaymentMethods.Count} metode pembayaran</small>
        <% else %>
        <small class="text-danger">Tidak ada metode pembayaran yang tersedia</small>
        <% end_if %>
    </div>

    <!-- Ringkasan Pembayaran -->
    <div class="mt-4 border rounded p-3 bg-light">
        <h5>Ringkasan Pembayaran</h5>
        <div class="d-flex justify-content-between">
            <span>Total Belanja:</span>
            <span id="subtotal">Rp {$TotalHarga}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Biaya Admin:</span>
            <span id="paymentFee">Rp 0</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between fw-bold">
            <span>Total Pembayaran:</span>
            <span id="totalPayment" class="text-success">Rp {$TotalHarga}</span>
        </div>
    </div>

    <!-- Tombol Checkout -->
    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-primary btn-lg">Lanjutkan Pembayaran</button>
    </div>
</form>
<% end_if %>

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
</main>