<div class="row">
  <% loop Produk %>
  <div class="col-md-3 mb-4">
    <div class="card h-100">
      <img src="$Image.URL" class="card-img-top" alt="Produk">
      <div class="card-body d-flex flex-column justify-content-between align-items-center">
        <h5 class="card-title">$Nama</h5>
        <p class="card-text">Rp {$Harga}</p>

        <!-- Tombol + dan - -->
        <div class="d-flex align-items-center">
          <% if $Top.getCartQuantity($ID) %>
            <!-- Tombol kurangi -->
            <form method="post" action="$BaseHref/keranjang/update-cart" style="display: inline;">
              <input type="hidden" name="cart_item_id" value="<% loop $Top.CartItem.filter('ProdukID', $ID).filter('MemberID', $Top.getCurrentUser.ID) %>$ID<% end_loop %>">
              <input type="hidden" name="action" value="decrease">
              <button type="submit" class="btn btn-outline-danger btn-sm me-2">−</button>
            </form>
            
            <span class="mx-2">$Top.getCartQuantity($ID)</span>
            
            <!-- Tombol tambah -->
            <form method="post" action="$BaseHref/keranjang/update-cart" style="display: inline;">
              <input type="hidden" name="cart_item_id" value="<% loop $Top.CartItem.filter('ProdukID', $ID).filter('MemberID', $Top.getCurrentUser.ID) %>$ID<% end_loop %>">
              <input type="hidden" name="action" value="increase">
              <button type="submit" class="btn btn-outline-success btn-sm ms-2">+</button>
            </form>
          <% else %>
            <!-- Jika belum ada di cart, tampilkan tombol add -->
            <button class="btn btn-outline-danger btn-sm me-2" disabled>−</button>
            <span class="mx-2">0</span>
            <form method="post" action="$BaseHref/keranjang/add-to-cart" style="display: inline;">
              <input type="hidden" name="produk_id" value="$ID">
              <input type="hidden" name="kuantitas" value="1">
              <button type="submit" class="btn btn-outline-success btn-sm ms-2">+</button>
            </form>
          <% end_if %>
        </div>
      </div>
    </div>
  </div>
  <% end_loop %>
</div>