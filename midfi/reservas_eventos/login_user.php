<?php
/**
 * Página de inicio de sesión para usuarios
 * Sistema de Reservas de Eventos de Comida - Mideliz
 */

session_start();
require_once "includes/config.php"; // Contiene constantes DB_HOST, DB_NAME, DB_USER, DB_PASS

$site_name = "Mideliz";
$title = "Iniciar Sesión";

$mensaje = "";
$usuario_logueado = isset($_SESSION["usuario_id"]); // ✅ Para evitar error de variable no definida

// Si ya está logueado, redirigir al inicio
if ($usuario_logueado) {
    header("Location: index.php");
    exit();
}

// Procesar el formulario de login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        $mensaje = "⚠️ Todos los campos son obligatorios.";
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario["password"])) {
                // Guardar sesión
                $_SESSION["usuario_id"] = $usuario["id"];
                $_SESSION["usuario"] = $usuario["nombre"];

                header("Location: index.php");
                exit();
            } else {
                $mensaje = "⚠️ Correo o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $mensaje = "❌ Error en la conexión a la base de datos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title); ?> - <?php echo htmlspecialchars($site_name); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../styles.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
  <style>
    /* Asegurar que las animaciones funcionen en móviles */
    * {
      -webkit-transform: translate3d(0, 0, 0);
      transform: translate3d(0, 0, 0);
    }
  </style>
</head>
<body class="flex flex-col min-h-screen font-poppins bg-gradient-to-br from-red-50 via-white to-red-100">

  <!-- Navigation -->
  <nav class="fixed w-full bg-white bg-opacity-90 backdrop-blur-md shadow-md z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
      <a href="index.php" class="text-xl sm:text-2xl font-bold text-red-600 hover:text-red-700 transition">
        <?php echo htmlspecialchars($site_name); ?>
      </a>

      <!-- Menú Desktop -->
      <ul class="hidden lg:flex items-center space-x-6 xl:space-x-8 text-base xl:text-lg">
        <li><a href="index.php#inicio" class="nav-link hover:text-red-600 transition">Inicio</a></li>
        <li><a href="menu.php" class="nav-link hover:text-red-600 transition">Menú</a></li>
        <li><a href="about_us.php" class="nav-link hover:text-red-600 transition">Sobre Nosotros</a></li>

        <?php if ($usuario_logueado): ?>
          <li><a href="contacto.php" class="nav-link hover:text-red-600 transition">Contacto</a></li>
          <li class="relative">
            <button id="userMenuButton" class="focus:outline-none">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" class="w-7 h-7 xl:w-8 xl:h-8 text-gray-700 hover:text-red-600 transition">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 
                    9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 
                    21a8.966 8.966 0 0 1-5.982-2.275M15 
                    9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </button>
            <div id="userMenu"
                class="hidden absolute right-0 mt-4 w-48 bg-white border bg-opacity-90 border-gray-200 rounded-xl shadow-lg py-2 z-50">
              <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Editar perfil</a>
              <a href="logout_user.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Cerrar sesión</a>
            </div>
          </li>
        <?php else: ?>
          <li><a href="admin/login.php" class="nav-link hover:text-red-600 transition">Admin</a></li>
        <?php endif; ?>
      </ul>

      <!-- Botón Hamburguesa Mobile -->
      <button id="mobileMenuButton" class="lg:hidden focus:outline-none z-50">
        <svg id="hamburgerIcon" class="w-7 h-7 text-gray-700 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        <svg id="closeIcon" class="w-7 h-7 text-gray-700 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <!-- Menú Mobile -->
    <div id="mobileMenu" class="lg:hidden bg-white border-t border-gray-200 max-h-0 overflow-hidden transition-all duration-300">
      <ul class="px-4 py-4 space-y-3">
        <li><a href="index.php#inicio" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Inicio</a></li>
        <li><a href="menu.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Menú</a></li>
        <li><a href="about_us.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Sobre Nosotros</a></li>
        
        <?php if ($usuario_logueado): ?>
          <li><a href="contacto.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Contacto</a></li>
          <li><a href="profile.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Editar perfil</a></li>
          <li><a href="logout_user.php" class="block py-2 text-red-600 hover:text-red-700 transition font-semibold mobile-link">Cerrar sesión</a></li>
        <?php else: ?>
          <li><a href="admin/login.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Admin</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <!-- Login Section -->
  <main class="flex-1 flex items-center justify-center px-4 sm:px-6 pt-20 sm:pt-24 pb-8">
    <div class="w-full max-w-md">
      <!-- Login Card -->
      <div class="login-card bg-white rounded-2xl shadow-2xl p-6 sm:p-8 lg:p-10">
        <div class="text-center mb-6 sm:mb-8">
          <div class="inline-block p-3 bg-red-100 rounded-full mb-4">
            <i class="fas fa-user-circle text-4xl sm:text-5xl text-red-600"></i>
          </div>
          <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-red-600">
            <?php echo htmlspecialchars($title); ?>
          </h2>
          <p class="text-gray-600 text-sm sm:text-base mt-2">Bienvenido de vuelta a <?php echo htmlspecialchars($site_name); ?></p>
        </div>

        <?php if ($mensaje): ?>
          <div class="mensaje-alerta bg-red-100 border border-red-400 text-red-600 px-3 sm:px-4 py-2 sm:py-3 rounded-lg mb-4 sm:mb-6 text-center text-sm sm:text-base">
            <?php echo htmlspecialchars($mensaje); ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4 sm:space-y-5">
          <div class="form-group">
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-envelope text-red-600 mr-2"></i>Correo electrónico
            </label>
            <input type="email" 
                   id="email"
                   name="email" 
                   placeholder="ejemplo@correo.com" 
                   required
                   class="w-full p-3 sm:p-3.5 text-sm sm:text-base border-2 border-gray-300 rounded-lg focus:border-red-600 focus:ring-2 focus:ring-red-200 outline-none transition-all duration-300">
          </div>

          <div class="form-group">
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-lock text-red-600 mr-2"></i>Contraseña
            </label>
            <input type="password" 
                   id="password"
                   name="password" 
                   placeholder="••••••••" 
                   required
                   class="w-full p-3 sm:p-3.5 text-sm sm:text-base border-2 border-gray-300 rounded-lg focus:border-red-600 focus:ring-2 focus:ring-red-200 outline-none transition-all duration-300">
          </div>

          <button type="submit" 
                  class="submit-btn w-full bg-red-600 hover:bg-red-700 text-white py-3 sm:py-3.5 rounded-lg font-semibold shadow-lg transition-all duration-300 hover:-translate-y-1 active:translate-y-0 text-sm sm:text-base flex items-center justify-center space-x-2">
            <span>Iniciar Sesión</span>
            <i class="fas fa-arrow-right"></i>
          </button>
        </form>

        <div class="mt-6 sm:mt-8 text-center">
          <p class="text-gray-600 text-xs sm:text-sm">
            ¿No tienes cuenta? 
            <a href="registrarse.php" class="text-red-600 font-semibold hover:text-red-700 hover:underline transition">
              Regístrate aquí
            </a>
          </p>
        </div>
      </div>

      <!-- Info adicional -->
      <div class="mt-6 sm:mt-8 text-center">
        <p class="text-gray-600 text-xs sm:text-sm">
          ¿Eres administrador? 
          <a href="admin/login.php" class="text-red-600 font-semibold hover:text-red-700 hover:underline transition">
            Ingresa aquí
          </a>
        </p>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-red-700 text-white py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center">
      <p class="text-sm sm:text-base">© <?php echo date("Y"); ?> <?php echo htmlspecialchars($site_name); ?>. Todos los derechos reservados.</p>
      <div class="mt-3 sm:mt-4 flex justify-center space-x-4 sm:space-x-6 text-xl sm:text-2xl">
        <a href="#" class="hover:text-red-300 transition"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="hover:text-red-300 transition"><i class="fab fa-twitter"></i></a>
        <a href="#" class="hover:text-red-300 transition"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </footer>

  <script>
  window.addEventListener("load", () => {
    gsap.registerPlugin(ScrollTrigger);

    // Detectar si es dispositivo móvil
    const isMobile = window.innerWidth < 768;

    // Configuración de animaciones según el dispositivo
    const animConfig = {
      mobile: {
        duration: 0.8,
        ease: "power2.out"
      },
      desktop: {
        duration: 1,
        ease: "power4.out"
      }
    };

    const config = isMobile ? animConfig.mobile : animConfig.desktop;

    // Animación Navbar
    gsap.from("nav", {
      y: -100,
      opacity: 0,
      duration: config.duration,
      ease: config.ease
    });

    // === MENÚ MOBILE ===
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');
    const hamburgerIcon = document.getElementById('hamburgerIcon');
    const closeIcon = document.getElementById('closeIcon');
    let menuOpen = false;

    if (mobileMenuButton && mobileMenu) {
      mobileMenuButton.addEventListener('click', (e) => {
        e.stopPropagation();
        menuOpen = !menuOpen;
        
        if (menuOpen) {
          mobileMenu.style.maxHeight = mobileMenu.scrollHeight + 'px';
          hamburgerIcon.classList.add('hidden');
          closeIcon.classList.remove('hidden');
        } else {
          mobileMenu.style.maxHeight = '0';
          hamburgerIcon.classList.remove('hidden');
          closeIcon.classList.add('hidden');
        }
      });

      // Cerrar menú al hacer clic en un enlace
      document.querySelectorAll('.mobile-link').forEach(link => {
        link.addEventListener('click', () => {
          mobileMenu.style.maxHeight = '0';
          hamburgerIcon.classList.remove('hidden');
          closeIcon.classList.add('hidden');
          menuOpen = false;
        });
      });

      // Cerrar menú al hacer clic fuera
      document.addEventListener('click', (e) => {
        if (menuOpen && !mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
          mobileMenu.style.maxHeight = '0';
          hamburgerIcon.classList.remove('hidden');
          closeIcon.classList.add('hidden');
          menuOpen = false;
        }
      });
    }

    // Toggle menú usuario (desktop)
    const userMenuButton = document.getElementById('userMenuButton');
    const userMenu = document.getElementById('userMenu');

    if (userMenuButton && userMenu) {
      userMenuButton.addEventListener('click', (e) => {
        e.stopPropagation();
        userMenu.classList.toggle('hidden');
      });
      document.addEventListener('click', (e) => {
        if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
          userMenu.classList.add('hidden');
        }
      });
    }

    // Animación Login Card
    gsap.from(".login-card", {
      opacity: 0,
      y: isMobile ? 30 : 50,
      scale: 0.95,
      duration: config.duration,
      delay: 0.3,
      ease: "back.out(1.2)"
    });

    // Animación elementos del formulario
    gsap.from(".form-group", {
      opacity: 0,
      x: isMobile ? 0 : -30,
      duration: config.duration * 0.8,
      stagger: 0.1,
      delay: 0.5,
      ease: config.ease
    });

    gsap.from(".submit-btn", {
      opacity: 0,
      scale: 0.9,
      duration: config.duration * 0.8,
      delay: 0.8,
      ease: "back.out(1.5)"
    });

    // Animación mensaje de alerta si existe
    const mensaje = document.querySelector('.mensaje-alerta');
    if (mensaje) {
      gsap.from(mensaje, {
        opacity: 0,
        y: -20,
        duration: 0.5,
        delay: 0.4,
        ease: "power2.out"
      });
    }

    // Animación footer
    gsap.from("footer", {
      y: isMobile ? 30 : 50,
      opacity: 0,
      duration: config.duration,
      delay: 1,
      ease: config.ease
    });

    // Efecto hover en inputs (solo desktop)
    if (!isMobile) {
      document.querySelectorAll('input[type="email"], input[type="password"]').forEach(input => {
        input.addEventListener('focus', () => {
          gsap.to(input, {
            scale: 1.02,
            duration: 0.3,
            ease: "power2.out"
          });
        });
        
        input.addEventListener('blur', () => {
          gsap.to(input, {
            scale: 1,
            duration: 0.3,
            ease: "power2.out"
          });
        });
      });
    }
  });
</script>
</body>
</html>