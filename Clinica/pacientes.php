
<?php
require 'conexion.php';



if (isset($_POST['eliminar'])) {
    $id = (int) $_POST['eliminar'];

    $pdo->prepare("DELETE FROM consultas WHERE paciente_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM Pacientes WHERE paciente_id = ?")->execute([$id]);

    header("Location: pacientes.php");
    exit;
}



if (isset($_POST['guardar'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    if ($id > 0) {
        $sql = $pdo->prepare(
            "UPDATE Pacientes SET nombre=?, apellido=?, direccion=?, telefono=?
             WHERE paciente_id=?"
        );
        $sql->execute([$nombre, $apellido, $direccion, $telefono, $id]);
    } else {
        $sql = $pdo->prepare(
            "INSERT INTO Pacientes (nombre, apellido, direccion, telefono)
             VALUES (?, ?, ?, ?)"
        );
        $sql->execute([$nombre, $apellido, $direccion, $telefono]);
    }

    header("Location: pacientes.php");
    exit;
}


$paciente = null;

if (isset($_GET['editar'])) {
    $sql = $pdo->prepare("SELECT * FROM Pacientes WHERE paciente_id=?");
    $sql->execute([(int) $_GET['editar']]);
    $paciente = $sql->fetch(PDO::FETCH_ASSOC);
}

$lista = $pdo->query("SELECT * FROM Pacientes ORDER BY paciente_id DESC")
             ->fetchAll(PDO::FETCH_ASSOC);

function mostrar($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pacientes</title>
<link rel="stylesheet" href="estilos.css">
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
<h2>Pacientes</h2>

<h3><?= $paciente ? 'Editar paciente' : 'Registrar paciente' ?></h3>

<form method="post">
    <input type="hidden" name="id"
           value="<?= mostrar($paciente['paciente_id'] ?? 0) ?>">

    <div class="grid">
        <div class="campo">
            <label>Nombre</label>
            <input type="text" name="nombre" required
                   value="<?= mostrar($paciente['nombre'] ?? '') ?>">
        </div>

        <div class="campo">
            <label>Apellido</label>
            <input type="text" name="apellido" required
                   value="<?= mostrar($paciente['apellido'] ?? '') ?>">
        </div>

        <div class="campo">
            <label>Dirección</label>
            <input type="text" name="direccion" required
                   value="<?= mostrar($paciente['direccion'] ?? '') ?>">
        </div>

        <div class="campo">
            <label>Teléfono</label>
            <input type="text" name="telefono" required
                   value="<?= mostrar($paciente['telefono'] ?? '') ?>">
        </div>
    </div>

    <div class="acciones">
        <button class="btn" type="submit" name="guardar">
            <?= $paciente ? 'Actualizar' : 'Guardar' ?>
        </button>

        <a href="pacientes.php" class="btn gris">Cancelar</a>
    </div>
</form>

<h3>Registros de pacientes</h3>

<div class="tabla">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($lista as $fila): ?>
        <tr>
            <td><?= mostrar($fila['paciente_id']) ?></td>
            <td><?= mostrar($fila['nombre']) ?></td>
            <td><?= mostrar($fila['apellido']) ?></td>
            <td><?= mostrar($fila['direccion']) ?></td>
            <td><?= mostrar($fila['telefono']) ?></td>
            <td>
                <a class="btn amarillo"
                   href="?editar=<?= mostrar($fila['paciente_id']) ?>">
                    Editar
                </a>

                <form method="post" style="display:inline"
                      onsubmit="return confirm('¿Está seguro de eliminar este paciente definitivamente?')">

                    <button class="btn rojo" type="submit"
                            name="eliminar"
                            value="<?= mostrar($fila['paciente_id']) ?>">
                        Eliminar
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php if (!$lista): ?>
    <p>No hay pacientes registrados.</p>
<?php endif; ?>

</main>
</body>
</html>
