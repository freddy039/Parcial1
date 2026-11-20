<?php
require 'conexion.php';
$tabla = 'consultas';
$clave = 'consulta_id';
$error = "";
$id = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT) ?: 0;
$actual = [];
$pacientes = $pdo->query('SELECT paciente_id, nombre, apellido FROM Pacientes ORDER BY nombre')->fetchAll();
$medicos = $pdo->query('SELECT medico_id, nombre, especialidad FROM Medicos ORDER BY nombre')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();
    $accion = $_POST['accion'] ?? '';
    $registroId = filter_var($_POST['consulta_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    try {
        if ($accion === 'eliminar' && $registroId > 0) {
            $stmt = $pdo->prepare('DELETE FROM consultas WHERE consulta_id = ?');
            $stmt->execute([$registroId]);
            ir('consultas.php');
        }
        if ($accion === 'guardar') {
            $paciente_id = filter_var($_POST['paciente_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
            if ($paciente_id < 1) throw new Exception('Seleccione paciente.');
            $medico_id = filter_var($_POST['medico_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
            if ($medico_id < 1) throw new Exception('Seleccione médico.');
            $fecha = trim($_POST['fecha'] ?? '');
            if ($fecha === '') throw new Exception('Complete el campo fecha y hora.');
            $motivo = trim($_POST['motivo'] ?? '');
            if ($motivo === '') throw new Exception('Complete el campo motivo.');
            $fecha = str_replace('T', ' ', $fecha);
            if (!DateTime::createFromFormat('Y-m-d H:i', $fecha)) throw new Exception('Fecha inválida.');
            $fecha .= ':00';
            if ($registroId > 0) {
                $stmt = $pdo->prepare('UPDATE consultas SET paciente_id = ?, medico_id = ?, fecha = ?, motivo = ? WHERE consulta_id = ?');
                $stmt->execute([$paciente_id, $medico_id, $fecha, $motivo, $registroId]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO consultas (paciente_id, medico_id, fecha, motivo) VALUES (?, ?, ?, ?)');
                $stmt->execute([$paciente_id, $medico_id, $fecha, $motivo]);
            }
            ir('consultas.php');
        }
    } catch (Throwable $ex) {
        $error = $ex instanceof PDOException ? "No se pudo guardar o eliminar. Compruebe los datos y las relaciones entre registros." : $ex->getMessage();
    }
}
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM consultas WHERE consulta_id = ?');
    $stmt->execute([$id]);
    $actual = $stmt->fetch() ?: [];
}
$filas = $pdo->query('SELECT c.*, CONCAT(p.nombre, " ", p.apellido) AS paciente, CONCAT(m.nombre, " (", m.especialidad, ")") AS medico FROM consultas c JOIN Pacientes p ON p.paciente_id = c.paciente_id JOIN Medicos m ON m.medico_id = c.medico_id ORDER BY c.fecha DESC')->fetchAll();
?>
<!doctype html><html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Consultas</title><link rel="stylesheet" href="estilos.css">
    </head>
    <body>
            <header>
                <h1>Sistema de Gestión de Clínicas</h1>
                <nav>
                    <a href="index.php">Inicio</a>
                    <a href="pacientes.php">Pacientes</a>
                    <a href="medicos.php">Médicos</a>
                    <a href="consultas.php">Consultas</a>
                </nav>
            </header>
            <main>
                <h2>Consultas</h2>
                <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
                    <h3><?= $actual ? "Editar registro" : "Nuevo registro" ?></h3>
                    <form method="post" action="consultas.php"><?= token() ?>
                    <input type="hidden" name="accion" value="guardar">
                    <input type="hidden" name="consulta_id" value="<?= e($actual['consulta_id'] ?? 0) ?>">
                    <div class="grid">
                        <div class="campo">
                            <label for="paciente_id">Paciente</label>
                            <select id="paciente_id" name="paciente_id" required>
                                <option value="">Seleccione paciente</option>
                                <?php foreach ($pacientes as $p): ?>
                                    <option value="<?= e($p['paciente_id']) ?>" 
                                    <?= (string)($actual['paciente_id'] ?? '') === (string)$p['paciente_id'] ? 'selected' : '' ?>><?= e($p['nombre'] . ' ' . $p['apellido']) ?>
                                </option><?php endforeach; ?></select>
                            </div>
                            <div class="campo">
                                <label for="medico_id">Médico</label>
                                <select id="medico_id" name="medico_id" required>
                                    <option value="">Seleccione médico</option>
                                    <?php foreach ($medicos as $m): ?>
                                        <option value="<?= e($m['medico_id']) ?>" 
                                        <?= (string)($actual['medico_id'] ?? '') === (string)$m['medico_id'] ? 'selected' : '' ?>><?= e($m['nombre'] . ' - ' . $m['especialidad']) ?>
                                    </option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="campo">
                                <label for="fecha">Fecha y hora</label>
                                <input id="fecha" type="datetime-local" name="fecha" value="<?= e(str_replace(' ', 'T', substr($actual['fecha'] ?? '', 0, 16))) ?>" required>
                            </div>
                            <div class="campo">
                                <label for="motivo">Motivo</label>
                                <input id="motivo" type="text" name="motivo" value="<?= e($actual['motivo'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="acciones">
                            <button class="btn" type="submit">Guardar</button>
                            <a class="btn gris" href="consultas.php">Cancelar</a></div></form>
                            <h3>Registros</h3><div class="tabla"><table>
                                <thead><tr>
                                <th>ID</th>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Fecha y hora</th>
                                <th>Motivo</th>
                                <th>Acciones</th>
                            </tr></thead>
                            <tbody><?php foreach ($filas as $fila): ?><tr>
                                <td><?= e($fila['consulta_id']) ?></td>
                                <td><?= e($fila['paciente']) ?></td>
                                <td><?= e($fila['medico']) ?></td>
                                <td><?= e($fila['fecha']) ?></td>
                                <td><?= e($fila['motivo']) ?></td>
                                <td><a class="btn amarillo" href="consultas.php?editar=<?= urlencode($fila['consulta_id']) ?>">Editar</a> 
                                    <form method="post" action="consultas.php" onsubmit="return confirm('¿Eliminar este registro?')"><?= token() ?>
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="consulta_id" value="<?= e($fila['consulta_id']) ?>">
                                        <button class="btn rojo" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr><?php endforeach; ?></tbody></table>
                        </div>
<?php if (!$filas): ?><p>No hay registros todavía.</p><?php endif; ?>
</main>
</body>
</html>
