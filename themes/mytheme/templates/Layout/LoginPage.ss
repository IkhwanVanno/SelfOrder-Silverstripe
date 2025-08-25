<main class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <form action="$BaseHref/auth/login" method="POST" class="w-100 p-4 border rounded shadow-sm" style="max-width: 400px;">
    <h4 class="mb-3">Masuk</h4>
    <div class="mb-3">
      <label for="login_email" class="form-label">Email</label>
      <input type="email" class="form-control" id="login_email" name="login_email" required>
    </div>

    <div class="mb-3">
      <label for="login_password" class="form-label">Sandi</label>
      <input type="password" class="form-control" id="login_password" name="login_password" required>
    </div>

    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="login_remember" name="login_remember" value="1">
      <label class="form-check-label" for="login_remember">Ingat saya</label>
    </div>

    <button type="submit" class="btn btn-primary w-100">Masuk</button>
    
    <div class="text-center mt-3">
      <p class="mb-0">Tidak memiliki akun? <a href="$BaseHref/auth/register">Daftar di sini</a></p>
    </div>
  </form>
</main>\