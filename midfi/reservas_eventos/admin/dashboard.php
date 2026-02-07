<?php
/**
 * Panel de administración - Dashboard
 * Sistema de Reservas de Eventos de Comida
 */

session_start();
require_once '../includes/config.php';

// Verificar si está logueado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = conectarDB();
$mensaje = '';
$tipoMensaje = '';

// Procesar acciones (aprobar, rechazar, eliminar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion']) && isset($_POST['reserva_id'])) {
        $reserva_id = (int)$_POST['reserva_id'];
        $accion = $_POST['accion'];
        
        try {
            switch ($accion) {
                case 'aprobar':
                    $sql = "UPDATE reservas SET estado = 'aprobada' WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$reserva_id]);
                    $mensaje = "Reserva aprobada exitosamente";
                    $tipoMensaje = 'success';
                    break;
                    
                case 'rechazar':
                    $sql = "UPDATE reservas SET estado = 'rechazada' WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$reserva_id]);
                    $mensaje = "Reserva rechazada";
                    $tipoMensaje = 'warning';
                    break;
                    
                case 'eliminar':
                    $sql = "DELETE FROM reservas WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$reserva_id]);
                    $mensaje = "Reserva eliminada permanentemente";
                    $tipoMensaje = 'info';
                    break;
            }
        } catch (PDOException $e) {
            $mensaje = "Error al procesar la acción";
            $tipoMensaje = 'error';
            error_log("Error en dashboard: " . $e->getMessage());
        }
    }
}

// Obtener estadísticas
try {
    $stats = [];
    
    // Total de reservas
    $sql = "SELECT COUNT(*) as total FROM reservas";
    $stmt = $pdo->query($sql);
    $stats['total'] = $stmt->fetch()['total'];
    
    // Reservas por estado
    $sql = "SELECT estado, COUNT(*) as cantidad FROM reservas GROUP BY estado";
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch()) {
        $stats[$row['estado']] = $row['cantidad'];
    }
    
    // Asegurar que todas las estadísticas existan
    $stats['pendiente'] = $stats['pendiente'] ?? 0;
    $stats['aprobada'] = $stats['aprobada'] ?? 0;
    $stats['rechazada'] = $stats['rechazada'] ?? 0;
    
    // Próximos eventos (aprobados)
    $sql = "SELECT COUNT(*) as proximos FROM reservas WHERE estado = 'aprobada' AND fecha_evento >= CURDATE()";
    $stmt = $pdo->query($sql);
    $stats['proximos'] = $stmt->fetch()['proximos'];
    
} catch (PDOException $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    $stats = ['total' => 0, 'pendiente' => 0, 'aprobada' => 0, 'rechazada' => 0, 'proximos' => 0];
}

// Obtener todas las reservas
try {
    $filtro = $_GET['filtro'] ?? 'todas';
    $orden = $_GET['orden'] ?? 'fecha_reserva';
    $direccion = $_GET['direccion'] ?? 'DESC';
    
    $sql = "SELECT * FROM reservas";
    $params = [];
    
    // Aplicar filtro
    if ($filtro != 'todas') {
        $sql .= " WHERE estado = ?";
        $params[] = $filtro;
    }
    
    // Aplicar orden
    $sql .= " ORDER BY {$orden} {$direccion}";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reservas = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error al obtener reservas: " . $e->getMessage());
    $reservas = [];
}

// Función para formatear el tipo de comida
function formatearTipoComida($tipo) {
    $tipos = [
        'buffet' => 'Buffet',
        'menu_fijo' => 'Menú Fijo',
        'barbacoa' => 'Barbacoa',
        'vegetariano' => 'Vegetariano',
        'vegano' => 'Vegano',
        'otro' => 'Otro'
    ];
    return $tipos[$tipo] ?? $tipo;
}

// Función para obtener la clase CSS del estado
function obtenerClaseEstado($estado) {
    $clases = [
        'pendiente' => 'bg-warning',
        'aprobada' => 'bg-success',
        'rechazada' => 'bg-danger'
    ];
    return $clases[$estado] ?? 'bg-secondary';
}
?>

<!DOCTYPE html> 
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - <?php echo SITE_NAME; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-red-50 font-sans">

  <!-- Navbar -->
  <nav class="bg-gradient-to-r from-red-700 to-red-900 shadow-md">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
      <a href="#" class="text-white font-bold text-lg flex items-center">
        <i class="fas fa-utensils mr-2"></i> Panel de Administración
      </a>
      <div class="flex items-center space-x-6">
        <span class="text-red-100 text-sm">
          <i class="fas fa-user mr-1"></i> Bienvenido, <?php echo $_SESSION['admin_nombre']; ?>
        </span>
        <a href="../index.php" target="_blank" class="text-white hover:text-red-200 text-sm">
          <i class="fas fa-external-link-alt mr-1"></i> Ver Sitio
        </a>
        <a href="logout.php" class="text-white hover:text-red-200 text-sm">
          <i class="fas fa-sign-out-alt mr-1"></i> Cerrar Sesión
        </a>
      </div>
    </div>
  </nav>

  <div class="max-w-7xl mx-auto py-6 px-4">
    <?php if ($mensaje): ?>
      <?php mostrarMensaje($mensaje, $tipoMensaje); ?>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-xl shadow hover:-translate-y-1 transition p-6 text-center">
        <i class="fas fa-calendar-alt text-red-500 text-3xl mb-2"></i>
        <h4 class="text-2xl font-bold text-red-700"><?php echo $stats['total']; ?></h4>
        <p class="text-red-400">Total Reservas</p>
      </div>
      <div class="bg-white rounded-xl shadow hover:-translate-y-1 transition p-6 text-center">
        <i class="fas fa-clock text-red-400 text-3xl mb-2"></i>
        <h4 class="text-2xl font-bold text-red-500"><?php echo $stats['pendiente']; ?></h4>
        <p class="text-red-400">Pendientes</p>
      </div>
      <div class="bg-white rounded-xl shadow hover:-translate-y-1 transition p-6 text-center">
        <i class="fas fa-check-circle text-red-600 text-3xl mb-2"></i>
        <h4 class="text-2xl font-bold text-red-600"><?php echo $stats['aprobada']; ?></h4>
        <p class="text-red-400">Aprobadas</p>
      </div>
      <div class="bg-white rounded-xl shadow hover:-translate-y-1 transition p-6 text-center">
        <i class="fas fa-times-circle text-red-800 text-3xl mb-2"></i>
        <h4 class="text-2xl font-bold text-red-800"><?php echo $stats['rechazada']; ?></h4>
        <p class="text-red-400">Rechazadas</p>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h5 class="font-semibold mb-3 text-red-700"><i class="fas fa-filter mr-2"></i>Filtros y Ordenamiento</h5>
          <div class="flex flex-wrap gap-2">
            <a href="?filtro=todas" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo ($filtro == 'todas') ? 'bg-red-700 text-white' : 'border border-red-700 text-red-700'; ?>">Todas</a>
            <a href="?filtro=pendiente" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo ($filtro == 'pendiente') ? 'bg-red-400 text-white' : 'border border-red-400 text-red-500'; ?>">Pendientes</a>
            <a href="?filtro=aprobada" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo ($filtro == 'aprobada') ? 'bg-red-600 text-white' : 'border border-red-600 text-red-600'; ?>">Aprobadas</a>
            <a href="?filtro=rechazada" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo ($filtro == 'rechazada') ? 'bg-red-800 text-white' : 'border border-red-800 text-red-800'; ?>">Rechazadas</a>
          </div>
        </div>
        <div class="flex gap-2">
          <a href="?filtro=<?php echo $filtro; ?>&orden=fecha_evento&direccion=ASC" class="px-3 py-2 border rounded-lg text-sm hover:bg-red-50 border-red-300 text-red-700">
            <i class="fas fa-sort-amount-up mr-1"></i>Por Fecha Evento
          </a>
          <a href="?filtro=<?php echo $filtro; ?>&orden=fecha_reserva&direccion=DESC" class="px-3 py-2 border rounded-lg text-sm hover:bg-red-50 border-red-300 text-red-700">
            <i class="fas fa-sort-amount-down mr-1"></i>Más Recientes
          </a>
        </div>
      </div>
    </div>

    <!-- Tabla de Reservas -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="flex justify-between items-center p-4 border-b border-red-200">
        <h4 class="font-semibold flex items-center text-red-700">
          <i class="fas fa-list mr-2"></i> Lista de Reservas
          <span class="ml-2 px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm"><?php echo count($reservas); ?> registros</span>
        </h4>
      </div>

      <?php if (empty($reservas)): ?>
        <div class="text-center py-10">
          <i class="fas fa-inbox text-4xl text-red-300 mb-3"></i>
          <h5 class="text-red-400 font-medium">No hay reservas que mostrar</h5>
          <p class="text-red-300 text-sm">Las reservas aparecerán aquí cuando los usuarios las envíen.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-red-700 text-white">
              <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">Cliente</th>
                <th class="px-4 py-3 text-left">Contacto</th>
                <th class="px-4 py-3 text-left">Fecha Evento</th>
                <th class="px-4 py-3 text-center">Invitados</th>
                <th class="px-4 py-3 text-left">Tipo Comida</th>
                <th class="px-4 py-3 text-left">Estado</th>
                <th class="px-4 py-3 text-left">Fecha Reserva</th>
                <th class="px-4 py-3 text-left">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-red-100">
              <?php foreach ($reservas as $reserva): ?>
              <tr class="hover:bg-red-50">
                <td class="px-4 py-3 font-bold text-red-700">#<?php echo $reserva['id']; ?></td>
                <td class="px-4 py-3"><?php echo htmlspecialchars($reserva['nombre']); ?></td>
                <td class="px-4 py-3 text-sm">
                  <i class="fas fa-envelope mr-1 text-red-400"></i><?php echo htmlspecialchars($reserva['correo']); ?><br>
                  <i class="fas fa-phone mr-1 text-red-400"></i><?php echo htmlspecialchars($reserva['telefono']); ?>
                </td>
                <td class="px-4 py-3 font-semibold text-red-600"><?php echo date('d/m/Y', strtotime($reserva['fecha_evento'])); ?></td>
                <td class="px-4 py-3 text-center"><span class="px-3 py-1 rounded-full bg-red-100 text-red-600"><?php echo $reserva['num_invitados']; ?> personas</span></td>
                <td class="px-4 py-3"><?php echo formatearTipoComida($reserva['tipo_comida']); ?></td>
                <td class="px-4 py-3"><span class="px-3 py-1 rounded-full <?php echo obtenerClaseEstado($reserva['estado']); ?>"><?php echo ucfirst($reserva['estado']); ?></span></td>
                <td class="px-4 py-3 text-sm"><?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])); ?></td>
                <td class="px-4 py-3 space-y-1">
                  <?php if ($reserva['estado'] == 'pendiente'): ?>
                    <form method="POST" class="inline">
                      <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                      <input type="hidden" name="accion" value="aprobar">
                      <button type="submit" class="w-full bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-xs" onclick="return confirm('¿Aprobar esta reserva?')">
                        <i class="fas fa-check"></i> Aprobar
                      </button>
                    </form>
                    <form method="POST" class="inline">
                      <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                      <input type="hidden" name="accion" value="rechazar">
                      <button type="submit" class="w-full bg-red-400 text-white px-3 py-1 rounded hover:bg-red-500 text-xs" onclick="return confirm('¿Rechazar esta reserva?')">
                        <i class="fas fa-times"></i> Rechazar
                      </button>
                    </form>
                  <?php endif; ?>

                  <?php if (!empty($reserva['comentarios'])): ?>
                    <button type="button" onclick="document.getElementById('comentarios-<?php echo $reserva['id']; ?>').classList.remove('hidden')" class="w-full bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-xs">
                      <i class="fas fa-comment"></i> Ver Comentarios
                    </button>
                  <?php endif; ?>

                  <form method="POST" class="inline">
                    <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                    <input type="hidden" name="accion" value="eliminar">
                    <button type="submit" class="w-full bg-red-800 text-white px-3 py-1 rounded hover:bg-red-900 text-xs" onclick="return confirm('¿Estás seguro de eliminar esta reserva?')">
                      <i class="fas fa-trash"></i> Eliminar
                    </button>
                  </form>
                </td>
              </tr>

              <!-- Modal Comentarios -->
              <?php if (!empty($reserva['comentarios'])): ?>
              <div id="comentarios-<?php echo $reserva['id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-lg w-96">
                  <div class="p-4 border-b border-red-200 flex justify-between items-center">
                    <h5 class="font-bold text-red-700"><i class="fas fa-comment mr-2"></i>Comentarios - <?php echo htmlspecialchars($reserva['nombre']); ?></h5>
                    <button onclick="document.getElementById('comentarios-<?php echo $reserva['id']; ?>').classList.add('hidden')" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                  </div>
                  <div class="p-4 text-red-700">
                    <p><?php echo nl2br(htmlspecialchars($reserva['comentarios'])); ?></p>
                  </div>
                  <div class="p-4 border-t border-red-200 text-right">
                    <button onclick="document.getElementById('comentarios-<?php echo $reserva['id']; ?>').classList.add('hidden')" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Cerrar</button>
                  </div>
                </div>
              </div>
              <?php endif; ?>

              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>
