<?php
require 'conexion.php';
$tabla = 'Medicos';
$clave = 'medico_id';
$error = "";
$id = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT) ?: 0;
$actual = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();
    $accion = $_POST['accion'] ?? '';
    $registroId = filter_var($_POST['medico_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    try {
        if ($accion === 'eliminar' && $registroId > 0) {
            $stmt = $pdo->prepare('DELETE FROM Medicos WHERE medico_id = ?');
            $stmt->execute([$registroId]);
            ir('medicos.php');
        }
        if ($accion === 'guardar') {
            $nombre = trim($_POST['nombre'] ?? '');
            if ($nombre === '') throw new Exception('Complete el campo nombre.');
            $especialidad = trim($_POST['especialidad'] ?? '');
            if ($especialidad === '') throw new Exception('Complete el campo especialidad.');
            $telefono = trim($_POST['telefono'] ?? '');
            if ($telefono === '') throw new Exception('Complete el campo teléfono.');
            $email = trim($_POST['email'] ?? '');
            if ($email === '') throw new Exception('Complete el campo correo electrónico.');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Correo inválido.');
            if ($registroId > 0) {
                $stmt = $pdo->prepare('UPDATE Medicos SET nombre = ?, especialidad = ?, telefono = ?, email = ? WHERE medico_id = ?');
                $stmt->execute([$nombre, $especialidad, $telefono, $email, $registroId]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO Medicos (nombre, especialidad, telefono, email) VALUES (?, ?, ?, ?)');
                $stmt->execute([$nombre, $especialidad, $telefono, $email]);
            }
            ir('medicos.php');
        }
    } catch (Throwable $ex) {
        $error = $ex instanceof PDOException ? "No se pudo guardar o eliminar. Compruebe los datos y las relaciones entre registros." : $ex->getMessage();
    }
}
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM Medicos WHERE medico_id = ?');
    $stmt->execute([$id]);
    $actual = $stmt->fetch() ?: [];
}
$filas = $pdo->query('SELECT * FROM Medicos ORDER BY medico_id DESC')->fetchAll();
?>
<!doctype html><html lang="es">
    <head><meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Médicos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header><h1>Sistema de Gestión de Clínicas</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="pacientes.php">Pacientes</a>
        <a href="medicos.php">Médicos</a>
        <a href="consultas.php">Consultas</a>
    </nav>
</header>
<main>
    <h2>Médicos</h2>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
        <h3><?= $actual ? "Editar registro" : "Nuevo registro" ?></h3>
        <form method="post" action="medicos.php"><?= token() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="medico_id" value="<?= e($actual['medico_id'] ?? 0) ?>">
        <div class="grid">
            <div class="campo"><label for="nombre">Nombre</label>
            <input id="nombre" type="text" name="nombre" value="<?= e($actual['nombre'] ?? '') ?>" required>
        </div>
        <div class="campo">
            <label for="especialidad">Especialidad</label>
            <input id="especialidad" type="text" name="especialidad" value="<?= e($actual['especialidad'] ?? '') ?>" required>
        </div>
        <div class="campo">
            <label for="telefono">Teléfono</label>
            <input id="telefono" type="text" name="telefono" value="<?= e($actual['telefono'] ?? '') ?>" required>
        </div>
        <div class="campo">
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" value="<?= e($actual['email'] ?? '') ?>" required>
            </div>
</div>
<div class="acciones">
    <button class="btn" type="submit">Guardar</button>
    <a class="btn gris" href="medicos.php">Cancelar</a>
</div>
</form>
<h3>Registros</h3>
<div class="tabla">
    <table><thead><tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Especialidad</th>
        <th>Teléfono</th>
        <th>Correo electrónico</th>
        <th>Acciones</th>
    </tr></thead>
    <tbody><?php foreach ($filas as $fila): ?><tr>
        <td><?= e($fila['medico_id']) ?></td>
        <td><?= e($fila['nombre']) ?></td>
        <td><?= e($fila['especialidad']) ?></td>
        <td><?= e($fila['telefono']) ?></td>
        <td><?= e($fila['email']) ?></td>
        <td>
            <a class="btn amarillo" href="medicos.php?editar=<?= urlencode($fila['medico_id']) ?>">Editar</a> 
            <form method="post" action="medicos.php" onsubmit="return confirm('¿Eliminar este registro?')"><?= token() ?>
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="medico_id" value="<?= e($fila['medico_id']) ?>">
            <button class="btn rojo" type="submit">Eliminar</button>
        </form>
    </td>
</tr><?php endforeach; ?>
</tbody></table></div>
<?php if (!$filas): ?><p>No hay registros todavía.</p><?php endif; ?>
</main>
</body>
</html>
