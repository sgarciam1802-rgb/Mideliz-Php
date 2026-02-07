<?php
/**
 * Dashboard principal del panel de administración
 */

session_start();
require_once '../includes/config.php';

// Verificar sesión
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Nombre del admin
$adminNombre = $_SESSION['admin_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Administración</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-red-50 via-white to-red-100 min-h-screen">

  <!-- NAV -->
  <nav class="bg-red-600 shadow-md">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
      
      <!-- 🧑 Bienvenida -->
      <span class="text-white font-bold text-lg flex items-center">
        <i class="fas fa-user mr-2"></i>
        Bienvenido, <?php echo htmlspecialchars($adminNombre); ?>
      </span>

      <!-- 🚪 Cerrar sesión -->
      <a href="logout.php" class="text-white hover:text-red-200 transition">Cerrar Sesión</a>

    </div>
  </nav>



  <!-- CONTENIDO -->
  <div class="max-w-6xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">
      Bienvenido, <?php echo htmlspecialchars($adminNombre); ?> 
    </h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Carta Usuarios -->
      <div class="bg-white shadow-lg rounded-2xl p-8 text-center hover:shadow-2xl transition">
        <i class="fas fa-users text-red-600 text-5xl mb-4"></i>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Administrar Usuarios</h2>
        <p class="text-gray-600 mb-4">Gestiona los administradores o clientes del sistema.</p>
        <a href="crud_users.php" class="inline-block bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-full transition">
          Ir a Usuarios
        </a>
      </div>

      <!-- Carta Eventos -->
      <div class="bg-white shadow-lg rounded-2xl p-8 text-center hover:shadow-2xl transition">
        <i class="fas fa-calendar-alt text-red-600 text-5xl mb-4"></i>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Administrar Eventos</h2>
        <p class="text-gray-600 mb-4">Visualiza y administra los eventos y reservas.</p>
        <a href="crud_dashboard.php" class="inline-block bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-full transition">
          Ir a Eventos
        </a>
      </div>
    </div>
  </div>

</body>
</html>
