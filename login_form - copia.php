<div class="bg-white rounded-2xl shadow-2xl p-10">
    <div class="text-center mb-8">
        <img src="img/logo.jpg" alt="Logo" class="w-28 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-gray-800">Bienvenido/a</h1>
        <p class="text-gray-600 mt-2">Portal de la Junta de Vecinos La Flecha</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/juntavecinos/login.php" class="space-y-6"></form>
        <div>
            <label class="block text-gray-700 font-medium mb-2">RUT o Email</label>
            <input type="text" name="usuario" required autofocus
                   placeholder="11.111.111-1 o tu@email.com"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-4 focus:ring-cyan-400 focus:border-transparent outline-none transition text-black">
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-2">Contraseña</label>
            <input type="password" name="password" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-4 focus:ring-cyan-400 focus:border-transparent outline-none transition text-black">
        </div>
        <button type="submit"
                class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold py-4 rounded-lg hover:from-cyan-600 hover:to-blue-700 transform hover:scale-105 transition duration-300 shadow-lg">
            <i class="fas fa-sign-in-alt mr-2"></i>
            Ingresar al Portal
        </button>
    </form>

</div>