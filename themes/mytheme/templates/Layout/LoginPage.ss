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