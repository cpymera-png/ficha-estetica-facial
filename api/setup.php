<?php
/**
 * Script de instalación — Ejecutar UNA sola vez
 * Crea la tabla de fichas en la base de datos.
 * Acceder a: tu-sitio.com/api/setup.php
 */

require_once 'config.php';

$pdo = getDB();

$sql = "
CREATE TABLE IF NOT EXISTS fichas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Datos personales
    cosmiatra VARCHAR(255),
    fecha_ingreso DATE NULL,
    apellido_nombre VARCHAR(255),
    domicilio TEXT,
    fecha_nacimiento DATE NULL,
    edad INT,
    ocupacion VARCHAR(255),
    hijos VARCHAR(100),
    telefono VARCHAR(50),
    biotipo VARCHAR(50),
    condicion VARCHAR(255),
    
    -- Motivo de consulta
    motivo_consulta TEXT,
    
    -- Antecedentes personales (Sí/No)
    cardiov VARCHAR(5),
    pulmon VARCHAR(5),
    renales VARCHAR(5),
    gastro VARCHAR(5),
    hemato VARCHAR(5),
    endocr VARCHAR(5),
    neuro VARCHAR(5),
    psico VARCHAR(5),
    derma VARCHAR(5),
    metab VARCHAR(5),
    otros_antec TEXT,
    
    -- Antecedentes detalle
    marcapasos VARCHAR(255),
    cardiopatias VARCHAR(255),
    neuropatias VARCHAR(255),
    alergias VARCHAR(255),
    quirurgicos VARCHAR(255),
    hospitalizaciones VARCHAR(255),
    implantes VARCHAR(255),
    tatuajes VARCHAR(255),
    medicamentos VARCHAR(255),
    alimentacion_ant VARCHAR(255),
    otros_detail TEXT,
    
    -- Cosmiátricos
    deshidratacion VARCHAR(50),
    
    -- Fototipo
    fototipo VARCHAR(10),
    
    -- Fotodaño
    arrugas_tipo VARCHAR(50),
    piel_gruesa VARCHAR(5),
    enrojecimiento VARCHAR(5),
    lineas_naso VARCHAR(5),
    lineas_entrecejos VARCHAR(5),
    lineas_periorbicular VARCHAR(5),
    
    -- Epidermis
    grosor_gruesa VARCHAR(50),
    grosor_fina VARCHAR(50),
    dermis VARCHAR(50),
    
    -- Evaluación dietética
    apetito VARCHAR(50),
    azucares VARCHAR(100),
    lacteos VARCHAR(100),
    frutas VARCHAR(100),
    verduras VARCHAR(100),
    carnes_rojas VARCHAR(100),
    carnes_blancas VARCHAR(100),
    harinas VARCHAR(100),
    grasas VARCHAR(100),
    comidas_dia INT,
    intolerancias TEXT,
    liquidos INT,
    
    -- Patologías cutáneas (almacenadas como JSON)
    patologias JSON,
    
    -- Tendencia acneica
    tendencia_acneica VARCHAR(5),
    acne_datos JSON,
    
    -- Diagnóstico y tratamiento
    diagnostico TEXT,
    tratamiento TEXT,
    
    -- Autorización
    paciente_nombre_auth VARCHAR(255),
    paciente_dni VARCHAR(50),
    direccion_auth TEXT,
    procedimiento_auth TEXT,
    dni_firma VARCHAR(50),
    
    -- Firmas (base64)
    firma_paciente LONGTEXT,
    firma_cosmiatra LONGTEXT,
    
    -- Rutina domiciliar
    rutina_dia TEXT,
    rutina_noche TEXT,
    
    -- Fotos (base64)
    foto_antes LONGTEXT,
    foto_despues LONGTEXT
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo json_encode([
        'success' => true,
        'message' => 'Tabla "fichas" creada correctamente. Ya puedes usar el formulario.'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al crear la tabla: ' . $e->getMessage()
    ]);
}
