<?php
require_once 'config.php';

// === EVENTOS DEL AÑO (calendario) ===
$eventos_calendario = $pdo->query("
    SELECT Fecha_evento, Nombre_evento, Hora_inicio, Hora_fin, Descripción 
    FROM eventos 
    WHERE YEAR(Fecha_evento) >= YEAR(CURDATE())
    ORDER BY Fecha_evento
")->fetchAll();

$eventos_js = [];
foreach ($eventos_calendario as $e) {
    $fecha = $e['Fecha_evento'];
    if (!isset($eventos_js[$fecha])) $eventos_js[$fecha] = [];
    $eventos_js[$fecha][] = $e;
}

// === PRÓXIMOS EVENTOS ===
$proximos_eventos = $pdo->query("
    SELECT * FROM eventos 
    WHERE Fecha_evento >= CURDATE() 
    ORDER BY Fecha_evento ASC LIMIT 6
")->fetchAll();

// === FOTOS PARA CARRUSEL (máximo 15) ===
try {
    $fotos = $pdo->query("SELECT ruta FROM imagenes ORDER BY fecha_subida DESC LIMIT 15")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $fotos = [];
}

$usuario_logueado = isset($_SESSION['rut']) && isset($_SESSION['nombre']);
$nombre_usuario = $_SESSION['nombre'] ?? '';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJVV La Flecha • Coihueco</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(to bottom, #ecfdf5, #ccfbf1); }
        .hover-scale { transition: all 0.4s ease; }
        .hover-scale:hover { transform: translateY(-10px) scale(1.03); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .dia-evento { background: linear-gradient(135deg, #10b981, #14b8a6) !important; color: white !important; font-weight: 900; }
    </style>
</head>
<body class="min-h-screen">

    <!-- NAV -->
    <nav class="bg-gradient-to-r from-emerald-800 to-teal-800 text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-6 py-5 flex flex-wrap justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 text-3xl font-black hover:scale-110 transition">
                JJVV La Flecha
            </a>
            <div class="flex gap-6 text-xl font-bold items-center mt-4 md:mt-0">
                <a href="index.php" class="hover:text-emerald-200">Inicio</a>
                <a href="avisos.php" class="hover:text-emerald-200">Noticias</a>
                <a href="galeria_publica.php" class="hover:text-emerald-200">Galería</a>
                <a href="reservar_sede.php" class="bg-emerald-400 text-emerald-900 px-6 py-3 rounded-full font-black hover:bg-emerald-300">
                    Reserva de Sede
                </a>
                <?php if ($usuario_logueado): ?>
                    <div class="flex items-center gap-4 border-l-2 border-white/30 pl-6">
                        <span><?= htmlspecialchars($nombre_usuario) ?></span>
                        <a href="dashboard.php" class="bg-white/20 hover:bg-white/30 px-6 py-3 rounded-full">Panel</a>
                        <a href="login.php?logout=1" class="text-red-300 text-sm">Salir</a>
                    </div>
                <?php else: ?>
                    <a href="#" onclick="document.getElementById('modal-login').classList.remove('hidden')" 
                       class="bg-white/20 hover:bg-white/30 px-8 py-3 rounded-full font-bold">
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- MODAL LOGIN -->
    <?php if (!$usuario_logueado): ?>
    <div id="modal-login" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-3xl p-10 max-w-md w-full mx-6 shadow-2xl">
            <h3 class="text-4xl font-black text-emerald-800 text-center mb-8">Acceso Administrativo</h3>
            <?php include 'login_form.php'; ?>
            <button onclick="document.getElementById('modal-login').classList.add('hidden')" class="mt-4 text-gray-500 underline block mx-auto">Cerrar</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- HERO -->
    <section class="py-28 px-6 text-center">
        <div class="container mx-auto max-w-5xl">
            <h1 class="text-7xl md:text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-6">
                JJVV LA FLECHA
            </h1>
            <p class="text-4xl font-bold text-emerald-800">Coihueco • Región de Ñuble</p>
        </div>
    </section>

    <!-- CARRUSEL DE FOTOS (PRIMERO!) -->
    <?php if (!empty($fotos)): ?>
    <section class="py-16 px-6 bg-white/80">
        <div class="container mx-auto max-w-7xl">
            <h2 class="text-6xl font-black text-center text-emerald-800 mb-12">Galería Reciente</h2>
            
            <div class="relative">
                <div class="overflow-hidden rounded-3xl shadow-2xl">
                    <div id="carrusel" class="flex transition-transform duration-500 ease-in-out">
                        <?php foreach ($fotos as $ruta): ?>
                        <div class="min-w-full flex-shrink-0">
                            <img src="<?= htmlspecialchars($ruta) ?>" alt="JJVV La Flecha" class="w-full h-96 md:h-screen max-h-screen object-cover">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Flechas -->
                <button onclick="moverCarrusel(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-emerald-700 w-16 h-16 rounded-full shadow-2xl text-4xl flex items-center justify-center">
                    ←
                </button>
                <button onclick="moverCarrusel(1)" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-emerald-700 w-16 h-16 rounded-full shadow-2xl text-4xl flex items-center justify-center">
                    →
                </button>

                <!-- Puntos -->
                <div class="flex justify-center gap-3 mt-8">
                    <?php for($i = 0; $i < count($fotos); $i++): ?>
                    <div class="w-4 h-4 rounded-full transition-all dot <?= $i===0?'bg-emerald-600':'bg-emerald-300' ?>" data-index="<?= $i ?>"></div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="text-center mt-10">
                <a href="galeria_publica.php" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-2xl py-5 px-12 rounded-full inline-block transform hover:scale-110 transition">
                    Ver toda la galería
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CALENDARIO -->
    <section class="py-20 px-6 bg-gradient-to-b from-emerald-50 to-teal-50">
        <div class="container mx-auto max-w-6xl">
            <h2 class="text-6xl font-black text-center text-emerald-800 mb-12">Calendario de Eventos</h2>

            <div class="bg-white rounded-3xl shadow-2xl p-10">
                <div class="flex justify-between items-center mb-8">
                    <button onclick="cambiarMes(-1)" class="text-5xl text-emerald-600 hover:text-emerald-800">←</button>
                    <h3 id="mes-actual" class="text-5xl font-black text-emerald-700">Cargando...</h3>
                    <button onclick="cambiarMes(1)" class="text-5xl text-emerald-600 hover:text-emerald-800">→</button>
                </div>

                <div id="calendario" class="grid grid-cols-7 gap-4 text-center font-bold text-xl"></div>
                <div id="eventos-dia" class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3"></div>
            </div>
        </div>
    </section>

    <!-- PRÓXIMOS EVENTOS -->
    <?php if (!empty($proximos_eventos)): ?>
    <section class="py-20 px-6">
        <div class="container mx-auto max-w-7xl">
            <h2 class="text-5xl font-black text-center text-emerald-800 mb-12">Próximos Eventos</h2>
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                <?php 
                $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                foreach ($proximos_eventos as $e): 
                    $dia = date('d', strtotime($e['Fecha_evento']));
                    $mes = $meses[date('n', strtotime($e['Fecha_evento']))-1];
                ?>
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden hover-scale">
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8 text-center">
                        <div class="text-7xl font-black"><?= $dia ?></div>
                        <div class="text-3xl font-bold"><?= $mes ?></div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-3xl font-black text-emerald-800 mb-4"><?= htmlspecialchars($e['Nombre_evento']) ?></h3>
                        <p class="text-xl text-gray-700">
                            <?= substr($e['Hora_inicio'],0,5) ?> - <?= substr($e['Hora_fin'],0,5) ?> hrs
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CONTACTO + FOOTER -->
    <section class="py-20 px-6 bg-emerald-800/95 text-white">
        <div class="container mx-auto max-w-4xl text-center">
            <h2 class="text-6xl font-black mb-10">Contáctanos</h2>
            <div class="grid md:grid-cols-2 gap-12 max-w-2xl mx-auto">
                <a href="mailto:juntalaflecha@gmail.com" class="bg-white/20 backdrop-blur hover:bg-white/30 p-12 rounded-3xl transition hover-scale">
                    <p class="text-3xl font-black">Correo</p>
                </a>
                <a href="https://www.facebook.com/profile.php?id=100091532520182" target="_blank" class="bg-white/20 backdrop-blur hover:bg-white/30 p-12 rounded-3xl transition hover-scale">
                    <p class="text-3xl font-black">Facebook</p>
                </a>
            </div>
        </div>
    </section>

    <footer class="text-center py-12 bg-emerald-900 text-white text-2xl font-bold">
        © <?= date('Y') ?> Junta de Vecinos La Flecha • Coihueco
    </footer>

    <!-- JAVASCRIPT FINAL (todo corregido y funcional) -->
    <script>
        const eventos = <?= json_encode($eventos_js, JSON_UNESCAPED_UNICODE) ?>;
        let fechaActual = new Date();
        const meses = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

        // === CARRUSEL ===
        let indice = 0;
        const totalFotos = <?= count($fotos) ?>;
        const carrusel = document.getElementById('carrusel');
        const dots = document.querySelectorAll('.dot');

        function actualizarCarrusel() {
            if (!carrusel) return;
            carrusel.style.transform = `translateX(-${indice * 100}%)`;
            dots.forEach((dot, i) => {
                dot.classList.toggle('bg-emerald-600', i === indice);
                dot.classList.toggle('bg-emerald-300', i !== indice);
            });
        }

        function moverCarrusel(dir) {
            indice = (indice + dir + totalFotos) % totalFotos;
            actualizarCarrusel();
        }

        dots.forEach(dot => dot.addEventListener('click', () => {
            indice = parseInt(dot.dataset.index);
            actualizarCarrusel();
        }));

        // Autoplay + pausa al hover
        if (totalFotos > 1) {
            let autoplay = setInterval(() => moverCarrusel(1), 5000);
            carrusel.parentElement.addEventListener('mouseenter', () => clearInterval(autoplay));
            carrusel.parentElement.addEventListener('mouseleave', () => autoplay = setInterval(() => moverCarrusel(1), 5000));
        }

        // === CALENDARIO ===
        function renderizarCalendario() {
            const año = fechaActual.getFullYear();
            const mes = fechaActual.getMonth();
            const primerDia = new Date(año, mes, 1).getDay();
            const diasEnMes = new Date(año, mes + 1, 0).getDate();
            const hoy = new Date(); hoy.setHours(0,0,0,0);

            document.getElementById("mes-actual").textContent = `${meses[mes]} ${año}`;

            let html = "";
            const diasSemana = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
            diasSemana.forEach(d => html += `<div class="text-emerald-700 font-black text-lg">${d}</div>`);

            for (let i = 0; i < (primerDia === 0 ? 6 : primerDia - 1); i++) html += `<div></div>`;

            for (let dia = 1; dia <= diasEnMes; dia++) {
                const fechaStr = `${año}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
                const tieneEvento = eventos[fechaStr];
                const esHoy = new Date(año, mes, dia).toISOString().slice(0,10) === hoy.toISOString().slice(0,10);

                let clase = "rounded-3xl p-6 min-h-32 flex flex-col justify-center cursor-pointer transition-all shadow-md ";
                if (tieneEvento) clase += "dia-evento";
                else clase += "bg-emerald-50 hover:bg-emerald-100";
                if (esHoy) clase += " ring-4 ring-emerald-500 ring-offset-4 bg-emerald-100";

                html += `<div class="${clase}" onclick="mostrarEventos('${fechaStr}')">
                    <div class="text-4xl font-black">${dia}</div>
                    ${tieneEvento ? `<div class="text-xs mt-2 text-white/90">(${tieneEvento.length} evento${tieneEvento.length>1?'s':''})</div>` : ''}
                </div>`;
            }
            document.getElementById("calendario").innerHTML = html;
            document.getElementById("eventos-dia").innerHTML = "";
        }

        function mostrarEventos(fecha) {
            const evts = eventos[fecha] || [];
            let html = `<h3 class="col-span-full text-4xl font-black text-emerald-700 text-center mb-8">
                Eventos del ${parseInt(fecha.split('-')[2])} ${meses[parseInt(fecha.split('-')[1])-1]} ${fecha.split('-')[0]}
            </h3>`;

            if (evts.length === 0) {
                html += `<p class="col-span-full text-center text-2xl text-gray-500 py-16">Sin eventos este día</p>`;
            } else {
                evts.forEach(e => {
                    html += `
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl shadow-xl p-8 hover-scale">
                        <h4 class="text-3xl font-black text-emerald-800 mb-4">${e.Nombre_evento}</h4>
                        <p class="text-xl text-gray-700">
                            ${e.Hora_inicio?.substring(0,5) || '?'} - ${e.Hora_fin?.substring(0,5) || '?'}
                        </p>
                        ${e.Descripción ? `<p class="text-gray-600 mt-4">${e.Descripción}</p>` : ''}
                    </div>`;
                });
            }
            document.getElementById("eventos-dia").innerHTML = html;
        }

        function cambiarMes(delta) {
            fechaActual.setMonth(fechaActual.getMonth() + delta);
            renderizarCalendario();
        }

        // INICIAR
        renderizarCalendario();
        if (totalFotos > 0) actualizarCarrusel();
    </script>
</body>
</html>