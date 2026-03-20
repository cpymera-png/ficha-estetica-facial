<?php
/**
 * API — Guardar ficha
 * POST /api/guardar.php
 * Recibe JSON con todos los campos del formulario
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$pdo = getDB();

// Campos directos
$directFields = [
    'cosmiatra', 'fecha_ingreso', 'apellido_nombre', 'domicilio',
    'fecha_nacimiento', 'edad', 'ocupacion', 'hijos', 'telefono',
    'biotipo', 'condicion', 'motivo_consulta',
    'cardiov', 'pulmon', 'renales', 'gastro', 'hemato',
    'endocr', 'neuro', 'psico', 'derma', 'metab', 'otros_antec',
    'marcapasos', 'cardiopatias', 'neuropatias', 'alergias',
    'quirurgicos', 'hospitalizaciones', 'implantes', 'tatuajes',
    'medicamentos', 'alimentacion_ant', 'otros_detail',
    'deshidratacion', 'fototipo', 'arrugas_tipo',
    'piel_gruesa', 'enrojecimiento',
    'lineas_naso', 'lineas_entrecejos', 'lineas_periorbicular',
    'grosor_gruesa', 'grosor_fina', 'dermis',
    'apetito', 'azucares', 'lacteos', 'frutas', 'verduras',
    'carnes_rojas', 'carnes_blancas', 'harinas', 'grasas',
    'comidas_dia', 'intolerancias', 'liquidos',
    'tendencia_acneica',
    'diagnostico', 'tratamiento',
    'paciente_nombre_auth', 'paciente_dni', 'direccion_auth',
    'procedimiento_auth', 'dni_firma',
    'firma_paciente', 'firma_cosmiatra',
    'rutina_dia', 'rutina_noche',
    'foto_antes', 'foto_despues'
];

// Recoger patologías (todos los campos que empiezan con "pat_")
$patologias = [];
$acneDatos = [];
foreach ($input as $key => $value) {
    if (strpos($key, 'pat_') === 0) {
        $patologias[$key] = $value;
    }
    if (strpos($key, 'acne_') === 0) {
        $acneDatos[$key] = $value;
    }
}

// Preparar datos
$data = [];
foreach ($directFields as $field) {
    $val = $input[$field] ?? null;
    // Convertir fechas vacías a null
    if (in_array($field, ['fecha_ingreso', 'fecha_nacimiento']) && empty($val)) {
        $val = null;
    }
    // Convertir números vacíos a null
    if (in_array($field, ['edad', 'comidas_dia', 'liquidos']) && $val === '') {
        $val = null;
    }
    $data[$field] = $val;
}
$data['patologias'] = json_encode($patologias, JSON_UNESCAPED_UNICODE);
$data['acne_datos'] = json_encode($acneDatos, JSON_UNESCAPED_UNICODE);

// Construir INSERT
$columns = array_keys($data);
$placeholders = array_map(fn($c) => ":$c", $columns);

$sql = "INSERT INTO fichas (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    $id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Ficha guardada correctamente',
        'id' => $id
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al guardar: ' . $e->getMessage()
    ]);
}
