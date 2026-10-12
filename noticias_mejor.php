<!-- MODAL CON HORAS OBLIGATORIAS -->
<div id="modal" class="fixed inset-0 bg-black/90 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl p-12 max-w-5xl w-full mx-6 max-h-screen overflow-y-auto text-gray-800">
        <h2 class="text-6xl font-black text-center text-purple-600 mb-10">
            Nuevo / Editar Evento
        </h2>

        <!-- FORMULARIO COMPLETO (todo dentro del <form>) -->
        <form method="POST" class="space-y-8">
            <input type="hidden" name="id" id="edit-id">

            <input type="text" name="titulo" id="titulo" required placeholder="Título del evento *"
                   class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-3xl font-bold text-center focus:border-purple-600 outline-none text-black">

            <textarea name="descripcion" id="descripcion" rows="5" placeholder="Descripción (opcional)"
                      class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-xl focus:border-purple-600 outline-none text-black"></textarea>

            <div class="grid md:grid-cols-2 gap-8">

                <div>
                    <label class="block text-xl font-bold mb-2 text-purple-600">RUT del administrador *</label>
                    <select name="rut_admin" id="rut_admin" required
                            class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl text-center focus:border-purple-600 outline-none text-black">
                        <option value="">Seleccione un RUT</option>
                        <?php foreach ($ruts_activos as $usuario): ?>
                            <option value="<?= htmlspecialchars($usuario['rut_usuario']) ?>">
                                <?= htmlspecialchars($usuario['rut_usuario'] . " - " . $usuario['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xl font-bold mb-2 text-purple-600">Fecha *</label>
                    <input type="date" name="fecha" id="fecha" required
                           class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl text-center focus:border-purple-600 outline-none text-black">
                </div>

                <div>
                    <label class="block text-xl font-bold mb-2 text-purple-600">Hora inicio *</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" required
                           class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl text-center focus:border-purple-600 outline-none text-black">
                </div>

                <div>
                    <label class="block text-xl font-bold mb-2 text-purple-600">Hora fin *</label>
                    <input type="time" name="hora_fin" id="hora_fin" required
                           class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl text-center focus:border-purple-600 outline-none text-black">
                </div>

                <div>
                    <label class="block text-xl font-bold mb-2 text-purple-600">Estado</label>
                    <select name="estado" id="estado"
                            class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl font-bold focus:border-purple-600 outline-none text-black">
                        <option value="Agendado">Agendado</option>
                        <option value="En progreso">En progreso</option>
                        <option value="Finalizado">Finalizado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>
            </div>

            <!-- BOTONES AHORA DENTRO DEL FORMULARIO -->
            <div class="flex gap-8 justify-center mt-10">
                <button type="submit" name="guardar" 
                        class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-black text-3xl py-6 px-24 rounded-3xl shadow-2xl hover:scale-110 transition">
                    GUARDAR Y PUBLICAR
                </button>
                <button type="button" 
                        onclick="document.getElementById('modal').classList.add('hidden'); resetForm();"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-black text-3xl py-6 px-20 rounded-3xl transition">
                    Cancelar
                </button>
            </div>
        </form>
        <!-- Fin del formulario -->
    </div>
</div>