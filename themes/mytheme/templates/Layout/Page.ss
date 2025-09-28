<div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 md:gap-6">
    <% loop Produk %>
    <div class="bg-light rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden group flex flex-col">
        <!-- Product Image -->
        <div class="relative overflow-hidden">
            <img src="$Image.URL" 
                class="w-full h-40 sm:h-48 md:h-52 object-cover group-hover:scale-105 transition-transform duration-300" 
                alt="$Nama">
            <div class="absolute top-2 left-2">
                <% if $Status == 'Aktif' %>
                    <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">Tersedia</span>
                <% else %>
                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">Habis</span>
                <% end_if %>
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="p-3 md:p-4 flex flex-col flex-grow">
            <div class="flex-grow text-center">
                <h6 class="font-bold text-primary text-sm md:text-base mb-2 line-clamp-2 leading-tight">$Nama</h6>
                <p class="font-bold text-secondary text-base md:text-lg mb-4">Rp {$Harga}</p>
            </div>

           <!-- Quantity Controls -->
            <div class="flex items-center justify-center bg-white rounded-full border-2 border-accent p-2 shadow-sm mt-auto">
                <% if $Status == 'Aktif' %>
                    <% if $Top.getCartQuantity($ID) %>
                        <!-- Decrease Button -->
                        <form method="post" action="$BaseHref/keranjang/update-cart" class="inline">
                            <input type="hidden" name="cart_item_id" value="<% loop $Top.CartItem.filter('ProdukID', $ID).filter('MemberID', $Top.getCurrentUser.ID) %>$ID<% end_loop %>">
                            <input type="hidden" name="action" value="decrease">
                            <button type="submit" class="w-8 h-8 bg-accent text-primary rounded-full flex items-center justify-center hover:bg-accent/80 transition-all duration-200 hover:scale-110">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                        </form>

                        <span class="mx-3 font-bold text-primary text-center min-w-[1.5rem]">$Top.getCartQuantity($ID)</span>

                        <!-- Increase Button -->
                        <form method="post" action="$BaseHref/keranjang/update-cart" class="inline">
                            <input type="hidden" name="cart_item_id" value="<% loop $Top.CartItem.filter('ProdukID', $ID).filter('MemberID', $Top.getCurrentUser.ID) %>$ID<% end_loop %>">
                            <input type="hidden" name="action" value="increase">
                            <button type="submit" class="w-8 h-8 bg-secondary text-white rounded-full flex items-center justify-center hover:bg-secondary/80 transition-all duration-200 hover:scale-110">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </form>
                    <% else %>
                        <!-- Add to Cart -->
                        <button class="w-8 h-8 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center cursor-not-allowed" disabled>
                            <i class="fas fa-minus text-xs"></i>
                        </button>

                        <span class="mx-3 font-bold text-primary text-center min-w-[1.5rem]">0</span>

                        <form method="post" action="$BaseHref/keranjang/add-to-cart" class="inline">
                            <input type="hidden" name="produk_id" value="$ID">
                            <input type="hidden" name="kuantitas" value="1">
                            <button type="submit" class="w-8 h-8 bg-secondary text-white rounded-full flex items-center justify-center hover:bg-secondary/80 transition-all duration-200 hover:scale-110 hover:shadow-md">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </form>
                    <% end_if %>
                <% else %>
                    <!-- Produk Nonaktif: semua tombol disabled -->
                    <button class="w-8 h-8 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center cursor-not-allowed" disabled>
                        <i class="fas fa-minus text-xs"></i>
                    </button>

                    <span class="mx-3 font-bold text-gray-400 text-center min-w-[1.5rem]">0</span>

                    <button class="w-8 h-8 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center cursor-not-allowed" disabled>
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                <% end_if %>
            </div>
        </div>
    </div>
    <% end_loop %>
</div>
<!-- Empty State -->
<% if not Produk %>
<div class="flex items-center justify-center min-h-[50vh]">
  <div class="text-center max-w-md mx-auto bg-light rounded-2xl p-8 shadow-lg border border-accent/20">
  <i class="fas fa-box-open text-6xl text-secondary mb-4"></i>
  <h5 class="text-xl font-bold text-primary mb-2">Produk Tidak Ditemukan</h5>
  <p class="text-gray-600">Belum ada produk yang tersedia untuk kategori ini.</p>
  </div>
</div>
<% end_if %>


<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

