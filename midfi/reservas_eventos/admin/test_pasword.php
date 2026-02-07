<?php
// Solo para verificar que la contraseña funciona
$password_texto = 'admin123';
$password_hash = '$2y$10$8K1p/wdAd8fSAgF8CAx.Ouy4S2T6B8KJjw.TQs4z9Z3K1OsN/J8zG';

if (password_verify($password_texto, $password_hash)) {
    echo "✅ La contraseña funciona correctamente";
} else {
    echo "❌ Error en la verificación de contraseña";
}
?>