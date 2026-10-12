<?php
require_once 'config.php';

// Cargar todas las imágenes con su evento (si tiene)
$stmt = $pdo->query("
    SELECT i.*, e.Nombre_evento 
    FROM imagenes i 
    LEFT JOIN eventos e ON i.ID_evento = e.ID_evento 
    ORDER BY i.fecha_subida DESC
");
$imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Fotos - JJVV Villa La Flecha</title>
    <meta name="description" content="Galería de fotos de actividades y eventos de la Junta de Vecinos Villa La Flecha - Coihueco">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 min-h-screen">

    <!-- BARRA DE NAVEGACIÓN PÚBLICA (igual que tu index.php) -->
    <nav class="bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-6 py-5 flex flex-wrap justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 text-2xl md:text-3xl font-black hover:scale-110 transition">
                <i class="fas fa-home"></i> JJVV Villa La Flecha
            </a>
            <div class="flex gap-6 text-lg md:text-xl font-bold mt-4 md:mt-0">
                <a href="index.php" class="hover:text-cyan-200 transition">Inicio</a>
                <a href="noticias.php" class="hover:text-cyan-200 transition">Noticias</a>
                <a href="galeria_publica.php" class="text-cyan-300 underline">Galería</a>
            </div>
        </div>
    </nav>

    <!-- CABECERA -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white py-20">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl md:text-7xl font-black mb-4">
                Galería de Fotos
            </h1>
            <p class="text-xl md:text-3xl font-bold opacity-90">
                Actividades y momentos de nuestra comunidad
            </p>
        </div>
    </div>

    <div class="container mx-auto px-6 py-16 max-w-7xl">

        <?php if (empty($imagenes)): ?>
            <div class="text-center py-20">
                <p class="text-4xl text-gray-600 font-bold">Pronto tendremos fotos de nuestras actividades</p>
                <p class="text-2xl text-gray-500 mt-4">¡Vuelve pronto!</p>
            </div>
        <?php else: ?>
            <!-- GRID DE FOTOS (Lightbox simple y bonito) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($imagenes as $img): ?>
                    <div class="group relative overflow-hidden rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3">
                        <img src="<?= htmlspecialchars($img['ruta']) ?>" 
                             alt="<?= htmlspecialchars($img['nombre_imagen']) ?>"
                             class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-700">
                        
                        <!-- Overlay con título -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-end">
                            <div class="p-6 text-white">
                                <h3 class="text-2xl font-black mb-2"><?= htmlspecialchars($img['nombre_imagen']) ?></h3>
                                <?php if ($img['Nombre_evento']): ?>
                                    <p class="text-lg opacity-90">
                                        <i class="fas fa-calendar-alt mr-2"></i>
                                        <?= htmlspecialchars($img['Nombre_evento']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Click para abrir grande (lightbox simple) -->
                        <a href="<?= htmlspecialchars($img['ruta']) ?>" 
                           target="_blank"
                           class="absolute inset-0 z-10">
                            <span class="sr-only">Abrir imagen grande</span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Pie de página bonito -->
        <div class="text-center mt-20 py-10 border-t-4 border-emerald-200">
            <p class="text-2xl font-bold text-emerald-700">
                Junta de Vecinos La Flecha - Coihueco
            </p>
            <p class="text-lg text-gray-600 mt-2">
                © <?= date('Y') ?> Todos los derechos reservados
            </p>
        </div>
    </div>

    <!-- Lightbox opcional más pro (si quieres, lo activamos después) -->
    <script>
        // Nada extra por ahora, el target="_blank" abre la foto grande y queda perfecto
    </script>
</body>
</html>