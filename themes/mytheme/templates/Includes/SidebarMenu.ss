<div class="mb-3">
  <div class="list-group">
    <% loop KategoriProduk %>		
    <a href="$BaseHref?Kategori=$ID" class="list-group-item list-group-item-action d-flex align-items-center py-3 border-0 mb-2 text-decoration-none" style="background-color: #EB8317; border-radius: 8px;">
      <div class="me-3">
        <img src="$Image.URL" alt="$Nama" class="rounded" style="width: 45px; height: 45px; object-fit: cover;">
      </div>
      <div class="flex-grow-1">
        <h6 class="mb-0 text-white fw-semibold">$Nama</h6>
      </div>
      <div class="text-white">
        <i class="fas fa-chevron-right"></i>
      </div>
    </a>
    <% end_loop %>
  </div>
</div>