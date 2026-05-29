<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de cita</title>
</head>
<body>
    <p>Hola {{ $paciente }},</p>

    <p>Tu cita ha sido confirmada con éxito.</p>

    <p>
        Psicólogo: <strong>{{ $psicologo }}</strong><br>
        Fecha: <strong>{{ $fecha }}</strong><br>
        Hora: <strong>{{ $hora }}</strong><br>
        Bloque asignado: <strong>{{ $bloque }}</strong>
    </p>

    <p>Por favor, guarda esta información y llega con tiempo.</p>

    <p>Gracias,</p>
    <p>Equipo de Psicoguía</p>
</body>
</html>
