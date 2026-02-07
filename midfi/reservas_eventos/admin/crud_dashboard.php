<?php
session_start();
require_once '../includes/config.php';

// === Verificar sesión ===
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = conectarDB();
$adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';
$mensaje = $_GET['msg'] ?? '';
$tipoMensaje = $_GET['tipo'] ?? '';

function obtenerClaseEstado($estado)
{
    $clases = [
        'pendiente' => 'bg-yellow-100 text-yellow-800',
        'aprobada'  => 'bg-green-100 text-green-800',
        'rechazada' => 'bg-red-100 text-red-800',
    ];
    return $clases[$estado] ?? 'bg-gray-100 text-gray-700';
}

$action = $_GET['action'] ?? 'list';
$reservaEditar = []; // datos para edición

// === Si se va a editar una reserva, cargar sus datos ===
if ($action === 'edit' && isset($_GET['id'])) {
    $reserva_id = (int)$_GET['id'];

    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.fecha_evento,
            r.num_invitados,
            r.tipo_comida_id,
            r.comentarios,
            r.estado_id,
            u.nombre,
            u.apellido,
            u.telefono,
            u.email AS correo
        FROM reservas r
        JOIN usuarios u ON r.usuario_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$reserva_id]);
    $reservaEditar = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservaEditar) {
        header("Location: ?action=list&msg=" . urlencode("Reserva no encontrada.") . "&tipo=warning");
        exit;
    }
}

// === Manejo de formularios POST ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        // === CREAR NUEVA RESERVA ===
        if ($accion === 'crear') {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $fecha_evento = $_POST['fecha_evento'] ?? '';
            $num_invitados = (int)($_POST['num_invitados'] ?? 0);
            $tipo_comida_id = (int)($_POST['tipo_comida_id'] ?? 0);
            $comentarios = $_POST['comentarios'] ?? '';

            $pdo->beginTransaction();

            // Verificar si el usuario ya existe
            $stmtU = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmtU->execute([$correo]);
            $usuario_id = $stmtU->fetchColumn();

            if (!$usuario_id) {
                $stmtU = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, telefono, email) VALUES (?, ?, ?, ?)");
                $stmtU->execute([$nombre, $apellido, $telefono, $correo]);
                $usuario_id = $pdo->lastInsertId();
            }

            // Estado "pendiente"
            $estado_id = $pdo->query("SELECT id FROM estados_reserva WHERE nombre = 'pendiente'")->fetchColumn();
            if (!$estado_id) {
                $pdo->exec("INSERT INTO estados_reserva (nombre) VALUES ('pendiente')");
                $estado_id = $pdo->lastInsertId();
            }

            // Crear reserva
            $stmtR = $pdo->prepare("
                INSERT INTO reservas (usuario_id, fecha_evento, num_invitados, tipo_comida_id, comentarios, estado_id, fecha_reserva)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmtR->execute([$usuario_id, $fecha_evento, $num_invitados, $tipo_comida_id, $comentarios, $estado_id]);

            $pdo->commit();

            header("Location: ?action=list&msg=" . urlencode("Reserva creada exitosamente.") . "&tipo=success");
            exit;
        }

        // === EDITAR RESERVA ===
        elseif ($accion === 'editar' && isset($_POST['reserva_id'])) {
            $reserva_id = (int)$_POST['reserva_id'];
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $fecha_evento = $_POST['fecha_evento'] ?? '';
            $num_invitados = (int)($_POST['num_invitados'] ?? 0);
            $tipo_comida_id = (int)($_POST['tipo_comida_id'] ?? 0);
            $comentarios = $_POST['comentarios'] ?? '';
            $estado_id = (int)($_POST['estado_id'] ?? 0);

            $pdo->beginTransaction();

            // Obtener usuario asociado
            $stmt = $pdo->prepare("SELECT usuario_id FROM reservas WHERE id = ?");
            $stmt->execute([$reserva_id]);
            $usuario_id = $stmt->fetchColumn();

            if (!$usuario_id) {
                throw new Exception("No se encontró el usuario asociado a esta reserva.");
            }

            // Actualizar usuario
            $stmtU = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, telefono=?, email=? WHERE id=?");
            $stmtU->execute([$nombre, $apellido, $telefono, $correo, $usuario_id]);

            // Actualizar reserva
            $stmtR = $pdo->prepare("
                UPDATE reservas 
                SET fecha_evento=?, num_invitados=?, tipo_comida_id=?, comentarios=?, estado_id=? 
                WHERE id=?
            ");
            $stmtR->execute([$fecha_evento, $num_invitados, $tipo_comida_id, $comentarios, $estado_id, $reserva_id]);

            $pdo->commit();

            header("Location: ?action=list&msg=" . urlencode("Reserva editada correctamente.") . "&tipo=success");
            exit;
        }

        // === CAMBIAR ESTADO DESDE TABLA ===
        elseif ($accion === 'cambiar_estado' && isset($_POST['reserva_id'])) {
            $reserva_id = (int)$_POST['reserva_id'];
            $estado_id = (int)$_POST['estado_id'];

            $stmt = $pdo->prepare("UPDATE reservas SET estado_id = ? WHERE id = ?");
            $stmt->execute([$estado_id, $reserva_id]);

            header("Location: ?action=list&msg=" . urlencode("Estado actualizado correctamente.") . "&tipo=success");
            exit;
        }

        // === APROBAR / RECHAZAR / ELIMINAR ===
        elseif (isset($_POST['reserva_id'])) {
            $reserva_id = (int)$_POST['reserva_id'];

            switch ($accion) {
                case 'aprobar':
                    $stmt = $pdo->prepare("UPDATE reservas 
                        SET estado_id = (SELECT id FROM estados_reserva WHERE nombre = 'aprobada') 
                        WHERE id = ?");
                    $stmt->execute([$reserva_id]);
                    $msg = "Reserva aprobada exitosamente.";
                    $tipo = "success";
                    break;

                case 'rechazar':
                    $stmt = $pdo->prepare("UPDATE reservas 
                        SET estado_id = (SELECT id FROM estados_reserva WHERE nombre = 'rechazada') 
                        WHERE id = ?");
                    $stmt->execute([$reserva_id]);
                    $msg = "Reserva rechazada.";
                    $tipo = "warning";
                    break;

                case 'eliminar':
                    $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
                    $stmt->execute([$reserva_id]);
                    $msg = "Reserva eliminada.";
                    $tipo = "info";
                    break;
            }

            header("Location: ?action=list&msg=" . urlencode($msg) . "&tipo=" . $tipo);
            exit;
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("⚠️ Error SQL: " . htmlspecialchars($e->getMessage()));
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("⚠️ Error: " . htmlspecialchars($e->getMessage()));
    }
}

// === Consultar tipos de comida y estados ===
$tiposComida = $pdo->query("SELECT id, nombre FROM tipos_comida")->fetchAll(PDO::FETCH_ASSOC);
$estadosReserva = $pdo->query("SELECT id, nombre FROM estados_reserva")->fetchAll(PDO::FETCH_ASSOC);

// === Consultar reservas ===
$filtro = $_GET['filtro'] ?? 'todas';
$where = ($filtro != 'todas') ? "WHERE e.nombre = :filtro" : '';

$sql = "SELECT 
            r.*, 
            u.nombre AS nombre_usuario, 
            u.apellido, 
            u.telefono, 
            u.email AS correo,
            t.nombre AS tipo_comida,
            e.nombre AS estado,
            e.id AS estado_id
        FROM reservas r
        JOIN usuarios u ON r.usuario_id = u.id
        JOIN tipos_comida t ON r.tipo_comida_id = t.id
        JOIN estados_reserva e ON r.estado_id = e.id
        $where
        ORDER BY r.fecha_reserva DESC";

$stmt = $pdo->prepare($sql);
if ($filtro != 'todas') $stmt->bindParam(':filtro', $filtro, PDO::PARAM_STR);
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Reservas</title>
  <script src="https://kit.fontawesome.com/a2d9d5f5b1.js" crossorigin="anonymous"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gradient-to-br from-red-50 via-white to-red-100">

  <!-- ======= NAV ======= -->
  <nav class="bg-red-600 shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">
      <div class="flex items-center text-white font-semibold text-lg">
        <i class="fas fa-user mr-2"></i>
        Bienvenido, <?php echo htmlspecialchars($adminNombre); ?>
      </div>
      <div class="flex space-x-6 text-sm">
        <a href="crud_election.php" class="text-white hover:text-red-200 transition">Inicio</a>
        <a href="crud_users.php" class="text-white hover:text-red-200 <?php echo (basename($_SERVER['PHP_SELF']) == 'crud_users.php') ? 'underline' : ''; ?>">Usuarios</a>
        <a href="logout.php" class="text-white hover:text-red-200 transition">Cerrar Sesión</a>
      </div>
    </div>
  </nav>

  <!-- ======= CONTENIDO PRINCIPAL ======= -->
  <div class="max-w-7xl mx-auto p-6 mt-3">

    <!-- ENCABEZADO -->
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold text-red-600 flex items-center gap-2">
        <i class="fas fa-calendar-check"></i> Administrar Reservas
      </h1>
      <div class="space-x-3">
        <a href="?action=list" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition">
          <i class="fas fa-list"></i> Lista
        </a>
        <a href="?action=create" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition shadow-md">
          <i class="fas fa-plus"></i> Nueva Reserva
        </a>
      </div>
    </div>

    <!-- MENSAJES -->
    <?php if ($mensaje): ?>
      <div class="mb-6 p-4 rounded-lg border-l-4 shadow-sm
        <?php echo ($tipoMensaje == 'success')
          ? 'bg-green-50 border-green-600 text-green-700'
          : (($tipoMensaje == 'warning')
          ? 'bg-yellow-50 text-yellow-700'
          : 'bg-red-50 text-red-700'); ?>">
        <?php echo htmlspecialchars($mensaje); ?>
      </div>
    <?php endif; ?>

    <!-- ======= FORMULARIO CREAR / EDITAR ======= -->
    <?php if ($action === 'create' || $action === 'edit'): ?>
      <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-2xl p-6 border-t-4 border-red-600">
        <h2 class="text-2xl font-semibold text-red-600 mb-4 flex items-center gap-2">
          <i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?>"></i>
          <?php echo $action === 'edit' ? 'Editar Reserva' : 'Nueva Reserva'; ?>
        </h2>

        <form method="POST" class="space-y-4" id="formReserva">
          <input type="hidden" name="accion" value="<?php echo $action === 'edit' ? 'editar' : 'crear'; ?>">
          <?php if ($action === 'edit'): ?>
            <input type="hidden" name="reserva_id" value="<?php echo htmlspecialchars($reservaEditar['id']); ?>">
          <?php endif; ?>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-semibold text-gray-700">Nombre</label>
              <input type="text" name="nombre" required value="<?php echo htmlspecialchars($reservaEditar['nombre'] ?? ''); ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:border-red-600 outline-none"/>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700">Apellido</label>
              <input type="text" name="apellido" required value="<?php echo htmlspecialchars($reservaEditar['apellido'] ?? ''); ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:border-red-600 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700">Correo</label>
            <input 
              type="email" 
              name="correo" 
              required 
              value="<?php echo htmlspecialchars($reservaEditar['correo'] ?? ''); ?>" 
              class="w-full p-3 border border-gray-300 rounded-lg focus:border-red-600 outline-none"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700">Teléfono</label>
            <input type="text" name="telefono" required value="<?php echo htmlspecialchars($reservaEditar['telefono'] ?? ''); ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:border-red-600 outline-none">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700">Fecha del Evento</label>
            <input type="date" name="fecha_evento" required value="<?php echo htmlspecialchars($reservaEditar['fecha_evento'] ?? ''); ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:border-red-600 outline-none">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700">Número de Invitados</label>
            <input type="number" name="num_invitados" min="1" required value="<?php echo htmlspecialchars($reservaEditar['num_invitados'] ?? ''); ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:border-red-600 outline-none">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700">Tipo de Comida</label>
            <select name="tipo_comida_id" required class="w-full p-3 border border-gray-300 rounded-lg focus:border-red-600 outline-none">
              <?php foreach ($tiposComida as $tipo): ?>
                <option value="<?php echo $tipo['id']; ?>" <?php echo (isset($reservaEditar['tipo_comida_id']) && $reservaEditar['tipo_comida_id'] == $tipo['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($tipo['nombre']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php if ($action === 'edit'): ?>
          <div>
            <label class="block text-sm font-semibold text-gray-700">Estado</label>
            <select name="estado_id" required class="w-full p-3 border border-gray-300 rounded-lg focus:border-red-600 outline-none">
              <?php foreach ($estadosReserva as $estado): ?>
                <option value="<?php echo $estado['id']; ?>" <?php echo ($reservaEditar['estado_id'] == $estado['id']) ? 'selected' : ''; ?>>
                  <?php echo ucfirst(htmlspecialchars($estado['nombre'])); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>

          <div>
            <label class="block text-sm font-semibold text-gray-700">Comentarios</label>
            <textarea name="comentarios" rows="3" class="w-full p-3 border border-gray-300 rounded-lg focus:border-red-600 outline-none"><?php echo htmlspecialchars($reservaEditar['comentarios'] ?? ''); ?></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <!-- BOTÓN CANCELAR QUE SOLO LIMPIA CAMPOS VISIBLES -->
            <button type="button" onclick="limpiarFormulario(this)" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition">
              Cancelar
            </button>

            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium shadow-md transition">
              <?php echo $action === 'edit' ? 'Guardar Cambios' : 'Guardar Reserva'; ?>
            </button>
          </div>
        </form>
      </div>

      <!-- SCRIPT PARA LIMPIAR FORMULARIO Y MOSTRAR TOAST -->
      <script>
      function limpiarFormulario(btn) {
        const form = btn.closest('form');
        if (!form) return;

        // Limpiar inputs visibles (no tocar inputs type=hidden)
        form.querySelectorAll('input').forEach(input => {
          if (input.type === 'hidden' || input.type === 'submit' || input.type === 'button') return;
          if (['text','email','date','number','tel','password'].includes(input.type)) input.value = '';
          if (input.type === 'checkbox' || input.type === 'radio') input.checked = false;
        });

        // Limpiar selects y textareas
        form.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        form.querySelectorAll('textarea').forEach(t => t.value = '');

        showToast('Formulario limpiado.');
      }

      function showToast(message, duration = 3000) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-20 right-6 z-50 px-4 py-2 rounded shadow bg-green-50 border border-green-200 text-green-800';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
          toast.style.transition = 'opacity 300ms ease';
          toast.style.opacity = '0';
          setTimeout(() => toast.remove(), 300);
        }, duration);
      }
      </script>

    <?php else: ?>

    <!-- ======= TABLA DE RESERVAS ======= -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <a href="?filtro=todas" class="p-4 bg-white shadow hover:shadow-md border rounded-xl text-center transition <?php echo ($_GET['filtro'] ?? 'todas')=='todas' ? 'ring-2 ring-red-600' : ''; ?>">
        <i class="fas fa-list text-red-600 text-xl mb-1"></i><p class="font-semibold text-gray-700">Todas</p>
      </a>
      <a href="?filtro=pendiente" class="p-4 bg-white shadow hover:shadow-md border rounded-xl text-center transition <?php echo ($_GET['filtro'] ?? '')=='pendiente' ? 'ring-2 ring-yellow-500' : ''; ?>">
        <i class="fas fa-hourglass-half text-yellow-500 text-xl mb-1"></i><p class="font-semibold text-gray-700">Pendientes</p>
      </a>
      <a href="?filtro=aprobada" class="p-4 bg-white shadow hover:shadow-md border rounded-xl text-center transition <?php echo ($_GET['filtro'] ?? '')=='aprobada' ? 'ring-2 ring-green-600' : ''; ?>">
        <i class="fas fa-check-circle text-green-600 text-xl mb-1"></i><p class="font-semibold text-gray-700">Aprobadas</p>
      </a>
      <a href="?filtro=rechazada" class="p-4 bg-white shadow hover:shadow-md border rounded-xl text-center transition <?php echo ($_GET['filtro'] ?? '')=='rechazada' ? 'ring-2 ring-red-600' : ''; ?>">
        <i class="fas fa-times-circle text-red-600 text-xl mb-1"></i><p class="font-semibold text-gray-700">Rechazadas</p>
      </a>
    </div>

    <div class="overflow-x-auto bg-white shadow-lg rounded-2xl border border-red-100">
      <table class="w-full text-sm text-left border-collapse">
        <thead class="bg-red-600 text-white">
          <tr>
            <th class="px-5 py-3">Cliente</th>
            <th class="px-5 py-3">Correo</th>
            <th class="px-5 py-3">Teléfono</th>
            <th class="px-5 py-3">Fecha Evento</th>
            <th class="px-5 py-3">Invitados</th>
            <th class="px-5 py-3">Tipo Comida</th>
            <th class="px-5 py-3">Estado</th>
            <th class="px-5 py-3 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($reservas) > 0): ?>
            <?php foreach ($reservas as $r): ?>
              <tr class="border-b hover:bg-gray-50 transition">
                <td class="px-5 py-3"><?php echo htmlspecialchars($r['nombre_usuario'] . ' ' . $r['apellido']); ?></td>
                <td class="px-5 py-3"><?php echo htmlspecialchars($r['correo']); ?></td>
                <td class="px-5 py-3"><?php echo htmlspecialchars($r['telefono']); ?></td>
                <td class="px-5 py-3"><?php echo htmlspecialchars($r['fecha_evento']); ?></td>
                <td class="px-5 py-3"><?php echo htmlspecialchars($r['num_invitados']); ?></td>
                <td class="px-5 py-3"><?php echo htmlspecialchars($r['tipo_comida']); ?></td>
                <td class="px-5 py-3">
                  <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo obtenerClaseEstado($r['estado']); ?>">
                    <?php echo ucfirst(htmlspecialchars($r['estado'])); ?>
                  </span>
                </td>
                <td class="px-5 py-3 text-center">
                  <div class="flex justify-center gap-2">
                    <a href="?action=edit&id=<?php echo $r['id']; ?>" class="px-3 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition" title="Editar reserva">
                      <i class="fas fa-edit"></i> Editar
                    </a>

                    <?php if ($r['estado'] === 'pendiente'): ?>
                      <form method="POST" class="inline">
                        <input type="hidden" name="reserva_id" value="<?php echo $r['id']; ?>">
                        <button type="submit" name="accion" value="aprobar" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition shadow-md" title="Aprobar reserva">
                          <i class="fas fa-check"></i> Aprobar
                        </button>
                      </form>

                      <form method="POST" class="inline">
                        <input type="hidden" name="reserva_id" value="<?php echo $r['id']; ?>">
                        <button type="submit" name="accion" value="rechazar" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition shadow-md" title="Rechazar reserva">
                          <i class="fas fa-times"></i> Rechazar
                        </button>
                      </form>

                      <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta reserva?');">
                        <input type="hidden" name="reserva_id" value="<?php echo $r['id']; ?>">
                        <button type="submit" name="accion" value="eliminar" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition shadow-md" title="Eliminar reserva">
                          <i class="fas fa-trash"></i> Eliminar
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-6 text-gray-500">No hay reservas registradas.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</body>
</html>
