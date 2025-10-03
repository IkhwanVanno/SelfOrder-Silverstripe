<main class="min-h-screen flex items-center justify-center py-6 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-primary text-center py-8">
                <i class="fas fa-sign-in-alt text-4xl text-white mb-4"></i>
                <h4 class="text-white font-bold text-2xl">Masuk</h4>
            </div>
            
            <!-- Body -->
            <div class="bg-light p-6 md:p-8">
                <a href="$BaseHref/auth/google-login"
                class="w-full mb-3 flex items-center justify-center border-2 border-red-500 text-red-600 font-semibold py-3 rounded-xl hover:bg-red-50 transition-colors duration-200">
                    <!-- Google Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="mr-2">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Masuk dengan Google
                </a>
                <form action="$BaseHref/auth/login" method="POST" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <label for="login_email" class="block font-semibold text-primary mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </label>
                        <input type="email" 
                               class="w-full px-4 py-3 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                               id="login_email" 
                               name="login_email" 
                               required 
                               placeholder="Masukkan email Anda">
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="login_password" class="block font-semibold text-primary mb-2">
                            <i class="fas fa-lock mr-2"></i>Kata Sandi
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   class="w-full px-4 py-3 pr-12 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                                   id="login_password" 
                                   name="login_password" 
                                   required 
                                   placeholder="Masukkan kata sandi">
                            <button type="button" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary hover:text-secondary/80 transition-colors duration-200" 
                                    onclick="togglePassword()">
                                <i class="fas fa-eye" id="passwordToggle"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   class="w-4 h-4 text-secondary border-secondary rounded focus:ring-secondary focus:ring-2" 
                                   id="login_remember" 
                                   name="login_remember" 
                                   value="1">
                            <label class="ml-2 text-primary" for="login_remember">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-secondary text-white font-bold py-4 px-6 rounded-xl hover:bg-secondary/90 transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk Sekarang
                    </button>
                </form>
                
                <!-- Register Link -->
                <div class="text-center mt-6">
                    <p class="text-primary">
                        Belum memiliki akun? 
                        <a href="$BaseHref/auth/register" 
                           class="font-semibold text-secondary hover:text-secondary/80 transition-colors duration-200">
                            Daftar di sini
                        </a>
                    </p>
                    <p class="text-primary">
                        Lupa kata sandi? 
                        <a href="$BaseHref/auth/forgotpassword" 
                           class="font-semibold text-secondary hover:text-secondary/80 transition-colors duration-200">
                            Atur ulang di sini
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Back to Home -->
        <div class="text-center mt-4">
            <a href="$BaseHref" 
               class="text-primary hover:text-primary/80 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</main>

<script>
function togglePassword() {
    const passwordField = document.getElementById('login_password');
    const passwordToggle = document.getElementById('passwordToggle');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        passwordToggle.classList.remove('fa-eye');
        passwordToggle.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        passwordToggle.classList.remove('fa-eye-slash');
        passwordToggle.classList.add('fa-eye');
    }
}
</script>