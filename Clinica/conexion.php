<?php
$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$base = 'clinica';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$base;charset=utf8mb4", $usuario, $contrasena, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    exit('No se pudo conectar con la base de datos. Revisa XAMPP y conexion.php.');
}
function e($texto) { return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8'); }
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function token() { return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function verificarToken() {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403); exit('Solicitud no válida.');
    }
}
function ir($archivo) { header('Location: '.$archivo); exit; }
