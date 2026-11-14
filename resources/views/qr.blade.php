<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - QuieroTIX.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
        }
        
        .qr-container {
            text-align: center;
            padding: 20px;
        }
        
        .qr-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333;
            font-weight: 500;
        }
        
        .qr-code {
            margin: 0 auto;
            padding: 10px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .qr-url {
            margin-top: 20px;
            font-size: 1rem;
            color: #666;
            word-break: break-all;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        @media (max-width: 576px) {
            .qr-title {
                font-size: 1.2rem;
            }
            
            .qr-code img {
                width: 280px;
                height: 280px;
            }
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <h1 class="qr-title">Código QR</h1>
        
        <div class="qr-code">
            {!! QrCode::size(400)->generate('https://QuieroTIX.com') !!}
        </div>
        
        <div class="qr-url">
            <p><strong>https://QuieroTIX.com</strong></p>
        </div>
    </div>
</body>
</html>