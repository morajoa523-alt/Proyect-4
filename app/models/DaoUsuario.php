<?php
require_once __DIR__ . '/../config/Database.php';

class DaoUsuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /* ===============================
       INSERTAR USUARIO
    =============================== */
    public function insertUsuario(array $data): int
    {
        $sql = "
            INSERT INTO usuarios (correo, contrasena, id_tipo, nombres, apellidos, cedula, celular)
            VALUES (:correo, :contrasena, :id_tipo, :nombres, :apellidos, :cedula, :celular)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':correo'     => $data['correo'],
            ':contrasena' => $data['contrasena'], // ya viene hasheada
            ':id_tipo'    => $data['id_tipo'],
            ':nombres'    => $data['nombres'] ?? null,
            ':apellidos'    => $data['apellidos']?? null,
            ':cedula'    => $data['cedula'] ?? null,
            ':celular'    => $data['celular'] ?? null

        ]);

        return (int) $this->db->lastInsertId();
    }

    /* ===============================
       LISTAR USUARIOS
    =============================== */
    public function selectUsuarios(): array
    {
        $sql = "SELECT u.id_usuario, u.correo, t.nombre AS rol
FROM usuarios u
JOIN tipos_usuario t ON u.id_tipo = t.id_tipo
WHERE t.nombre <> 'cliente'
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===============================
       OBTENER USUARIO POR ID
    =============================== */
    public function selectUsuario(int $id): ?array
    {
        $sql = "
            SELECT 
                u.id_usuario,
                u.correo,
                u.id_tipo,
                u.nombres,
                u.apellidos,
                u.cedula,
                u.celular,
                t.nombre AS rol
            FROM usuarios u
            INNER JOIN tipos_usuario t 
                ON u.id_tipo = t.id_tipo
            WHERE u.id_usuario = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /* ===============================
       ACTUALIZAR USUARIO
    =============================== */
    public function updateUsuario(int $id, array $data): bool
    {
        $campos = [];
        $params = [':id' => $id];

        if (isset($data['correo'])) {
            $campos[] = 'correo = :correo';
            $params[':correo'] = $data['correo'];
        }

        if (isset($data['id_tipo'])) {
            $campos[] = 'id_tipo = :id_tipo';
            $params[':id_tipo'] = $data['id_tipo'];
        }

        if (isset($data['contrasena'])) {
            $campos[] = 'contrasena = :contrasena';
            $params[':contrasena'] = $data['contrasena'];
        }

        if (isset($data['nombres'])) {
            $campos[] = 'nombres = :nombres';
            $params[':nombres'] = $data['nombres'];
        }

        if (isset($data['apellidos'])) {
            $campos[] = 'apellidos = :apellidos';
            $params[':apellidos'] = $data['apellidos'];
        }

        if (isset($data['cedula'])) {
            $campos[] = 'cedula = :cedula';
            $params[':cedula'] = $data['cedula'];
        }

        if (isset($data['celular'])) {
            $campos[] = 'celular = :celular';
            $params[':celular'] = $data['celular'];
        }
 
        
        if (empty($campos)) {
            return false;
        }

        $sql = "
            UPDATE usuarios
            SET " . implode(', ', $campos) . "
            WHERE id_usuario = :id
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /* ===============================
       ELIMINAR USUARIO
    =============================== */
    public function deleteUsuario(int $id): bool
    {
        $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /* ===============================
       LISTAR TIPOS DE USUARIO (ROLES)
    =============================== */
    public function selectTiposUsuario(): array
    {

        $sql = "
            SELECT id_tipo, nombre
            FROM tipos_usuario
            where nombre != 'CLIENTE' AND nombre != 'EMPLEADO'
            ORDER BY nombre ASC
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function buscarPorCorreo(string $correo): ?array
{
    $sql = "
        SELECT 
            u.id_usuario,
            u.correo,
            u.contrasena,
            u.id_tipo,
            u.nombres,
            u.apellidos,
            u.cedula,
            u.celular,
            t.nombre AS rol
        FROM usuarios u
        INNER JOIN tipos_usuario t ON u.id_tipo = t.id_tipo
        WHERE u.correo = :correo
        LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':correo' => $correo]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //echo "------------------------------" . print_r($row);
    return $row ?: null;
}


public function obtenerTiposAccesoUsuario(): array
{
    $sql = "SELECT id_acceso_usuario, acceso FROM tipo_acceso_usuario";
    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

public function crearUsuarioReporte(
    int $idUsuario,
    int $idAccesoUsuario,
    ?string $rutaReporte = null
): bool {
    $sql = "
        INSERT INTO usuario_reporte 
        (id_usuario, id_acceso_usuario, ruta_reporte)
        VALUES (:id_usuario, :id_acceso_usuario, :ruta_reporte)
    ";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        'id_usuario' => $idUsuario,
        'id_acceso_usuario' => $idAccesoUsuario,
        'ruta_reporte' => $rutaReporte
    ]);
}


public function obtenerUsuariosReporte(): array
{
    $sql = "
        SELECT 
            ur.id_usuario_reporte,
            u.correo AS usuario,
            ta.acceso,
            u.id_usuario,
            ta.id_acceso_usuario,
            ur.ruta_reporte
        FROM usuario_reporte ur
        INNER JOIN usuarios u ON u.id_usuario = ur.id_usuario
        INNER JOIN tipo_acceso_usuario ta 
            ON ta.id_acceso_usuario = ur.id_acceso_usuario
    ";

    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function obtenerUsuarioReportePorId(int $id): ?array
{
    $sql = "
        SELECT *
        FROM usuario_reporte
        WHERE id_usuario_reporte = :id
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}


public function actualizarUsuarioReporte(
    int $idUsuarioReporte,
    int $idUsuario,
    int $idAccesoUsuario,
    ?string $rutaReporte
): bool {
    $sql = "
        UPDATE usuario_reporte
        SET 
            id_usuario = :id_usuario,
            id_acceso_usuario = :id_acceso_usuario,
            ruta_reporte = :ruta_reporte
        WHERE id_usuario_reporte = :id
    ";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        'id' => $idUsuarioReporte,
        'id_usuario' => $idUsuario,
        'id_acceso_usuario' => $idAccesoUsuario,
        'ruta_reporte' => $rutaReporte
    ]);
}

public function eliminarUsuarioReporte(int $id): bool
{
    $sql = "DELETE FROM usuario_reporte WHERE id_usuario_reporte = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute(['id' => $id]);
}

public function selectUsuariosReporte(): array
{
    $sql = "
        SELECT id_usuario, correo as nombre
        FROM usuarios
        WHERE id_tipo = 4
    ";
    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

public function obtenerAccesoReportePorUsuario(int $idUsuario): ?array
{
    $sql = "
        SELECT 
            ur.ruta_reporte,
            ta.acceso
        FROM usuario_reporte ur
        INNER JOIN tipo_acceso_usuario ta
            ON ta.id_acceso_usuario = ur.id_acceso_usuario
        WHERE ur.id_usuario = :id_usuario
        LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id_usuario' => $idUsuario]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}


}