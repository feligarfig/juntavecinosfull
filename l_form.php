<!-- login_form.php -->
<div class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 card-hover border border-white/20">
    <h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
        <i class="fas fa-user-lock text-cyan-300 text-4xl"></i>
        Área de Socios
    </h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-600/80 text-white p-4 rounded-xl mb-5 text-center">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['exito'])): ?>
        <div class="bg-green-600/80 text-white p-4 rounded-xl mb-5 text-center">
            <?= htmlspecialchars($_SESSION['exito']) ?>
            <?php unset($_SESSION['exito']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="space-y-6">
        <div>
            <label class="block text-sm font-bold mb-2">RUT (sin puntos ni guión)</label>
            <input type="text" name="rut" required placeholder="11222333" maxlength="12"
                   class="w-full px-4 py-3 rounded-xl bg-black/30 border border-white/30 
                          focus:border-cyan-400 focus:outline-none transition
                          text-white placeholder-gray-400">
        </div>

        <div>
            <label class="block text-sm font-bold mb-2">Contraseña</label>
            <input type="password" name="password" required placeholder="••••••••"
                   class="w-full px-4 py-3 rounded-xl bg-black/30 border border-white/30 
                          focus:border-cyan-400 focus:outline-none transition
                          text-white placeholder-gray-400">
        </div>

        <button type="submit" 
                class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 
                       hover:from-cyan-400 hover:to-blue-500 text-white font-bold 
                       text-xl py-4 rounded-2xl transition transform hover:scale-105 shadow-2xl">
            <i class="fas fa-sign-in-alt mr-2"></i>
            Ingresar al Portal
        </button>
    </form>

    <div class="mt-6 text-center text-sm opacity-80">
        <p>¿Olvidaste tu contraseña? Contacta al administrador</p>
    </div>
</div>