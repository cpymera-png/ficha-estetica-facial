<?php
/**
 * API — Eliminar ficha
 * POST /api/eliminar.php  { "id": 5 }
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("DELETE FROM fichas WHERE id = :id");
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Ficha eliminada']);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ficha no encontrada']);
}
