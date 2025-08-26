<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md border border-gray-200">
  <h2 class="text-2xl font-bold text-gray-800 mb-6">Profil Pengguna</h2>

  <form action="$Link(doUpdateProfil)" method="POST" class="space-y-5">
    <% if $ProfilData %>
      <!-- Firstname -->
      <div>
        <label for="firstname" class="block text-sm font-medium text-gray-700">Firstname</label>
        <input type="text" id="firstname" name="firstname"
               value="$ProfilData.FirstName"
               class="mt-1 w-full border border-gray-300 rounded-md px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               required>
      </div>

      <!-- Surname -->
      <div>
        <label for="surname" class="block text-sm font-medium text-gray-700">Surname</label>
        <input type="text" id="surname" name="surname"
               value="$ProfilData.Surname"
               class="mt-1 w-full border border-gray-300 rounded-md px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               required>
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" name="email"
               value="$ProfilData.Email"
               class="mt-1 w-full border border-gray-300 rounded-md px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               required>
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" id="password" name="password"
               class="mt-1 w-full border border-gray-300 rounded-md px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Kosongkan jika tidak ingin mengubah">
      </div>
    <% end_if %>

    <!-- Save Button -->
    <div class="pt-4">
      <button type="submit"
              class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-md hover:bg-blue-700 transition duration-200">
        Simpan Perubahan
      </button>
    </div>
  </form>
</div>
