<div class="row">
  <% loop Produk %>
  <div class="col-md-3 mb-4">
    <div class="card h-100">
      <img src="$Image.URL" class="card-img-top" alt="Produk">
      <div class="card-body d-flex flex-column justify-content-between align-items-center">
        <h5 class="card-title">$Nama</h5>
        <p class="card-text">$Harga</p>

        <!-- Tombol + dan - -->
        <div class="d-flex align-items-center">
          <button class="btn btn-outline-danger btn-sm me-2">−</button>
          <span class="mx-2">0</span>
          <button class="btn btn-outline-success btn-sm ms-2">+</button>
        </div>
      </div>
    </div>
  </div>
  <% end_loop %>
</div>
