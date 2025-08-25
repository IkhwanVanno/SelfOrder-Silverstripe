<header class="bg-light border-bottom">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center py-3">
        <!-- Logo Kiri -->
        <div class="d-flex align-items-center">
          <img src="$SiteConfig.Logo.URL" alt="Logo" class="me-2">
        </div>

        <!-- Nama Perusahaan di Tengah -->
        <div class="text-center flex-grow-1">
          <h5 class="mb-0">$SiteConfig.Title</h5>
        </div>

        <!-- Dropdown Kanan -->
        <div class="dropdown">
          <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="authDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <% if $isLoggedIn %>
              $CurrentUser.FirstName
            <% else %>
              Nama Pengguna
            <% end_if %>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="authDropdown">
            <% if $isLoggedIn %>
            <li><a class="dropdown-item" href="$BaseHref/Auth/logout">Logout</a></li>
            <% else %>
            <li><a class="dropdown-item" href="$BaseHref/Auth/login">Login</a></li>
            <li><a class="dropdown-item" href="$BaseHref/Auth/register">Register</a></li>
            <% end_if %>

          </ul>
        </div>
      </div>
    </div>
  </header>