<?php require 'conexion.php'; ?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistema de Clínica</title>
        <link rel="stylesheet" href="estilos.css">
    </head>
    <body>
        <header>
            <h1>Sistema de Gestión de Clínicas</h1>
            <h3>Freddy Guaillas</h3>
            
            </nav>
        </header>
        <main>
            <h2>Panel principal</h2>
            <p>Seleccione el módulo que desea administrar.</p>
            <div class="tarjetas">
                <a class="tarjeta" href="pacientes.php"> Registrar paciente</a>
                <a class="tarjeta" href="medicos.php"> Registrar médico</a>
                <a class="tarjeta" href="consultas.php"> Gestionar consulta</a>
            </div>
        </main>
</body>
</html>
