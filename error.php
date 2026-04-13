<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="4;url=index.php">
    <title>mvCaptcha · Error</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { height: 100%; }
        body {
            min-height: 100%;
            background: #080810;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            font-family: system-ui, "Segoe UI", sans-serif;
            color: rgba(255,255,255,.75);
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% -10%, rgba(200,60,60,.14) 0%, transparent 65%);
            pointer-events: none;
        }
        .card {
            position: relative;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 20px;
            padding: 2.5rem 3rem;
            text-align: center;
            box-shadow: 0 8px 40px rgba(0,0,0,.45), 0 0 0 1px rgba(200,60,60,.15);
            backdrop-filter: blur(16px);
        }
        .icon {
            font-size: 2.5rem;
            margin-bottom: .8rem;
            filter: drop-shadow(0 0 12px rgba(220,60,60,.5));
        }
        h2 { font-size: 1.2rem; font-weight: 600; color: #f06060; margin-bottom: .6rem; }
        p  { font-size: .875rem; color: rgba(255,255,255,.45); }
        a  { color: #9d97ff; text-decoration: none; border-bottom: 1px solid rgba(157,151,255,.35); }
        a:hover { color: #c5c2ff; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#x2716;</div>
        <h2>Validación incorrecta</h2>
        <p>Redirigiendo en 4 segundos&hellip; <a href="index.php">Intentar de nuevo</a></p>
    </div>
</body>
</html>
