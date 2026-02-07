<?php
// Configuración de la página
$title = "Menú - Restaurante Mideliz";
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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

    /* === Efecto Flip === */
    .flip-card {
      perspective: 1000px;
    }
    .flip-inner {
      position: relative;
      width: 100%;
      height: 100%;
      transition: transform 0.8s;
      transform-style: preserve-3d;
    }
    .flip-card:hover .flip-inner {
      transform: rotateY(180deg);
    }
    .flip-front, .flip-back {
      position: absolute;
      width: 100%;
      height: 100%;
      backface-visibility: hidden;
      border-radius: 0.75rem;
      overflow: hidden;
    }
    .flip-back {
      transform: rotateY(180deg);
    }

    /* Deshabilitar flip en móviles */
    @media (max-width: 768px) {
      .flip-card:hover .flip-inner {
        transform: none;
      }
    }
  </style>
</head>

<body class="flex flex-col min-h-screen font-poppins bg-gray-50 text-gray-800"> 

  <!-- Navigation -->
  <nav class="fixed w-full bg-white bg-opacity-90 backdrop-blur-md shadow-md z-50">
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

  <!-- Main -->
  <main class="flex-1 flex flex-col justify-center items-center pt-20 sm:pt-24 pb-8 sm:pb-12 px-4 sm:px-6">
    <header class="text-center w-full max-w-7xl">
      <h2 class="header-title text-3xl sm:text-4xl md:text-5xl font-bold mb-4 sm:mb-6 text-red-600">
        Explora Nuestro Menú
      </h2>
      <p class="header-sub text-base sm:text-lg md:text-xl text-gray-600 mb-8 sm:mb-12 px-4">
        Tres categorías únicas para que disfrutes en cualquier ocasión
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 lg:gap-10 w-full">

        <!-- Típicos -->
        <div class="flip-card h-72 sm:h-80 md:h-96 w-full max-w-sm mx-auto">
          <div class="flip-inner">
            <div class="flip-front shadow-lg hover:shadow-2xl transition-shadow duration-300 cursor-pointer">
              <img src="https://conocedores.com/wp-content/uploads/2020/12/cocina-colombia-gastronomia-07122020.jpg" 
                   class="absolute inset-0 w-full h-full object-cover" 
                   alt="Platos Típicos" />
              <div class="absolute inset-0 bg-black/40"></div>
              <div class="relative z-10 flex flex-col items-center justify-center text-center text-white h-full p-6 sm:p-8">
                <div class="text-4xl sm:text-5xl mb-3 sm:mb-4"><i class="fas fa-drumstick-bite"></i></div>
                <h2 class="text-2xl sm:text-3xl font-semibold mb-2">Típicos</h2>
                <p class="text-sm sm:text-base opacity-90 md:hidden">Sabores auténticos de nuestra tierra</p>
              </div>
            </div>

            <div class="flip-back bg-red-700 flex flex-col justify-center items-center text-white p-6 text-center">
              <h3 class="text-xl sm:text-2xl font-semibold mb-3">Sabores Auténticos</h3>
              <p class="mb-4 sm:mb-6 text-sm sm:text-base">
                Descubre los platos tradicionales que hacen de nuestra cocina un tesoro cultural.
              </p>
              <button class="bg-white text-red-700 font-semibold px-5 sm:px-6 py-2 sm:py-2.5 rounded-full hover:bg-gray-200 transition text-sm sm:text-base">
                Ver más
              </button>
            </div>
          </div>
        </div>

        <!-- Gourmet -->
        <div class="flip-card h-72 sm:h-80 md:h-96 w-full max-w-sm mx-auto">
          <div class="flip-inner">
            <div class="flip-front shadow-lg hover:shadow-2xl transition-shadow duration-300 cursor-pointer">
              <img src="https://img.freepik.com/premium-photo/photo-lunch-restaurant-hotel-photography-ai-generated_925376-7341.jpg" 
                   class="absolute inset-0 w-full h-full object-cover" 
                   alt="Platos Gourmet" />
              <div class="absolute inset-0 bg-black/40"></div>
              <div class="relative z-10 flex flex-col items-center justify-center text-center text-white h-full p-6 sm:p-8">
                <div class="text-4xl sm:text-5xl mb-3 sm:mb-4"><i class="fas fa-crown"></i></div>
                <h2 class="text-2xl sm:text-3xl font-semibold mb-2">Gourmet</h2>
                <p class="text-sm sm:text-base opacity-90 md:hidden">Elegancia en cada bocado</p>
              </div>
            </div>

            <div class="flip-back bg-red-700 flex flex-col justify-center items-center text-white p-6 text-center">
              <h3 class="text-xl sm:text-2xl font-semibold mb-3">Elegancia Culinaria</h3>
              <p class="mb-4 sm:mb-6 text-sm sm:text-base">
                Experiencias gastronómicas sofisticadas con ingredientes premium y presentación impecable.
              </p>
              <button class="bg-white text-red-700 font-semibold px-5 sm:px-6 py-2 sm:py-2.5 rounded-full hover:bg-gray-200 transition text-sm sm:text-base">
                Ver más
              </button>
            </div>
          </div>
        </div>

        <!-- Snacks -->
        <div class="flip-card h-72 sm:h-80 md:h-96 w-full max-w-sm mx-auto md:col-span-2 lg:col-span-1">
          <div class="flip-inner">
            <div class="flip-front shadow-lg hover:shadow-2xl transition-shadow duration-300 cursor-pointer">
              <img src="https://th.bing.com/th/id/R.62dc7a5af97deab68a304553eef715fe?rik=OFdFcQ9lNvk2gg&riu=http%3a%2f%2fcecinasllanquihue.cl%2fblog%2fwp-content%2fuploads%2f2020%2f05%2ffood-3137152_1920.jpg&ehk=SNQNZJ8Yq3GRCHf%2bwkzqTVcDcefalzBorpLMf%2bd6wAM%3d&risl=&pid=ImgRaw&r=0" 
                   class="absolute inset-0 w-full h-full object-cover" 
                   alt="Snacks" />
              <div class="absolute inset-0 bg-black/40"></div>
              <div class="relative z-10 flex flex-col items-center justify-center text-center text-white h-full p-6 sm:p-8">
                <div class="text-4xl sm:text-5xl mb-3 sm:mb-4"><i class="fas fa-cookie-bite"></i></div>
                <h2 class="text-2xl sm:text-3xl font-semibold mb-2">Snacks</h2>
                <p class="text-sm sm:text-base opacity-90 md:hidden">Momentos deliciosos</p>
              </div>
            </div>

            <div class="flip-back bg-red-700 flex flex-col justify-center items-center text-white p-6 text-center">
              <h3 class="text-xl sm:text-2xl font-semibold mb-3">Bocados Perfectos</h3>
              <p class="mb-4 sm:mb-6 text-sm sm:text-base">
                Opciones ligeras y deliciosas para disfrutar en cualquier momento del día.
              </p>
              <button class="bg-white text-red-700 font-semibold px-5 sm:px-6 py-2 sm:py-2.5 rounded-full hover:bg-gray-200 transition text-sm sm:text-base">
                Ver más
              </button>
            </div>
          </div>
        </div>

      </div>
    </header>
  </main>

  <!-- Footer -->
  <footer class="site-footer bg-red-700 text-white py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 text-center md:text-left">
      <div>
        <h4 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4"><?php echo $site_name; ?></h4>
        <p class="text-sm sm:text-base">© <?php echo date("Y"); ?> <?php echo $site_name; ?>. Todos los derechos reservados.</p>
      </div>
      <div>
        <h4 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Contacto</h4>
        <p class="text-sm sm:text-base">Teléfono: +57 321 771 4480</p>
        <p class="text-sm sm:text-base">Email: contacto@mideliz.com</p>
        <p class="text-sm sm:text-base">Dirección: Cocina oculta, Medellín</p>
      </div>
      <div>
        <h4 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Síguenos</h4>
        <div class="flex justify-center md:justify-start space-x-4 sm:space-x-6 text-xl sm:text-2xl">
          <a href="#" class="hover:text-red-400 transition"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="hover:text-red-400 transition"><i class="fab fa-twitter"></i></a>
          <a href="#" class="hover:text-red-400 transition"><i class="fab fa-instagram"></i></a>
        </div>
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

      // Navbar Animation
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

      // Animaciones de entrada
      gsap.from([".header-title", ".header-sub"], {
        y: isMobile ? 30 : 40, 
        opacity: 0, 
        duration: config.duration, 
        ease: config.ease, 
        stagger: 0.2, 
        delay: 0.5
      });

      gsap.from(".flip-card", {
        y: isMobile ? 40 : 60, 
        opacity: 0, 
        duration: config.duration, 
        stagger: 0.2, 
        ease: config.ease, 
        delay: 1
      });

      gsap.from(".site-footer", {
        y: isMobile ? 50 : 100, 
        opacity: 0, 
        duration: config.duration, 
        ease: config.ease, 
        delay: 1.5
      });
    });
  </script>
</body>
</html>