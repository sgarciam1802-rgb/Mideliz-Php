<?php  
/**
 * Archivo de diagnóstico para encontrar el problema
 */

echo "<h1>🔍 Diagnóstico del Sistema</h1>";

// Variable de conexión
$pdo = null;

// 1. Verificar conexión a la base de datos
echo "<h3>1. Verificando conexión a la base de datos...</h3>";
try {
    require_once '../includes/config.php';
    $pdo = conectarDB();
    if ($pdo instanceof PDO) {
        echo "✅ Conexión a la base de datos: <strong>EXITOSA</strong><br>";
    } else {
        echo "❌ Conexión a la base de datos: <strong>FALLÓ</strong><br>";
    }
} catch (Exception $e) {
    echo "❌ Error al cargar config.php o conectar DB: " . $e->getMessage() . "<br>";
}

// Continuar solo si hay conexión
if ($pdo) {

    // 2. Verificar si existe la tabla administradores
    echo "<h3>2. Verificando tabla administradores...</h3>";
    try {
        $sql = "SELECT COUNT(*) as total FROM administradores";
        $stmt = $pdo->query($sql);
        $resultado = $stmt->fetch();
        echo "✅ Tabla administradores existe. Total registros: <strong>" . $resultado['total'] . "</strong><br>";
    } catch (Exception $e) {
        echo "❌ Error con tabla administradores: " . $e->getMessage() . "<br>";
    }

    // 3. Verificar usuarios en la tabla
    echo "<h3>3. Verificando usuarios existentes...</h3>";
    try {
        $sql = "SELECT id, usuario, nombre FROM administradores";
        $stmt = $pdo->query($sql);
        $usuarios = $stmt->fetchAll();
        
        if (empty($usuarios)) {
            echo "⚠️ No hay usuarios en la tabla administradores<br>";
        } else {
            echo "✅ Usuarios encontrados:<br>";
            foreach ($usuarios as $user) {
                echo "- ID: {$user['id']}, Usuario: <strong>{$user['usuario']}</strong>, Nombre: {$user['nombre']}<br>";
            }
        }
    } catch (Exception $e) {
        echo "❌ Error al consultar usuarios: " . $e->getMessage() . "<br>";
    }

    // 4. Crear usuario admin si no existe
    echo "<h3>4. Creando usuario admin...</h3>";
    try {
        $pdo->exec("DELETE FROM administradores WHERE usuario = 'admin'");
        
        // Guardar contraseña en texto plano (SIN CIFRADO)
        $password = 'admin123';
        
        $sql = "INSERT INTO administradores (usuario, password, nombre) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['admin', $password, 'Administrador']);
        
        echo "✅ Usuario admin creado exitosamente<br>";
        echo "📋 Usuario: <strong>admin</strong><br>";
        echo "📋 Contraseña: <strong>$password</strong><br>";
        
    } catch (Exception $e) {
        echo "❌ Error al crear usuario admin: " . $e->getMessage() . "<br>";
    }

    // 5. Probar login
    echo "<h3>5. Probando verificación de contraseña...</h3>";
    try {
        $sql = "SELECT password FROM administradores WHERE usuario = 'admin'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            if ($admin['password'] === 'admin123') {
                echo "✅ Verificación de contraseña: <strong>CORRECTA</strong><br>";
            } else {
                echo "❌ Verificación de contraseña: <strong>INCORRECTA</strong><br>";
            }
        } else {
            echo "❌ Usuario admin no encontrado<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error al verificar contraseña: " . $e->getMessage() . "<br>";
    }

} else {
    echo "<p style='color:red;'>🚨 No se pudo continuar porque no hay conexión a la base de datos.</p>";
}

echo "<hr>";
echo "<h3>🎯 Resultado Final:</h3>";
echo "<p>Si todo aparece en verde arriba, ahora puedes intentar el login con:</p>";
echo "<ul>";
echo "<li><strong>Usuario:</strong> admin</li>";
echo "<li><strong>Contraseña:</strong> admin123</li>";
echo "</ul>";

echo "<p><a href='login.php' class='btn btn-primary'>🔐 Ir al Login</a></p>";
?>

