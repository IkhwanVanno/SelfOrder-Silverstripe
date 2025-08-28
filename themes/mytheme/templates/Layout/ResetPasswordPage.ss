<main class="min-h-screen flex items-center justify-center py-6 px-4">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-primary text-center py-8">
                <i class="fa-regular fa-key"></i>
                <h4 class="text-white font-bold text-2xl">Atur ulang kata sandi</h4>
            </div>
            
            <!-- Body -->
            <div class="bg-light p-6 md:p-8">
                <form action="$BaseHref/auth/resetpassword?token=$Token" method="POST" class="space-y-6">

                    <!-- Password Fields -->
                    <div>
                        <label for="new_password_1" class="block font-semibold text-primary mb-2">
                            <i class="fas fa-lock mr-2"></i>Kata Sandi
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   class="w-full px-4 py-3 pr-12 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                                   id="new_password_1" 
                                   name="new_password_1" 
                                   required 
                                   placeholder="Masukkan kata sandi">
                            <button type="button" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary hover:text-secondary/80 transition-colors duration-200" 
                                    onclick="togglePassword('new_password_1', 'passwordToggle1')">
                                <i class="fas fa-eye" id="passwordToggle1"></i>
                            </button>
                        </div>
                        <small class="text-gray-600 mt-1 block">Minimal 6 karakter</small>
                    </div>

                    <div>
                        <label for="new_password_2" class="block font-semibold text-primary mb-2">
                            <i class="fas fa-lock mr-2"></i>Konfirmasi Kata Sandi
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   class="w-full px-4 py-3 pr-12 rounded-xl border-2 border-accent bg-white focus:border-secondary focus:outline-none transition-colors duration-200" 
                                   id="new_password_2" 
                                   name="new_password_2" 
                                   required 
                                   placeholder="Konfirmasi kata sandi">
                            <button type="button" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary hover:text-secondary/80 transition-colors duration-200" 
                                    onclick="togglePassword('new_password_2', 'passwordToggle2')">
                                <i class="fas fa-eye" id="passwordToggle2"></i>
                            </button>
                        </div>
                        <div id="passwordMatch" class="mt-2"></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-secondary text-white font-bold py-4 px-6 rounded-xl hover:bg-secondary/90 transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5" 
                            id="submitBtn">
                        <i class="fas fa-user-plus mr-2"></i>Ubah sandi
                    </button>
                </form>
            </div>
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
document.getElementById('new_password_2').addEventListener('input', function() {
    const password1 = document.getElementById('new_password_1').value;
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
document.getElementById('new_password_1').addEventListener('input', function() {
    const password = this.value;
    const minLength = password.length >= 8;
    const hasNumber = /\d/.test(password);
    const hasLetter = /[a-zA-Z]/.test(password);
    
    let strength = 0;
    if (minLength) strength++;
    if (hasNumber) strength++;
    if (hasLetter) strength++;
    
    // You can add password strength indicator here if needed
});
</script>
