<?php
require_once '../includes/config.php';

// Solo acceso admin
ensure_admin();

// Obtener el nombre del administrador desde la sesión
$adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$message = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf($_POST['csrf'] ?? '')) {
        die('Token CSRF inválido.');
    }

    // Crear usuario
    if ($_POST['do'] === 'create') {
        $nombre   = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $ciudad   = trim($_POST['ciudad']);
        $telefono = trim($_POST['telefono']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        if ($nombre && $apellido && $email && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, ciudad, telefono, email, password) VALUES (?, ?, ?, ?, ?, ?)");
            try {
                $stmt->execute([$nombre, $apellido, $ciudad, $telefono, $email, $hash]);
                $message = "✅ Usuario creado correctamente.";
                $action = 'list';
            } catch (PDOException $e) {
                $message = "⚠️ Error al crear usuario: " . e($e->getMessage());
            }
        } else {
            $message = "⚠️ Todos los campos obligatorios deben completarse.";
        }
    }

    // Actualizar usuario
    if ($_POST['do'] === 'update' && !empty($_POST['id'])) {
        $uid = (int)$_POST['id'];
        $nombre   = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $ciudad   = trim($_POST['ciudad']);
        $telefono = trim($_POST['telefono']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        if ($nombre && $apellido && $email) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, ciudad=?, telefono=?, email=?, password=? WHERE id=?");
                $stmt->execute([$nombre, $apellido, $ciudad, $telefono, $email, $hash, $uid]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, ciudad=?, telefono=?, email=? WHERE id=?");
                $stmt->execute([$nombre, $apellido, $ciudad, $telefono, $email, $uid]);
            }
            $message = "✅ Usuario actualizado correctamente.";
            $action = 'list';
        } else {
            $message = "⚠️ Campos obligatorios faltantes.";
        }
    }

    // Eliminar usuario
    if ($_POST['do'] === 'delete' && !empty($_POST['id'])) {
        $uid = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$uid]);
        $message = "🗑️ Usuario eliminado.";
        $action = 'list';
    }
}

// Datos si se edita
$editingUser = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id=?");
    $stmt->execute([$id]);
    $editingUser = $stmt->fetch();
    if (!$editingUser) {
        $message = "Usuario no encontrado.";
        $action = 'list';
    }
}

// Listar usuarios
$usuarios = [];
if ($action === 'list') {
    $usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Usuarios</title>

  <!-- Tailwind y FontAwesome -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/a2e0e6c6c8.js" crossorigin="anonymous"></script>

  <!-- Fuente Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-red-50 via-white to-red-100 text-gray-800">

  <!-- NAV BAR -->
  <nav class="bg-red-600 shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">
      <div class="flex items-center text-white font-semibold text-lg">
        <i class="fas fa-user mr-2"></i>
        Bienvenido, <?= htmlspecialchars($adminNombre) ?>
      </div>
      <div class="flex space-x-6 text-sm">
        <a href="crud_election.php" class="text-white hover:text-red-200 transition">Inicio</a>
        <a href="crud_dashboard.php" class="text-white hover:text-red-200 <?= basename($_SERVER['PHP_SELF']) == 'crud_dashboard.php' ? 'underline' : '' ?>">Eventos</a>
        <a href="logout.php" class="text-white hover:text-red-200 transition">Cerrar Sesión</a>
      </div>
    </div>
  </nav>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="max-w-6xl mx-auto p-8">

    <!-- Encabezado -->
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold text-red-600 flex items-center gap-2">
        <i class="fas fa-users"></i> Administrar Usuarios
      </h1>
      <div class="space-x-3">
        <a href="?action=list" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition">
          <i class="fas fa-list"></i> Lista
        </a>
        <a href="?action=create" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition shadow-md">
          <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
      </div>
    </div>

    <!-- Mensaje -->
    <?php if ($message): ?>
      <div class="p-3 mb-6 rounded-lg text-sm font-medium shadow-md 
        <?= str_contains($message, '✅') 
          ? 'bg-green-100 text-green-700 border border-green-300' 
          : 'bg-yellow-100 text-yellow-800 border border-yellow-300' ?>">
        <?= e($message) ?>
      </div>
    <?php endif; ?>

    <!-- LISTA DE USUARIOS -->
    <?php if ($action === 'list'): ?>
      <div class="overflow-x-auto bg-white/90 backdrop-blur-sm border border-red-100 shadow-lg rounded-2xl transition hover:shadow-xl duration-300">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-red-600 text-white uppercase text-xs tracking-wider">
            <tr>
              <th class="px-4 py-3 text-left">ID</th>
              <th class="px-4 py-3 text-left">Nombre</th>
              <th class="px-4 py-3 text-left">Apellido</th>
              <th class="px-4 py-3 text-left">Ciudad</th>
              <th class="px-4 py-3 text-left">Teléfono</th>
              <th class="px-4 py-3 text-left">Correo</th>
              <th class="px-4 py-3 text-left">Creado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($usuarios as $u): ?>
              <tr class="hover:bg-red-50 transition">
                <td class="px-4 py-3"><?= e($u['id']) ?></td>
                <td class="px-4 py-3 font-medium text-gray-800"><?= e($u['nombre']) ?></td>
                <td class="px-4 py-3"><?= e($u['apellido']) ?></td>
                <td class="px-4 py-3"><?= e($u['ciudad']) ?></td>
                <td class="px-4 py-3"><?= e($u['telefono']) ?></td>
                <td class="px-4 py-3"><?= e($u['email']) ?></td>
                <td class="px-4 py-3 text-xs text-gray-500"><?= e($u['creado_en']) ?></td>

                <!-- BOTONES EDITAR Y ELIMINAR -->
                <td class="px-4 py-2 text-center">
                  <div class="flex justify-center gap-2">
                    <!-- Botón Editar -->
                    <a href="?action=edit&id=<?= e($u['id']) ?>"
                      class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition">
                      <i class="fas fa-edit"></i> Editar
                    </a>

                    <!-- Botón Eliminar -->
                    <form class="inline" method="post" onsubmit="return confirm('¿Eliminar este usuario?');">
                      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                      <input type="hidden" name="do" value="delete">
                      <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                      <button type="submit"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition shadow-md">
                        <i class="fas fa-trash-alt"></i> Eliminar
                      </button>
                    </form>
                  </div>
                </td>


              </tr>
            <?php endforeach; ?>

            <?php if (empty($usuarios)): ?>
              <tr>
                <td colspan="8" class="px-4 py-6 text-center text-gray-500">No hay usuarios registrados.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    <!-- CREAR / EDITAR -->
    <?php elseif ($action === 'create' || $action === 'edit'):
      $isEdit = ($action === 'edit' && $editingUser);
    ?>
      <div class="bg-white/90 backdrop-blur-sm border border-red-100 rounded-2xl shadow-lg p-8 transition hover:shadow-xl duration-300">
        <h2 class="text-2xl font-semibold text-red-600 mb-6">
          <?= $isEdit ? 'Editar Usuario' : 'Crear Nuevo Usuario' ?>
        </h2>

        <form method="post" class="space-y-5">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="do" value="<?= $isEdit ? 'update' : 'create' ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= e($editingUser['id']) ?>">
  <?php endif; ?>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div>
      <label class="block text-sm font-semibold text-gray-700">Nombre</label>
      <input 
        type="text" 
        name="nombre" 
        value="<?= e($editingUser['nombre'] ?? '') ?>" 
        required 
        class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-red-600 focus:ring-red-600 p-2 outline-none">
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700">Apellido</label>
      <input 
        type="text" 
        name="apellido" 
        value="<?= e($editingUser['apellido'] ?? '') ?>" 
        required 
        class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-red-600 focus:ring-red-600 p-2 outline-none">
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div>
      <label class="block text-sm font-semibold text-gray-700">Ciudad</label>
      <input 
        type="text" 
        name="ciudad" 
        value="<?= e($editingUser['ciudad'] ?? '') ?>" 
        class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-red-600 focus:ring-red-600 p-2 outline-none">
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700">Teléfono</label>
      <input 
        type="text" 
        name="telefono" 
        value="<?= e($editingUser['telefono'] ?? '') ?>" 
        class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-red-600 focus:ring-red-600 p-2 outline-none">
    </div>
  </div>

  <div>
    <label class="block text-sm font-semibold text-gray-700">Correo Electrónico</label>
    <input 
      type="email" 
      name="email" 
      value="<?= e($editingUser['email'] ?? '') ?>" 
      required 
      class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-red-600 focus:ring-red-600 p-2 outline-none">
  </div>

  <div>
    <label class="block text-sm font-semibold text-gray-700">
      <?= $isEdit ? 'Nueva Contraseña (opcional)' : 'Contraseña' ?>
    </label>
    <input 
      type="password" 
      name="password" 
      <?= $isEdit ? '' : 'required' ?> 
      class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-red-600 focus:ring-red-600 p-2 outline-none">
  </div>

  <div class="flex justify-end space-x-3 pt-4">
  <button 
    type="button" 
    onclick="this.closest('form').reset();" 
    class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded-lg text-sm font-medium"
  >
    <i class="fas fa-times"></i> Cancelar
  </button>

  <button 
    type="submit" 
    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow-md"
  >
    <?= $isEdit ? 'Actualizar' : 'Crear' ?>
  </button>
</div>

</form>

      </div>
    <?php endif; ?>
  </main>
</body>
</html>
