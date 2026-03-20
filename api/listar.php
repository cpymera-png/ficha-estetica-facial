<?php
/**
 * API — Listar fichas
 * GET /api/listar.php              → lista todas las fichas (resumen)
 * GET /api/listar.php?id=5         → detalle de una ficha
 * GET /api/listar.php?buscar=maria → buscar por nombre
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$pdo = getDB();

// Detalle de una ficha
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM fichas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $ficha = $stmt->fetch();
    
    if (!$ficha) {
        http_response_code(404);
        echo json_encode(['error' => 'Ficha no encontrada']);
        exit;
    }
    
    // Decodificar JSON
    $ficha['patologias'] = json_decode($ficha['patologias'], true) ?? [];
    $ficha['acne_datos'] = json_decode($ficha['acne_datos'], true) ?? [];
    
    echo json_encode($ficha);
    exit;
}

// Búsqueda por nombre
$where = '';
$params = [];
if (isset($_GET['buscar']) && $_GET['buscar'] !== '') {
    $where = "WHERE apellido_nombre LIKE :buscar OR cosmiatra LIKE :buscar2 OR paciente_dni LIKE :buscar3";
    $params = [
        'buscar' => '%' . $_GET['buscar'] . '%',
        'buscar2' => '%' . $_GET['buscar'] . '%',
        'buscar3' => '%' . $_GET['buscar'] . '%'
    ];
}

// Listar resumen (sin fotos ni firmas para que sea rápido)
$sql = "SELECT id, created_at, cosmiatra, fecha_ingreso, apellido_nombre, 
        edad, telefono, biotipo, diagnostico, motivo_consulta
        FROM fichas $where ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fichas = $stmt->fetchAll();

echo json_encode([
    'total' => count($fichas),
    'fichas' => $fichas
]);
