<main class="container py-4">
<h2 class="mb-4">Keranjang Anda</h2>

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
        <a href="/" class="btn btn-primary">Mulai Belanja</a>
    </div>
<% end_if %>
</div>

<% if CartItems %>
<!-- Total Harga -->
<div class="text-end mt-4">
    <h5>Total Harga: <span class="text-success">Rp {$TotalHarga}</span></h5>
</div>

<!-- Nomor Meja -->
<div class="mt-4">
    <label for="nomorMeja" class="form-label">Nomor Meja</label>
    <input type="number" class="form-control" id="nomorMeja" placeholder="Masukkan nomor meja">
</div>

<!-- Metode Pembayaran -->
<div class="mt-4">
    <label for="metodePembayaran" class="form-label">Metode Pembayaran</label>
    <select class="form-select" id="metodePembayaran">
        <option selected>Pilih metode pembayaran</option>
        <option value="cash">Tunai</option>
        <option value="qris">QRIS</option>
        <option value="debit">Debit</option>
    </select>
</div>

<!-- Tombol Checkout -->
<div class="mt-4 text-end">
    <button class="btn btn-primary">Lanjutkan Pembayaran</button>
</div>
<% end_if %>
</main>