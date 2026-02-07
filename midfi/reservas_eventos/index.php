<?php
// index.php

// Configuración básica
$title = "Restaurante Mideliz";
$site_name = "Mideliz";

// Manejo de sesión
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
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
  <nav class="fixed w-full bg-white bg-opacity-90 backdrop-blur-md shadow-md z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
      <a href="index.php" class="text-xl sm:text-2xl font-bold text-red-600 hover:text-red-700 transition">
        <?php echo $site_name; ?>
      </a>

      <!-- Menú Desktop -->
      <ul class="hidden lg:flex items-center space-x-6 xl:space-x-8 text-base xl:text-lg">
        <li><a href="#inicio" class="nav-link hover:text-red-600 transition">Inicio</a></li>
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
        <li><a href="#inicio" class="block py-2 text-gray-700 hover:text-red-600 transition mobile-link">Inicio</a></li>
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

  <!-- Hero Section -->
  <header id="inicio" class="relative h-screen w-full overflow-hidden">
    <img
      src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1920&q=80"
      alt="Delicious food"
      class="absolute inset-0 w-full h-full object-cover opacity-90"/>
    <div class="absolute inset-0 bg-black bg-opacity-40 flex flex-col justify-center items-center text-center px-4">
      <h1 class="hero-title text-white text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold mb-4 sm:mb-6 drop-shadow-lg">
        Bienvenidos a <?php echo $site_name; ?>
      </h1>
      <p class="hero-subtitle text-white text-lg sm:text-xl md:text-2xl mb-6 sm:mb-8">
        Sabores auténticos que enamoran
      </p>
      <a href="menu.php" class="hero-btn bg-red-500 text-white px-6 sm:px-8 py-2.5 sm:py-3 rounded-full font-semibold shadow-lg hover:bg-red-700 text-sm sm:text-base">
        Ver Menú <i class="fas fa-utensils ml-2"></i>
      </a>
    </div>
  </header>

  <!-- Menu Highlights Section -->
  <section id="menu" class="w-full py-12 sm:py-16 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <h2 class="menu-title text-2xl sm:text-3xl md:text-4xl font-bold mb-8 sm:mb-12 text-red-600 text-center">
        Nuestro Menú Destacado
      </h2>

      <div class="relative flex items-center">
        <!-- Botones del carrusel - Ocultos en mobile, visibles en tablet+ -->
        <button onclick="moveSlide(-1)" 
          class="hidden md:block absolute -left-4 lg:-left-16 top-1/2 -translate-y-1/2 z-20 bg-red-600 text-white px-3 py-3 lg:px-5 lg:py-4 rounded-full hover:bg-red-700 transition shadow-lg">
          ‹
        </button>

        <div class="overflow-hidden w-full">
          <div id="carousel" class="flex gap-3 sm:gap-4 transition-transform duration-500">
            <!-- Tarjetas menú -->
            <?php
            $platos = [
              ["Paella Valenciana", "Un clásico español con mariscos frescos y arroz aromático.", "https://th.bing.com/th/id/R.275b3b529a87aaee743e1b6915af3ba9?rik=gWTvHIvV%2blUmxw&pid=ImgRaw&r=0"],
              ["Tacos al Pastor", "Midelizs tacos con carne marinada y piña fresca.", "https://www.seriouseats.com/thmb/EAwyskovb4VA2HjXLc4xald4cZ8=/1500x1125/filters:fill(auto,1)/20210712-tacos-al-pastor-melissa-hom-seriouseats-37-f72cdd02c9574bceb1eef1c8a23b76ed.jpg"],
              ["Ceviche Peruano", "Fresco y cítrico, preparado con pescado del día y limón.", "https://lanoticia.com.pe/wp-content/uploads/2021/06/14.1-ceviche.jpg"],
              ["Pizza Napolitana", "Masa artesanal, salsa fresca y mozzarella.", "https://tse3.mm.bing.net/th/id/OIP.GDnjUtX7q-62HalbepVHlAHaE8?rs=1&pid=ImgDetMain&o=7&rm=3"]
            ];

            foreach ($platos as $plato): ?>
              <article class="menu-card bg-white rounded-lg overflow-hidden cursor-pointer flex-shrink-0 shadow-md hover:shadow-xl transition-shadow"
                data-card>
                <img src="<?php echo $plato[2]; ?>" class="w-full h-40 sm:h-48 object-cover" alt="<?php echo $plato[0]; ?>" />
                <div class="p-3 sm:p-4">
                  <h3 class="text-lg sm:text-xl font-semibold mb-1"><?php echo $plato[0]; ?></h3>
                  <p class="text-gray-600 text-xs sm:text-sm"><?php echo $plato[1]; ?></p>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>

        <button onclick="moveSlide(1)" 
          class="hidden md:block absolute -right-4 lg:-right-16 top-1/2 -translate-y-1/2 z-20 bg-red-600 text-white px-3 py-3 lg:px-5 lg:py-4 rounded-full hover:bg-red-700 transition shadow-lg">
          ›
        </button>
      </div>

      <!-- Indicadores para mobile -->
      <div class="flex justify-center mt-6 space-x-2 md:hidden" id="carouselDots"></div>
    </div>
  </section>

  <!-- About Section -->
  <section id="sobre-nosotros" class="bg-red-50 py-12 sm:py-16 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto text-center">
      <h2 class="about-title text-2xl sm:text-3xl md:text-4xl font-bold text-red-600 mb-8 sm:mb-12 text-center">Sobre Nosotros</h2>
      <div class="about-items grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 lg:gap-10">
        <?php
        $about = [
          ["Nuestra Pasión", "fa-utensils", "En Mideliz nos apasiona acompañar tus momentos especiales con experiencias gastronómicas auténticas y memorables. Cada plato es preparado con dedicación, amor y el compromiso de hacer de tu celebración un recuerdo inolvidable."],
          ["Ingredientes Frescos", "fa-leaf", "La calidad no se negocia. Seleccionamos cuidadosamente los mejores ingredientes frescos y locales para garantizar el mejor sabor en cada plato."],
          ["Nuestro Equipo", "fa-users", "Contamos con un equipo humano cálido y atento, liderado por Aleida, chef profesional apasionada por la gastronomía y el servicio. Su talento y creatividad nos inspiran a demostrar que la comida de calidad está al alcance de todos, siempre con un toque único y cercano."]
        ];

        foreach ($about as $a): ?>
          <div class="about-card bg-white rounded-lg shadow-lg p-6 sm:p-8 hover:shadow-2xl transition-shadow duration-300 cursor-default">
            <div class="text-red-500 text-4xl sm:text-5xl mb-3 sm:mb-4"><i class="fas <?php echo $a[1]; ?>"></i></div>
            <h3 class="text-xl sm:text-2xl font-semibold mb-2 sm:mb-3"><?php echo $a[0]; ?></h3>
            <p class="text-gray-700 text-sm sm:text-base leading-relaxed"><?php echo $a[2]; ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-red-700 text-white py-8 sm:py-12">
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

    // Navbar
    gsap.from("nav", {
      y: -100,
      opacity: 0,
      duration: config.duration,
      ease: config.ease
    });

    // === MENÚ MOBILE MEJORADO ===
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

    // Hero animations
    gsap.from("header img", {
      scale: 1.2,
      opacity: 0,
      duration: isMobile ? 1 : 1.5,
      ease: "power2.out"
    });

    gsap.from(".hero-title", { 
      opacity: 0, 
      y: isMobile ? 30 : 50, 
      duration: config.duration, 
      delay: 0.3, 
      ease: config.ease 
    });
    
    gsap.from(".hero-subtitle", { 
      opacity: 0, 
      y: isMobile ? 30 : 50, 
      duration: config.duration, 
      delay: 0.5, 
      ease: config.ease 
    });
    
    gsap.from(".hero-btn", { 
      opacity: 0, 
      scale: 0.8, 
      duration: config.duration, 
      delay: 0.7, 
      ease: "back.out(1.7)" 
    });

    // === CARRUSEL RESPONSIVO CORREGIDO ===
    const carousel = document.getElementById("carousel");
    const dotsContainer = document.getElementById("carouselDots");
    
    function getVisibleSlides() {
      const width = window.innerWidth;
      if (width < 768) return 1;
      return 2;
    }

    let visible = getVisibleSlides();
    let index = visible;

    if (carousel) {
      const slides = Array.from(carousel.children).filter(el => el.hasAttribute('data-card'));
      const totalSlides = slides.length;

      function updateCardWidth() {
        visible = getVisibleSlides();
        const cards = carousel.querySelectorAll('[data-card]');
        const isMobileView = window.innerWidth < 768;
        
        cards.forEach(card => {
          if (isMobileView) {
            card.style.width = '100%';
            card.style.minWidth = '100%';
            card.style.maxWidth = '100%';
          } else {
            card.style.width = 'calc(50% - 0.5rem)';
            card.style.minWidth = 'calc(50% - 0.5rem)';
            card.style.maxWidth = 'calc(50% - 0.5rem)';
          }
        });
      }

      updateCardWidth();

      // Clonamos para efecto infinito
      slides.slice(-visible).forEach(slide => {
        const clone = slide.cloneNode(true);
        carousel.insertBefore(clone, carousel.firstChild);
      });
      
      slides.slice(0, visible).forEach(slide => {
        const clone = slide.cloneNode(true);
        carousel.appendChild(clone);
      });

      const allSlides = carousel.children;

      function createDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        for (let i = 0; i < totalSlides; i++) {
          const dot = document.createElement('button');
          dot.className = 'w-2 h-2 rounded-full bg-gray-400 transition-all';
          if (i === 0) dot.classList.add('bg-red-600', 'w-6');
          dot.addEventListener('click', () => goToSlide(i));
          dotsContainer.appendChild(dot);
        }
      }

      createDots();

      function updateDots() {
        if (!dotsContainer || window.innerWidth >= 768) return;
        const dots = dotsContainer.children;
        const currentIndex = ((index - visible) % totalSlides + totalSlides) % totalSlides;
        
        Array.from(dots).forEach((dot, i) => {
          if (i === currentIndex) {
            dot.classList.add('bg-red-600', 'w-6');
            dot.classList.remove('bg-gray-400');
          } else {
            dot.classList.remove('bg-red-600', 'w-6');
            dot.classList.add('bg-gray-400');
          }
        });
      }

      function getSlideWidth() {
        const firstSlide = allSlides[0];
        const gap = window.innerWidth < 640 ? 12 : 16;
        const computedStyle = window.getComputedStyle(firstSlide);
        const width = firstSlide.offsetWidth;
        return width + gap;
      }

      function goToSlide(slideIndex) {
        index = slideIndex + visible;
        const slideWidth = getSlideWidth();
        carousel.style.transition = "transform 0.5s ease";
        carousel.style.transform = `translateX(-${index * slideWidth}px)`;
        updateDots();
      }

      carousel.style.transform = `translateX(-${index * getSlideWidth()}px)`;

      window.moveSlide = (step) => {
        index += step;
        const slideWidth = getSlideWidth();
        carousel.style.transition = "transform 0.5s ease";
        carousel.style.transform = `translateX(-${index * slideWidth}px)`;

        carousel.addEventListener(
          "transitionend",
          () => {
            if (index >= allSlides.length - visible) {
              carousel.style.transition = "none";
              index = visible;
              carousel.style.transform = `translateX(-${index * slideWidth}px)`;
            }
            if (index < visible) {
              carousel.style.transition = "none";
              index = allSlides.length - visible - 1;
              carousel.style.transform = `translateX(-${index * slideWidth}px)`;
            }
            updateDots();
          },
          { once: true }
        );
      };

      // Swipe para mobile
      let touchStartX = 0;
      let touchEndX = 0;

      carousel.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
      }, { passive: true });

      carousel.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
      }, { passive: true });

      function handleSwipe() {
        const swipeThreshold = 50;
        if (touchStartX - touchEndX > swipeThreshold) {
          window.moveSlide(1);
        }
        if (touchEndX - touchStartX > swipeThreshold) {
          window.moveSlide(-1);
        }
      }

      // Redimensionar ventana
      let resizeTimeout;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
          const oldVisible = visible;
          updateCardWidth();
          const newVisible = getVisibleSlides();
          
          if (newVisible !== oldVisible) {
            location.reload();
          } else {
            const slideWidth = getSlideWidth();
            carousel.style.transition = "none";
            carousel.style.transform = `translateX(-${index * slideWidth}px)`;
          }
        }, 250);
      });

      // Animación de entrada de las cards
      gsap.fromTo(
        "#carousel .menu-card",
        { opacity: 0, y: isMobile ? 30 : 50 },
        {
          opacity: 1,
          y: 0,
          stagger: 0.15,
          duration: 0.6,
          ease: "power2.out",
          scrollTrigger: {
            trigger: "#carousel",
            start: "top 85%",
            end: "bottom 15%",
            toggleActions: "play none none none"
          }
        }
      );
    }

    // === SOBRE NOSOTROS ===
    gsap.from(".about-card", {
      x: isMobile ? 0 : 200,
      y: isMobile ? 50 : 0,
      opacity: 0,
      duration: config.duration,
      stagger: 0.2,
      ease: config.ease,
      scrollTrigger: {
        trigger: ".about-items",
        start: "top 80%",
        toggleActions: "play none none none"
      }
    });

    // Hover effect solo en desktop
    if (!isMobile) {
      document.querySelectorAll(".about-card").forEach(card => {
        card.addEventListener("mouseenter", () => {
          gsap.to(card, { scale: 1.05, duration: 0.4, ease: "elastic.out(1, 0.5)" });
        });
        card.addEventListener("mouseleave", () => {
          gsap.to(card, { scale: 1, duration: 0.3 });
        });
      });
    }

    // Footer
    gsap.from("footer", {
      y: isMobile ? 50 : 100,
      opacity: 0,
      duration: config.duration,
      ease: config.ease,
      scrollTrigger: {
        trigger: "footer",
        start: "top 90%",
        toggleActions: "play none none none"
      }
    });
  });
</script>
</body>
</html>