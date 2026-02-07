<?php
session_start();
require_once 'includes/config.php';

// --- Verificar sesión ---
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$pdo = conectarDB();

// --- CSRF token ---
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$mensaje = '';
$tipoMensaje = '';

// --- Obtener datos actuales ---
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// --- Procesar formulario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $mensaje = 'Token de seguridad inválido.';
        $tipoMensaje = 'red';
    } else {
        $nombre = sanitizar($_POST['nombre']);
        $apellido = sanitizar($_POST['apellido']);
        $telefono = sanitizar($_POST['telefono']);
        $pass_actual = $_POST['password_actual'] ?? '';
        $pass_nueva = $_POST['password_nueva'] ?? '';
        $pass_confirm = $_POST['password_confirm'] ?? '';

        if ($nombre === '' || $apellido === '') {
            $mensaje = 'Nombre y apellido son obligatorios.';
            $tipoMensaje = 'red';
        } else {
            // ✅ Actualización sin campo "ciudad"
            $update = $pdo->prepare("
                UPDATE usuarios 
                SET nombre = :nombre, apellido = :apellido, telefono = :telefono
                WHERE id = :id
            ");
            $update->execute([
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':telefono' => $telefono,
                ':id' => $usuario_id
            ]);

            // --- Cambio de contraseña ---
            if ($pass_actual || $pass_nueva || $pass_confirm) {
                if (!password_verify($pass_actual, $usuario['password'])) {
                    $mensaje = 'La contraseña actual no es correcta.';
                    $tipoMensaje = 'red';
                } elseif ($pass_nueva !== $pass_confirm) {
                    $mensaje = 'Las contraseñas nuevas no coinciden.';
                    $tipoMensaje = 'red';
                } elseif (strlen($pass_nueva) < 8) {
                    $mensaje = 'La nueva contraseña debe tener al menos 8 caracteres.';
                    $tipoMensaje = 'red';
                } else {
                    $new_hash = password_hash($pass_nueva, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE usuarios SET password = :p WHERE id = :id")
                        ->execute([':p' => $new_hash, ':id' => $usuario_id]);
                    $mensaje = 'Contraseña actualizada correctamente.';
                    $tipoMensaje = 'green';
                }
            } else {
                if (!$mensaje) {
                    $mensaje = 'Perfil actualizado correctamente.';
                    $tipoMensaje = 'green';
                }
            }

            // --- Actualizar datos del usuario en memoria ---
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $usuario_id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mi Perfil</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
  <link rel="stylesheet" href="../styles.css">
</head>

<body class="bg-gradient-to-br from-red-50 via-white to-red-100 font-[Poppins] min-h-screen flex flex-col overflow-y-auto">

  <!-- NAVBAR -->
  <nav class="fixed top-0 w-full bg-white bg-opacity-80 backdrop-blur-md shadow-md z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <a href="index.php" class="text-2xl font-bold text-red-600 hover:text-red-700 transition">Mideliz</a>
      <ul class="flex space-x-8 text-lg items-center">
        <li><a href="index.php" class="nav-link">Inicio</a></li>
        <li><a href="menu.php" class="nav-link">Menú</a></li>
        <li><a href="about_us.php" class="nav-link">Sobre Nosotros</a></li>
        <li><a href="contacto.php" class="nav-link">Contacto</a></li>
        <li class="relative">
          <button id="userMenuButton" class="focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke-width="1.5" stroke="currentColor"
              class="w-8 h-8 text-gray-700 hover:text-red-600 transition">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 
                2.975m11.963 0a9 9 0 1 0-11.963 
                0m11.963 0A8.966 8.966 0 0 1 12 
                21a8.966 8.966 0 0 1-5.982-2.275M15 
                9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
          </button>

          <div id="userMenu"
              class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50">
            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Editar perfil</a>
            <a href="logout_user.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Cerrar sesión</a>
          </div>
        </li>
      </ul>
    </div>
  </nav>

  <!-- CONTENEDOR GENERAL -->
  <main class="flex-grow flex items-start justify-center mt-[110px] mb-16 mx-10">
    <div class="w-full max-w-7xl grid grid-cols-1 md:grid-cols-3 gap-6 pb-5">

      <!-- CARD IZQUIERDA -->
      <div class="card-left md:col-span-1 bg-white/90 backdrop-blur-md rounded-2xl shadow-lg p-6 flex flex-col items-center transition hover:shadow-xl hover:-translate-y-1 duration-300">
        <div class="w-24 h-24 bg-gradient-to-br from-red-600 to-red-600 rounded-full flex items-center justify-center mb-4 shadow-inner">
          <i class="fas fa-user text-white text-5xl"></i>
        </div>

        <div class="w-full text-left space-y-3 text-gray-700 text-sm">
          <div class="bg-gray-50 rounded-lg p-3 shadow-sm">
            <p><strong class="text-red-600">Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
          </div>
          <div class="bg-gray-50 rounded-lg p-3 shadow-sm">
            <p><strong class="text-red-600">Apellido:</strong> <?php echo htmlspecialchars($usuario['apellido']); ?></p>
          </div>
          <div class="bg-gray-50 rounded-lg p-3 shadow-sm">
            <p><strong class="text-red-600">Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono']); ?></p>
          </div>
          <div class="bg-gray-50 rounded-lg p-3 shadow-sm">
            <p><strong class="text-red-600">Correo:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
          </div>
        </div>
      </div>

      <!-- CARD DERECHA -->
      <div class="card-right md:col-span-2 bg-white/90 backdrop-blur-md rounded-2xl shadow-lg p-8 transition hover:shadow-xl hover:-translate-y-1 duration-300">
        <h1 class="text-2xl font-semibold mb-4 text-red-600">Mi Perfil</h1>

        <?php if ($mensaje): ?>
          <div class="mb-4 p-3 rounded-md text-white bg-<?php echo $tipoMensaje; ?>-600">
            <?php echo $mensaje; ?>
          </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium mb-1">Nombre</label>
              <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required class="w-full border border-gray-200 rounded-lg p-2 transition outline-none focus:border-red-600">
            </div>

            <div>
              <label class="block font-medium mb-1">Apellido</label>
              <input type="text" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required class="w-full border border-gray-200 rounded-lg p-2 transition outline-none focus:border-red-600">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium mb-1">Teléfono</label>
              <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" class="w-full border border-gray-200 rounded-lg p-2 transition outline-none focus:border-red-600">
            </div>

            <div>
              <label class="block font-medium mb-1">Ciudad</label>
              <input type="text" name="ciudad" value="Medellín" disabled class="w-full border border-gray-200 bg-gray-200 text-gray-500 rounded-lg p-2">
            </div>
          </div>

          <div>
            <label class="block font-medium mb-1">Correo electrónico</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled class="w-full border border-gray-200 bg-gray-200 text-gray-500 rounded-lg p-2">
          </div>

          <hr class="my-6 border-gray-200">

          <h2 class="text-lg font-semibold text-red-600 mb-2">Cambiar Contraseña</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block font-medium mb-1">Contraseña actual</label>
              <input type="password" name="password_actual" class="w-full border border-gray-200 rounded-lg p-2 transition outline-none focus:border-red-600">
            </div>

            <div>
              <label class="block font-medium mb-1">Nueva contraseña</label>
              <input type="password" name="password_nueva" class="w-full border border-gray-200 rounded-lg p-2 transition outline-none focus:border-red-600">
            </div>

            <div>
              <label class="block font-medium mb-1">Confirmar nueva</label>
              <input type="password" name="password_confirm" class="w-full border border-gray-200 rounded-lg p-2 transition outline-none focus:border-red-600">
            </div>
          </div>

          <div class="flex justify-between mt-6">
            <a href="index.php" class="text-red-600 hover:text-red-700 transition">Volver al Inicio</a>
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-md transition">
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

<!-- JS MENU Y GSAP -->
<script>
  const userMenuButton = document.getElementById('userMenuButton');
  const userMenu = document.getElementById('userMenu');

  // === MENÚ USUARIO ===
  userMenuButton.addEventListener('click', () => userMenu.classList.toggle('hidden'));
  window.addEventListener('click', (e) => {
    if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
      userMenu.classList.add('hidden');
    }
  });

</script>

</body>
</html>


