<main class="min-h-screen flex items-center justify-center py-6 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-primary text-center py-8">
                <i class="fa-regular fa-key"></i>
                <h4 class="text-white font-bold text-2xl">Kirim Verifikasi</h4>
            </div>
            
            <!-- Body -->
            <div class="bg-light p-6 md:p-8">
                <form action="$BaseHref/auth/forgotpassword" method="POST" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <label for="forgot_email" class="block font-semibold text-primary mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </label>
                        <input type="email" 
                               class="w-full px-4 py-3 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                               id="forgot_email" 
                               name="forgot_email" 
                               required 
                               placeholder="Masukkan email Anda">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-secondary text-white font-bold py-4 px-6 rounded-xl hover:bg-secondary/90 transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-in-alt mr-2"></i>Kirim Verifikasi
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
                        Ingat kata sandi? 
                        <a href="$BaseHref/auth/login" 
                           class="font-semibold text-secondary hover:text-secondary/80 transition-colors duration-200">
                            Masuk di sini
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