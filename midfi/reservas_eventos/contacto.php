<?php
require_once 'includes/config.php';

$mensaje = '';
$tipoMensaje = '';
$site_name = 'Mideliz';

// Inicializar variables para evitar warnings
$nombre = $apellido = $ciudad = $email = $telefono = $fecha_evento = $comentarios = '';
$num_invitados = $tipo_comida_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre        = sanitizar($_POST['nombre']);
    $apellido      = sanitizar($_POST['apellido']);
    $ciudad        = sanitizar($_POST['ciudad']);
    $email         = sanitizar($_POST['correo']);
    $telefono      = sanitizar($_POST['telefono']);
    $fecha_evento  = sanitizar($_POST['fecha_evento']);
    $num_invitados = (int) $_POST['num_invitados'];
    $tipo_comida_id = (int) $_POST['tipo_comida'];
    $comentarios   = sanitizar($_POST['comentarios']);

    $errores = [];

    // --- Validaciones ---
    if (empty($nombre)) $errores[] = "El nombre es requerido";
    if (empty($apellido)) $errores[] = "El apellido es requerido";
    if (empty($ciudad)) $errores[] = "La ciudad es requerida";
    if (empty($email) || !validarEmail($email)) $errores[] = "El correo electrónico es inválido";
    if (empty($telefono)) $errores[] = "El teléfono es requerido";

    if (empty($fecha_evento)) {
        $errores[] = "La fecha del evento es requerida";
    } else {
        $fechaEvento = DateTime::createFromFormat('Y-m-d', $fecha_evento);
        $hoy = new DateTime('today');
        if (!$fechaEvento) {
            $errores[] = "Formato de fecha no válido";
        } elseif ($fechaEvento < $hoy) {
            $errores[] = "La fecha del evento debe ser futura";
        }
    }

    if ($num_invitados < 1 || $num_invitados > 500) $errores[] = "Número de invitados no válido";
    if ($tipo_comida_id <= 0) $errores[] = "Debe seleccionar un tipo de comida";

    // --- Procesamiento ---
    if (empty($errores)) {
        $pdo = conectarDB();

        if ($pdo) {
            try {
                // Verificar si el usuario ya existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    $usuario_id = $usuario['id'];
                } else {
                    // Insertar nuevo usuario
                    $sqlUsuario = "INSERT INTO usuarios (nombre, apellido, ciudad, telefono, email, password)
                                   VALUES (?, ?, ?, ?, ?, NULL)";
                    $stmt = $pdo->prepare($sqlUsuario);
                    $stmt->execute([$nombre, $apellido, $ciudad, $telefono, $email]);
                    $usuario_id = $pdo->lastInsertId();
                }

                // Insertar reserva
                $sqlReserva = "INSERT INTO reservas 
                               (usuario_id, fecha_evento, num_invitados, tipo_comida_id, comentarios, estado_id, fecha_reserva)
                               VALUES (?, ?, ?, ?, ?, 1, ?)";
                $stmt2 = $pdo->prepare($sqlReserva);
                $stmt2->execute([
                    $usuario_id,
                    $fecha_evento,
                    $num_invitados,
                    $tipo_comida_id,
                    $comentarios,
                    date('Y-m-d H:i:s')
                ]);

                $mensaje = "✅ ¡Reserva registrada con éxito!";
                $tipoMensaje = "success";

                // Limpiar valores del formulario
                $nombre = $apellido = $ciudad = $email = $telefono = $fecha_evento = $comentarios = '';
                $num_invitados = $tipo_comida_id = '';
            } catch (PDOException $e) {
                $mensaje = "❌ Error al guardar la reserva. Intenta nuevamente.";
                $tipoMensaje = "error";
                error_log("Error en reserva: " . $e->getMessage());
            }
        } else {
            $mensaje = "❌ Error de conexión a la base de datos.";
            $tipoMensaje = "error";
        }
    } else {
        $mensaje = "Corrige los errores:<br>• " . implode("<br>• ", $errores);
        $tipoMensaje = "error";
    }
}

// --- Obtener tipos de comida ---
$tiposComida = [];
$pdo = conectarDB();
if ($pdo) {
    $stmt = $pdo->query("SELECT id, nombre FROM tipos_comida ORDER BY nombre ASC");
    $tiposComida = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo SITE_NAME; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
  <link rel="stylesheet" href="../styles.css">
</head>
<body class="bg-red-50 font-sans min-h-screen flex flex-col">

   <!-- Navigation -->
  <nav class="fixed w-full bg-white bg-opacity-80 backdrop-blur-md shadow-md z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <a href="../index.html" class="text-2xl font-bold text-red-600 hover:text-red-700 transition">Mideliz</a>
      <div class="flex gap-4">
        <ul class="flex space-x-8 text-lg">
          <li><a href="index.php" class="nav-link">Inicio</a></li>
          <li><a href="menu.php" class="nav-link">Menú</a></li>
          <li><a href="about_us.php" class="nav-link">Sobre Nosotros</a></li>
          <li><a href="contacto.php" class="nav-link">Contacto</a></li>

          <li class="relative">
            <button id="userMenuButton" class="focus:outline-none">
              <!-- Ícono usuario -->
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-700 hover:text-red-600 transition">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 
                    9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 
                    21a8.966 8.966 0 0 1-5.982-2.275M15 
                    9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </button>

            <!-- Menú desplegable -->
            <div id="userMenu"
                class="hidden absolute right-0 mt-4 w-48 bg-white border bg-opacity-90 border-gray-200 rounded-xl shadow-lg py-2 z-50">
              <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Editar perfil</a>
              <a href="logout_user.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Cerrar sesión</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="text-center text-red-600 pt-[80px] pb-16">
    <h1 class="hero-title text-4xl font-bold mb-4 drop-shadow-md">
      <i class="fas fa-calendar-alt mr-2"></i>Reserva tu Evento
    </h1>
    <p class="hero-subtitle text-lg">Organizamos los mejores eventos gastronómicos para ti</p>
  </section>

  <!-- Formulario -->
  <section class="flex-grow">
    <div class="form-card max-w-3xl mx-auto px-6 py-10 bg-white rounded-2xl shadow-lg">
      <?php if ($mensaje): ?>
        <?php mostrarMensaje($mensaje, $tipoMensaje); ?>
      <?php endif; ?>

      <h3 class="text-xl font-semibold text-center mb-6 text-red-600">
        <i class="fas fa-calendar-plus mr-2"></i>Formulario de Reserva
      </h3>

      <form method="POST" action="" class="space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
          <!-- Nombre -->
          <div>
            <label for="nombre" class="block font-medium mb-1">
              <i class="fas fa-user mr-1"></i> Nombre *
            </label>
            <input type="text" id="nombre" name="nombre"
                  value="<?php echo isset($nombre) ? $nombre : ''; ?>"
                  required
                  class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-red-600 outline-none">
          </div>

          <!-- Apellido -->
          <div>
            <label for="apellido" class="block font-medium mb-1">
              <i class="fas fa-user mr-1"></i> Apellido *
            </label>
            <input type="text" id="apellido" name="apellido"
                  value="<?php echo isset($apellido) ? $apellido : ''; ?>"
                  required
                  class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-red-600 outline-none">
          </div>

          <!-- Ciudad -->
          <div>
            <label for="ciudad" class="block font-medium mb-1">
              <i class="fas fa-city mr-1"></i> Ciudad *
            </label>
            <input type="text" id="ciudad" name="ciudad"
                  value="<?php echo isset($ciudad) ? $ciudad : ''; ?>"
                  required
                  class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-red-600 outline-none">
          </div>

          <!-- Correo -->
          <div>
            <label for="correo" class="block font-medium mb-1">
              <i class="fas fa-envelope mr-1"></i> Correo Electrónico *
            </label>
            <input type="email" id="correo" name="correo"
                  value="<?php echo isset($email) ? $email : ''; ?>"
                  required
                  class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-red-600 outline-none">
          </div>

          <!-- Teléfono -->
          <div>
            <label for="telefono" class="block font-medium mb-1">
              <i class="fas fa-phone mr-1"></i> Teléfono *
            </label>
            <input type="tel" id="telefono" name="telefono"
                  value="<?php echo isset($telefono) ? $telefono : ''; ?>"
                  required
                  class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-red-600 outline-none">
          </div>

          <!-- Fecha -->
          <div>
            <label for="fecha_evento" class="block font-medium mb-1">
              <i class="fas fa-calendar mr-1"></i> Fecha del Evento *
            </label>
            <input type="date" id="fecha_evento" name="fecha_evento"
                  value="<?php echo isset($fecha_evento) ? $fecha_evento : ''; ?>"
                  min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                  required
                  class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-red-600 outline-none">
          </div>

          <!-- Invitados -->
          <div>
            <label for="num_invitados" class="block font-medium mb-1">
              <i class="fas fa-users mr-1"></i> Número de Invitados *
            </label>
            <input type="number" id="num_invitados" name="num_invitados"
                  value="<?php echo isset($num_invitados) ? $num_invitados : ''; ?>"
                  min="1" max="500" required
                  class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-red-600 outline-none">
          </div>

          <!-- Tipo de comida -->
          <div>
            <label for="tipo_comida" class="block font-medium mb-1">
              <i class="fas fa-utensils mr-1"></i> Tipo de Comida *
            </label>
            <select id="tipo_comida" name="tipo_comida" required
                    class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-red-600 outline-none">
                <option value="">Selecciona...</option>
                <?php foreach ($tiposComida as $tipo): ?>
                    <option value="<?php echo $tipo['id']; ?>" 
                        <?php echo (isset($tipo_comida_id) && $tipo_comida_id == $tipo['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($tipo['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Comentarios -->
        <div>
          <label for="comentarios" class="block font-medium mb-1">
            <i class="fas fa-comment mr-1"></i> Comentarios
          </label>
          <textarea id="comentarios" name="comentarios" rows="4"
                    class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-red-600 outline-none"><?php echo isset($comentarios) ? $comentarios : ''; ?></textarea>
        </div>

        <!-- Botón -->
        <div class="text-center">
          <button type="submit" class="submit-btn w-full bg-red-600 text-white font-semibold py-3 rounded-lg hover:opacity-90 transition">
            <i class="fas fa-paper-plane mr-2"></i> Enviar Reserva
          </button>
        </div>
      </form>

    </div>
  </section>

  <!-- Features -->
  <section class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-3 gap-8">
    <div class="feature-card bg-white p-6 rounded-2xl shadow-md text-center">
      <i class="fas fa-star feature-icon"></i>
      <h4 class="text-xl font-semibold mb-2">Calidad Premium</h4>
      <p>Los mejores ingredientes y chefs profesionales para tu evento especial.</p>
    </div>
    <div class="feature-card bg-white p-6 rounded-2xl shadow-md text-center">
      <i class="fas fa-clock feature-icon"></i>
      <h4 class="text-xl font-semibold mb-2">Servicio Puntual</h4>
      <p>Garantizamos la puntualidad y organización perfecta de tu evento.</p>
    </div>
    <div class="feature-card bg-white p-6 rounded-2xl shadow-md text-center">
      <i class="fas fa-heart feature-icon"></i>
      <h4 class="text-xl font-semibold mb-2">Atención Personalizada</h4>
      <p>Cada evento es único y adaptamos nuestro servicio a tus necesidades.</p>
    </div>
  </section>

    <!-- Footer -->
  <footer class="site-footer bg-red-700 text-white py-14 mt-20">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-10 text-center md:text-left">

      <!-- Columna 1: Marca -->
      <div>
        <h4 class="text-2xl font-semibold mb-4"><?php echo $site_name; ?></h4>
        <p class="text-gray-100 leading-relaxed">
          © <?php echo date("Y"); ?> <?php echo $site_name; ?>.  
          Todos los derechos reservados.
        </p>
      </div>

      <!-- Columna 2: Contacto -->
      <div>
        <h4 class="text-2xl font-semibold mb-4">Contacto</h4>
        <ul class="space-y-2 text-gray-100">
          <li><i class="fas fa-phone-alt mr-2"></i> +57 321 771 4480</li>
          <li><i class="fas fa-envelope mr-2"></i> contacto@mideliz.com</li>
          <li><i class="fas fa-map-marker-alt mr-2"></i> Cocina oculta, Medellín</li>
        </ul>
      </div>

      <!-- Columna 3: Redes Sociales -->
      <div>
        <h4 class="text-2xl font-semibold mb-4">Síguenos</h4>
        <div class="flex justify-center md:justify-start space-x-6 text-2xl">
          <a href="#" class="hover:text-red-300 transition" aria-label="Facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" class="hover:text-red-300 transition" aria-label="Twitter">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="#" class="hover:text-red-300 transition" aria-label="Instagram">
            <i class="fab fa-instagram"></i>
          </a>
        </div>
      </div>

    </div>

  <!-- GSAP Animations -->
  <script>
    gsap.registerPlugin(ScrollTrigger);

    const userMenuButton = document.getElementById('userMenuButton');
    const userMenu = document.getElementById('userMenu');

    userMenuButton.addEventListener('click', () => {
      userMenu.classList.toggle('hidden');
    });

    // Hero
    gsap.from(".hero-title", {
      opacity: 0,
      y: -50,
      duration: 1,
      ease: "power2.out"
    });

    gsap.from(".hero-subtitle", {
      opacity: 0,
      y: 50,
      duration: 1,
      delay: 0.3,
      ease: "power2.out"
    });

    // Formulario
    gsap.from(".form-card", {
      scrollTrigger: {
        trigger: ".form-card",
        start: "top 80%"
      },
      opacity: 0,
      scale: 0.8,
      duration: 1,
      ease: "back.out(1.7)"
    });

    // Features
    gsap.from(".feature-card", {
      scrollTrigger: {
        trigger: ".feature-card",
        start: "top 85%"
      },
      opacity: 0,
      y: 50,
      duration: 1,
      stagger: 0.3,
      ease: "power2.out"
    });

    // Footer
    gsap.from(".site-footer", {
      scrollTrigger: {
        trigger: ".site-footer",
        start: "top 95%"
      },
      opacity: 0,
      y: 40,
      duration: 1,
      ease: "power2.out"
    });
  </script>
</body>
</html>
