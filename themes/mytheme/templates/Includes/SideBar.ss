<style>
.sidebar-content {
	max-height: 0;
	overflow: hidden;
	transition: max-height 0.3s ease-in-out;
}
.sidebar-content.active {
	max-height: 1000px;
}
@media (min-width: 1024px) {
	.sidebar-content {
		max-height: none;
		overflow: visible;
	}
}
</style>

<!-- Mobile Toggle Button -->
<div class="lg:hidden mb-4">
    <button 
        class="w-full bg-secondary text-white font-bold py-3 px-4 rounded-lg hover:bg-secondary/90 transition-colors duration-200"
        type="button" 
        onclick="toggleSidebar()"
        aria-expanded="false">
        <i class="fas fa-bars mr-2"></i>Menu Kategori
    </button>
</div>

<!-- Sidebar Content -->
<div class="sidebar-content lg:block" id="sidebarContent">
    <div class="bg-accent rounded-lg shadow-lg border border-accent/20 overflow-hidden">
        <!-- Header -->
        <div class="bg-secondary text-center py-4 hidden lg:block">
            <h6 class="text-white font-bold text-lg">
                <i class="fas fa-list mr-2"></i>Kategori Produk
            </h6>
        </div>
        
        <!-- Body -->
        <div class="p-3">
            <!-- Sidebar Menu Content -->
            <div class="mb-4">
                <div class="space-y-3">
                    <!-- Semua Kategori -->
                    <a href="$BaseHref" 
                       class="flex items-center p-3 bg-secondary rounded-lg text-white hover:bg-secondary/85 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group">
                        <div class="mr-3">
                            <% if $SiteConfig.LogoID %>
                                <img src="$SiteConfig.Logo.URL" 
                                     alt="Semua Kategori" 
                                     class="w-11 h-11 rounded-lg object-cover border-2 border-white/20">
                            <% else %>
                                <div class="w-11 h-11 rounded-lg object-cover border-2 border-white/20 bg-white/20 flex items-center justify-center">
                                    <i class="fas fa-th text-white"></i>
                                </div>
                            <% end_if %>
                        </div>
                        <div class="flex-1">
                            <h6 class="text-white font-semibold text-sm md:text-base">Semua Kategori</h6>
                        </div>
                        <div class="text-white group-hover:translate-x-1 transition-transform duration-200">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    
                    <!-- Kategori Produk -->
                    <% loop KategoriProduk %>
                    <a href="$BaseHref?Kategori=$ID" 
                       class="flex items-center p-3 bg-secondary rounded-lg text-white hover:bg-secondary/85 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group">
                        <div class="mr-3">
                            <img src="$Image.URL" 
                                 alt="$Nama" 
                                 class="w-11 h-11 rounded-lg object-cover border-2 border-white/20">
                        </div>
                        <div class="flex-1">
                            <h6 class="text-white font-semibold text-sm md:text-base">$Nama</h6>
                        </div>
                        <div class="text-white group-hover:translate-x-1 transition-transform duration-200">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    <% end_loop %>
                    <!-- Filter Dropdown -->
                    <div class="mt-6">
                        <label for="filterProduk" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-filter mr-1 text-secondary"></i>Filter Produk
                        </label>
                        <div class="relative">
                            <select 
                                id="filterProduk" 
                                class="w-full appearance-none bg-white border border-gray-300 rounded-lg py-2 px-3 pr-8 text-sm font-medium text-gray-700 focus:ring-2 focus:ring-secondary focus:border-secondary transition-all duration-200"
                                onchange="applyFilter()">
                                <option value="">-- Pilih Filter --</option>
                                <option value="populer">Produk yang Sering Dibeli</option>
                                <option value="harga_tertinggi">Harga Tertinggi</option>
                                <option value="harga_terendah">Harga Terendah</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cart & Order Buttons -->
            <div class="mt-4 flex gap-3">
                <a href="$BaseHref/keranjang" 
                class="flex items-center justify-center w-1/2 bg-primary text-white font-bold py-4 px-4 rounded-xl hover:bg-primary/90 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 group">
                    <i class="fas fa-shopping-cart mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    <span class="block md:hidden xl:block">Keranjang</span>
                </a>

                <a href="$BaseHref/order" 
                class="flex items-center justify-center w-1/2 bg-primary text-white font-bold py-4 px-4 rounded-xl hover:bg-primary/90 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 group">
                    <i class="fas fa-check-circle mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    <span class="block md:hidden xl:block">Order</span>
                </a>
            </div>
            <div class="mt-4 gap-3">
                <a href="$BaseHref/reservasi" 
                class="flex items-center justify-center w-full bg-primary text-white font-bold py-4 px-4 rounded-xl hover:bg-primary/90 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 group">
                    <i class="fa-regular fa-calendar-days mr-2 group-hover:scale-110 transition-transform duration-200"></i>
                    <span class="block md:hidden xl:block">Reservasi</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebarContent = document.getElementById('sidebarContent');
    sidebarContent.classList.toggle('active');
}

function applyFilter() {
    const filterSelect = document.getElementById('filterProduk');
    const filterValue = filterSelect.value;
    const urlParams = new URLSearchParams(window.location.search);
    
    if (filterValue) {
        urlParams.set('Filter', filterValue);
    } else {
        urlParams.delete('Filter');
    }
    
    window.location.href = window.location.pathname + '?' + urlParams.toString();
}

// Auto-close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebarContent = document.getElementById('sidebarContent');
    const toggleButton = event.target.closest('button');
    
    if (window.innerWidth < 1024 && 
        !sidebarContent.contains(event.target) && 
        !toggleButton) {
        sidebarContent.classList.remove('active');
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebarContent = document.getElementById('sidebarContent');
    if (window.innerWidth >= 1024) {
        sidebarContent.classList.remove('active');
    }
});
</script>