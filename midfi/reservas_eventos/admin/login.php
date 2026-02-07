<?php 
/**
 * Página de login para administradores
 * Sistema de Reservas de Eventos de Comida
 */

session_start();
require_once '../includes/config.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: crud_election.php');
    exit();
}

$mensaje = '';
$tipoMensaje = '';

// Procesar el login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = sanitizar($_POST['usuario']);
    $password = $_POST['password'];
    
    if (empty($usuario) || empty($password)) {
        $mensaje = "Usuario y contraseña son requeridos";
        $tipoMensaje = 'error';
    } else {
        $pdo = conectarDB();
        if ($pdo) {
            try {
                $sql = "SELECT id, usuario, password, nombre FROM administradores WHERE usuario = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario]);
                $admin = $stmt->fetch();
                
                // 🔑 Comparación en texto plano (sin cifrado)
                if ($admin && $password === $admin['password']) {
                    // Login exitoso
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_usuario'] = $admin['usuario'];
                    $_SESSION['admin_nombre'] = $admin['nombre'];
                    
                    header('Location: crud_election.php');
                    exit();
                } else {
                    $mensaje = "Usuario o contraseña incorrectos";
                    $tipoMensaje = 'error';
                }
            } catch (PDOException $e) {
                $mensaje = "Error de conexión";
                $tipoMensaje = 'error';
                error_log("Error en login: " . $e->getMessage());
            }
        } else {
            $mensaje = "Error de conexión a la base de datos";
            $tipoMensaje = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - <?php echo SITE_NAME; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-red-50 min-h-screen flex items-center justify-center font-sans bg-gradient-to-br from-red-50 via-white to-red-100">

  <div class="w-full max-w-md px-4">
    <!-- Enlace para volver -->
    <div class="text-center mb-6">
      <a href="../index.php" class="text-red-600 hover:text-red-800 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Volver al sitio principal
      </a>
    </div>

    <!-- Card -->
    <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl overflow-hidden">
      <!-- Header -->
      <div class="bg-red-600 text-white text-center py-8 px-6">
        <i class="fas fa-user-shield text-5xl mb-4"></i>
        <h3 class="text-2xl font-bold">Panel de Administración</h3>
        <p class="mt-2 text-sm opacity-90">Ingresa tus credenciales</p>
      </div>

      <!-- Body -->
      <div class="p-6">
        <?php if ($mensaje): ?>
          <?php mostrarMensaje($mensaje, $tipoMensaje); ?>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
          <!-- Usuario -->
          <div>
            <label for="usuario" class="block text-gray-700 font-semibold mb-2">
              <i class="fas fa-user mr-2"></i>Usuario
            </label>
            <input type="text" id="usuario" name="usuario" 
                   placeholder="Ingresa tu usuario" required
                   class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-red-500 transition">
          </div>

          <!-- Contraseña -->
          <div>
            <label for="password" class="block text-gray-700 font-semibold mb-2">
              <i class="fas fa-lock mr-2"></i>Contraseña
            </label>
            <input type="password" id="password" name="password" 
                   placeholder="Ingresa tu contraseña" required
                   class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-red-500 transition">
          </div>

          <!-- Botón -->
          <div>
            <button type="submit" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-full shadow-lg transition transform hover:-translate-y-1">
              <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
