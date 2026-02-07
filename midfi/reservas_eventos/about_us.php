<?php
// Configuración de la página
$title = "Sobre Nosotros - Mideliz";
$site_name = "Mideliz";

session_start();
$usuario_logueado = isset($_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $title; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
  <link rel="stylesheet" href="../styles.css">
  <style>
    /* Asegurar que las animaciones funcionen en móviles */
    * {
      -webkit-transform: translate3d(0, 0, 0);
      transform: translate3d(0, 0, 0);
    }
  </style>
</head>
<body class="flex flex-col min-h-screen font-poppins bg-gray-50 text-gray-800">

  <!-- Navigation -->
  <nav class="fixed top-0 w-full bg-white bg-opacity-90 backdrop-blur-md shadow-md z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
      <a href="index.php" class="text-xl sm:text-2xl font-bold text-red-600 hover:text-red-700 transition">
        <?php echo $site_name; ?>
      </a>

      <!-- Menú Desktop -->
      <ul class="hidden lg:flex items-center space-x-6 xl:space-x-8 text-base xl:text-lg">
        <li><a href="index.php" class="nav-link hover:text-red-600 transition">Inicio</a></li>
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
          <li><a href="login_user.php" class="nav-link hover:text-red-600 transition">Iniciar Sesión</a></li>
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
        <li><a href="index.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Inicio</a></li>
        <li><a href="menu.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Menú</a></li>
        <li><a href="about_us.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Sobre Nosotros</a></li>
        
        <?php if ($usuario_logueado): ?>
          <li><a href="contacto.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Contacto</a></li>
          <li><a href="profile.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Editar perfil</a></li>
          <li><a href="logout_user.php" class="block py-2 text-red-600 hover:text-red-700 transition font-semibold mobile-link">Cerrar sesión</a></li>
        <?php else: ?>
          <li><a href="login_user.php" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Iniciar Sesión</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <!-- Encabezado -->
  <header class="pt-24 sm:pt-28 md:pt-32 pb-8 sm:pb-12 text-center px-4 fade-in">
    <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-center mb-4 sm:mb-6 md:mb-8 text-red-600">Sobre Nosotros</h2>
    <p class="text-lg sm:text-xl md:text-2xl text-gray-600">Tradición, sabor y compromiso desde Medellín</p>
  </header>

  <!-- Quiénes Somos -->
  <section class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16 fade-in about-section">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
      <div class="about-text">
        <h2 class="text-2xl sm:text-3xl font-semibold text-red-600 mb-3 sm:mb-4">¿Quiénes Somos?</h2>
        <p class="text-gray-700 text-base sm:text-lg mb-4 sm:mb-6">
          Mideliz es un restaurante colombiano que celebra la riqueza de nuestra gastronomía con ingredientes frescos, recetas tradicionales y un toque moderno. Fundado en Medellín, somos más que comida: somos cultura, comunidad y sabor.
        </p>
        <p class="text-gray-700 text-base sm:text-lg">
          Nuestro equipo está comprometido con la excelencia, la sostenibilidad y el servicio cálido que nos caracteriza.
        </p>
      </div>
      <div class="about-img text-center order-first md:order-last">
        <img src="https://images.unsplash.com/photo-1600891964599-f61ba0e24092?auto=format&fit=crop&w=800&q=80" 
             alt="Cocina colombiana" 
             class="rounded-lg shadow-lg mx-auto w-full h-auto max-w-md">
      </div>
    </div>
  </section>

  <!-- Misión y Visión -->
  <section class="bg-white py-12 sm:py-16 fade-in mission-vision">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center">
      <h2 class="text-2xl sm:text-3xl font-semibold text-red-600 mb-6 sm:mb-8">Misión & Visión</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-10 text-left">
        <div class="mv-card bg-gray-50 p-6 sm:p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
          <h3 class="text-lg sm:text-xl font-semibold mb-2 text-red-600">✨ Misión</h3>
          <p class="text-gray-700 text-sm sm:text-base">
            Prestar un servicio de catering presente en los momentos especiales de las personas, facilitando su vida a través de una alimentación balanceada, deliciosa y confiable.
          </p>
        </div>
        <div class="mv-card bg-gray-50 p-6 sm:p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
          <h3 class="text-lg sm:text-xl font-semibold mb-2 text-red-600">🎯 Visión</h3>
          <p class="text-gray-700 text-sm sm:text-base">
            Para el 2027, ser reconocidos como una de las mejores opciones gastronómicas para eventos familiares y empresariales en Medellín y sus alrededores, destacándonos por la calidad, innovación y compromiso sostenible.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Servicios Destacados -->
  <section class="bg-gray-50 py-12 sm:py-16 fade-in services">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center">
      <h2 class="text-2xl sm:text-3xl font-semibold text-red-600 mb-6 sm:mb-8">Lo Que Nos Destaca</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 lg:gap-10">
        <div class="card bg-white rounded-lg shadow-lg p-6 sm:p-8 hover:shadow-2xl transition-shadow">
          <div class="card-icon text-red-500 text-4xl sm:text-5xl mb-3 sm:mb-4">
            <i class="fas fa-concierge-bell"></i>
          </div>
          <h3 class="text-lg sm:text-xl font-semibold mb-2">Catering Personalizado</h3>
          <p class="text-gray-700 text-sm sm:text-base">Nos adaptamos a tus gustos, necesidades y tipo de evento: familiar, social o corporativo.</p>
        </div>
        <div class="card bg-white rounded-lg shadow-lg p-6 sm:p-8 hover:shadow-2xl transition-shadow">
          <div class="card-icon text-red-500 text-4xl sm:text-5xl mb-3 sm:mb-4">
            <i class="fas fa-leaf"></i>
          </div>
          <h3 class="text-lg sm:text-xl font-semibold mb-2">Empaques Sostenibles</h3>
          <p class="text-gray-700 text-sm sm:text-base">Usamos empaques biodegradables y compostables porque creemos en cuidar el planeta mientras disfrutas de nuestros sabores.</p>
        </div>
        <div class="card bg-white rounded-lg shadow-lg p-6 sm:p-8 hover:shadow-2xl transition-shadow md:col-span-2 lg:col-span-1">
          <div class="card-icon text-red-500 text-4xl sm:text-5xl mb-3 sm:mb-4">
            <i class="fas fa-hand-holding-heart"></i>
          </div>
          <h3 class="text-lg sm:text-xl font-semibold mb-2">Compromiso Social</h3>
          <p class="text-gray-700 text-sm sm:text-base">Apoyamos a productores locales y participamos en iniciativas comunitarias que promueven el desarrollo sostenible de nuestra región.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-red-700 text-white py-8 sm:py-12 mt-12 sm:mt-16 footer">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 text-center md:text-left">
      <div>
        <h4 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4"><?php echo $site_name; ?></h4>
        <p class="text-sm sm:text-base">© <?php echo date("Y"); ?> <?php echo $site_name; ?>. Todos los derechos reservados.</p>
        <p class="text-sm sm:text-base">Medellín, Colombia</p>
      </div>
      <div>
        <h4 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Contacto</h4>
        <p class="text-sm sm:text-base">Teléfono: +57 321 771 4480</p>
        <p class="text-sm sm:text-base">Email: contacto@mideliz.com</p>
        <p class="text-sm sm:text-base">Dirección: Cocina oculta, Medellín</p>
      </div>
      <div>
        <h4 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Síguenos</h4>
        <div class="footer-social flex justify-center md:justify-start space-x-4 sm:space-x-6 text-xl sm:text-2xl">
          <a href="#" class="hover:text-red-400 transition"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="hover:text-red-400 transition"><i class="fab fa-twitter"></i></a>
          <a href="#" class="hover:text-red-400 transition"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- GSAP Animaciones -->
  <script>
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

    // Navbar Animation
    window.addEventListener("load", () => {
      gsap.from("nav", {
        y: -100,
        opacity: 0,
        duration: config.duration,
        ease: config.ease
      });
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

    // Header
    gsap.from("header h2", {
      y: isMobile ? -30 : -40,
      opacity: 0,
      duration: config.duration,
      ease: config.ease,
      delay: 0.3
    });

    gsap.from("header p", {
      y: isMobile ? 30 : 40,
      opacity: 0,
      duration: config.duration,
      ease: config.ease,
      delay: 0.5
    });

    // Quiénes Somos
    gsap.from(".about-text", {
      scrollTrigger: {
        trigger: ".about-section",
        start: "top 80%",
        toggleActions: "play none none none"
      },
      x: isMobile ? 0 : -100,
      y: isMobile ? 30 : 0,
      opacity: 0,
      duration: config.duration
    });

    gsap.from(".about-img", {
      scrollTrigger: {
        trigger: ".about-section",
        start: "top 80%",
        toggleActions: "play none none none"
      },
      x: isMobile ? 0 : 100,
      y: isMobile ? 30 : 0,
      opacity: 0,
      duration: config.duration
    });

    // Misión y Visión
    gsap.from(".mv-card", {
      scrollTrigger: {
        trigger: ".mission-vision",
        start: "top 85%",
        toggleActions: "play none none none"
      },
      y: isMobile ? 30 : 50,
      opacity: 0,
      duration: 0.8,
      stagger: 0.2
    });

    // Servicios destacados
    gsap.from(".card", {
      scrollTrigger: {
        trigger: ".services",
        start: "top 80%",
        toggleActions: "play none none none"
      },
      scale: 0.9,
      opacity: 0,
      duration: 0.8,
      stagger: 0.2
    });

    // Footer
    gsap.from(".footer", {
      scrollTrigger: {
        trigger: ".footer",
        start: "top 95%",
        toggleActions: "play none none none"
      },
      y: isMobile ? 50 : 100,
      opacity: 0,
      duration: config.duration
    });

    // Hover animaciones solo en desktop
    if (!isMobile) {
      document.querySelectorAll(".nav-link, .card, .mv-card").forEach(el => {
        el.addEventListener("mouseenter", () => {
          gsap.to(el, { scale: 1.05, duration: 0.2, ease: "power1.out" });
        });
        el.addEventListener("mouseleave", () => {
          gsap.to(el, { scale: 1, duration: 0.2, ease: "power1.in" });
        });
      });
    }
  </script>
</body>
</html>