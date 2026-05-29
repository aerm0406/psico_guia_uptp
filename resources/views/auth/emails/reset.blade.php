<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Restablecer Contraseña</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 40px;
            color: #374151;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-top: 0;
            margin-bottom: 24px;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            display: inline-block;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2), 0 2px 4px -1px rgba(79, 70, 229, 0.1);
            transition: all 0.2s ease;
        }
        .btn:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px 40px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #f3f4f6;
        }
        .footer a {
            color: #4f46e5;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PsicoGuía UPTP</h1>
        </div>
        <div class="content">
            <p>Hola, <strong>{{ $name }}</strong>:</p>
            <p>Recibiste este correo porque solicitaste restablecer la contraseña de tu cuenta en PsicoGuía UPTP.</p>
            <div class="btn-container">
                <a href="{{ $url }}" class="btn">Restablecer Contraseña</a>
            </div>
            <p>Este enlace de restablecimiento de contraseña caducará en 60 minutos.</p>
            <p>Si no realizaste esta solicitud, puedes ignorar este correo de forma segura.</p>
        </div>
        <div class="footer">
            <p>Si tienes problemas con el botón de arriba, copia y pega la siguiente URL en tu navegador:</p>
            <p><a href="{{ $url }}">{{ $url }}</a></p>
        </div>
    </div>
</body>
</html>
