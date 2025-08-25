<main class="container py-4">
<h2 class="mb-4">Keranjang Anda</h2>

<!-- Daftar Produk -->
<div class="row mb-3 align-items-center border-bottom pb-3">
    <div class="col-3">
    <img src="https://via.placeholder.com/100" class="img-fluid" alt="Produk">
    </div>
    <div class="col-3">
    <h6>Nama Produk</h6>
    <p class="mb-0">Rp 25.000</p>
    </div>
    <div class="col-3 d-flex align-items-center">
    <button class="btn btn-outline-danger btn-sm">−</button>
    <span class="mx-2">1</span>
    <button class="btn btn-outline-success btn-sm">+</button>
    </div>
    <div class="col-3 text-end">
    <strong>Subtotal: Rp 25.000</strong>
    </div>
</div>

<!-- Tambahkan produk lainnya jika perlu -->
<!-- Copy block di atas dan ubah datanya -->

<!-- Total Harga -->
<div class="text-end mt-4">
    <h5>Total Harga: <span class="text-success">Rp 25.000</span></h5>
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
</main>