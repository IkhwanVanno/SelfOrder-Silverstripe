<main class="min-h-screen flex items-center justify-center py-6 px-4">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-primary text-center py-8">
                <i class="fas fa-user-plus text-4xl text-white mb-4"></i>
                <h4 class="text-white font-bold text-2xl">Daftar Akun Baru</h4>
            </div>
            
            <!-- Body -->
            <div class="bg-light p-6 md:p-8">
                <form action="$BaseHref/auth/register" method="POST" class="space-y-6">
                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="register_first_name" class="block font-semibold text-primary mb-2">
                                <i class="fas fa-user mr-2"></i>Nama Depan
                            </label>
                            <input type="text" 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                                   id="register_first_name" 
                                   name="register_first_name" 
                                   required 
                                   placeholder="Nama depan">
                        </div>
                        <div>
                            <label for="register_last_name" class="block font-semibold text-primary mb-2">
                                <i class="fas fa-user mr-2"></i>Nama Belakang
                            </label>
                            <input type="text" 
                                   class="w-full px-4 py-3 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                                   id="register_last_name" 
                                   name="register_last_name" 
                                   required 
                                   placeholder="Nama belakang">
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="register_email" class="block font-semibold text-primary mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </label>
                        <input type="email" 
                               class="w-full px-4 py-3 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                               id="register_email" 
                               name="register_email" 
                               required 
                               placeholder="Masukkan email Anda">
                    </div>

                    <!-- Password Fields -->
                    <div>
                        <label for="register_password_1" class="block font-semibold text-primary mb-2">
                            <i class="fas fa-lock mr-2"></i>Kata Sandi
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   class="w-full px-4 py-3 pr-12 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                                   id="register_password_1" 
                                   name="register_password_1" 
                                   required 
                                   placeholder="Masukkan kata sandi">
                            <button type="button" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary hover:text-secondary/80 transition-colors duration-200" 
                                    onclick="togglePassword('register_password_1', 'passwordToggle1')">
                                <i class="fas fa-eye" id="passwordToggle1"></i>
                            </button>
                        </div>
                        <small class="text-gray-600 mt-1 block">Minimal 6 karakter</small>
                    </div>

                    <div>
                        <label for="register_password_2" class="block font-semibold text-primary mb-2">
                            <i class="fas fa-lock mr-2"></i>Konfirmasi Kata Sandi
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   class="w-full px-4 py-3 pr-12 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                                   id="register_password_2" 
                                   name="register_password_2" 
                                   required 
                                   placeholder="Konfirmasi kata sandi">
                            <button type="button" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary hover:text-secondary/80 transition-colors duration-200" 
                                    onclick="togglePassword('register_password_2', 'passwordToggle2')">
                                <i class="fas fa-eye" id="passwordToggle2"></i>
                            </button>
                        </div>
                        <div id="passwordMatch" class="mt-2"></div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div>
                        <div class="flex items-start">
                            <input type="checkbox" 
                                   class="w-4 h-4 text-secondary border-secondary rounded focus:ring-secondary focus:ring-2 mt-1 flex-shrink-0" 
                                   id="terms" 
                                   required>
                            <label class="ml-2 text-primary text-sm leading-relaxed" for="terms">
                                Saya setuju dengan 
                                <a href="#" class="font-semibold text-secondary hover:text-secondary/80 transition-colors duration-200">
                                    Syarat dan Ketentuan
                                </a>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-secondary text-white font-bold py-4 px-6 rounded-xl hover:bg-secondary/90 transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5" 
                            id="submitBtn">
                        <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                    </button>
                </form>
                
                <!-- Login Link -->
                <div class="text-center mt-6">
                    <p class="text-primary">
                        Sudah memiliki akun? 
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

<script>
function togglePassword(fieldId, toggleId) {
    const passwordField = document.getElementById(fieldId);
    const passwordToggle = document.getElementById(toggleId);
    
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

// Password matching validation
document.getElementById('register_password_2').addEventListener('input', function() {
    const password1 = document.getElementById('register_password_1').value;
    const password2 = this.value;
    const matchDiv = document.getElementById('passwordMatch');
    const submitBtn = document.getElementById('submitBtn');
    
    if (password2 === '') {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (password1 === password2) {
        matchDiv.innerHTML = '<small class="text-green-600 flex items-center"><i class="fas fa-check mr-1"></i>Kata sandi cocok</small>';
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        matchDiv.innerHTML = '<small class="text-red-600 flex items-center"><i class="fas fa-times mr-1"></i>Kata sandi tidak cocok</small>';
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
});

// Password strength indicator
document.getElementById('register_password_1').addEventListener('input', function() {
    const password = this.value;
    const minLength = password.length >= 6;
    const hasNumber = /\d/.test(password);
    const hasLetter = /[a-zA-Z]/.test(password);
    
    let strength = 0;
    if (minLength) strength++;
    if (hasNumber) strength++;
    if (hasLetter) strength++;
    
    // You can add password strength indicator here if needed
});
</script>
