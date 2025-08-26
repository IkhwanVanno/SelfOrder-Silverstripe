<style>
.dropdown { position: relative; display: inline-block; }
.dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    min-width: 200px;
    background: #F4F6FF;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}
.dropdown.active .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.dropdown-item {
    display: block;
    width: 100%;
    padding: 12px 16px;
    color: #10375C;
    text-decoration: none;
    transition: background-color 0.2s ease;
}
.dropdown-item:hover {
    background-color: rgba(235, 131, 23, 0.1);
}
</style>
<header class="border-b shadow-sm bg-primary">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-3 md:py-4">
            
            <!-- Logo -->
            <div class="flex items-center w-1/4 md:w-1/4 lg:w-1/6">
                <img src="$SiteConfig.Logo.URL" alt="Logo" class="max-h-8 md:max-h-10 max-w-[100px] md:max-w-[120px] object-contain">
            </div>

            <!-- Nama Perusahaan -->
            <div class="flex items-center justify-center text-center w-1/2 md:w-1/2 lg:w-2/3">
                <div>
                    <h4 class="text-white font-bold text-xl md:text-2xl lg:text-3xl hidden md:block">$SiteConfig.Title</h4>
                    <h6 class="text-white font-bold text-sm md:hidden">$SiteConfig.Title</h6>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="flex items-center justify-end w-1/4 md:w-1/4 lg:w-1/6">
                <div class="dropdown w-full">
                    <button 
                        class="w-full px-3 py-2 md:px-4 md:py-2 bg-transparent border border-white text-white rounded-lg hover:bg-white hover:text-primary transition-colors duration-200 text-sm md:text-base"
                        type="button" 
                        onclick="toggleDropdown()"
                        aria-expanded="false">
                        <i class="fas fa-user mr-1 md:mr-2"></i>
                        <span class="hidden sm:inline">
                            <% if $isLoggedIn %>
                                $CurrentUser.FirstName
                            <% else %>
                                Menu
                            <% end_if %>
                        </span>
                        <i class="fas fa-chevron-down ml-1 md:ml-2 text-xs"></i>
                    </button>
                    <div class="dropdown-menu">
                        <% if $isLoggedIn %>
                        <a class="dropdown-item" href="$BaseHref/Profil">
                            <i class="fa-solid fa-user mr-2"></i>Profil
                        </a>
                        <a class="dropdown-item" href="$BaseHref/Auth/logout">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                        <% else %>
                        <a class="dropdown-item" href="$BaseHref/Auth/login">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a class="dropdown-item" href="$BaseHref/Auth/register">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                        <% end_if %>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>

<script>
function toggleDropdown() {
    const dropdown = document.querySelector('.dropdown');
    dropdown.classList.toggle('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.dropdown');
    if (!dropdown.contains(event.target)) {
        dropdown.classList.remove('active');
    }
});
</script>