<?php
/**
 * Configuración de Base de Datos
 * ================================
 * INSTRUCCIONES: Reemplaza estos valores con los datos
 * de tu base de datos creada en Hostinger (hPanel).
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'u959661053_ficha');      // Nombre de tu BD
define('DB_USER', 'u959661053_ficha');      // Usuario de tu BD
define('DB_PASS', 'TU_CONTRASEÑA_AQUI');   // Contraseña de tu BD

// Conexión PDO
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión a la base de datos']);
        exit;
    }
}

// CORS headers para permitir peticiones desde el formulario
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
