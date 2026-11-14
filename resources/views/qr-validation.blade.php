<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Tickets - Sistema de Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f2037 0%, #0f2037 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .h1, h1 {
            font-size: 5.5rem;
        }

        .validation-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        
        .validation-header {
            background: #2c3e50;
            color: white;
            text-align: center;
        }
        
        .validation-icon {
            font-size: 4rem;
        }
        
        .validation-content {
            padding: 30px;
        }
        
        .status-card {
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .status-valid {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        
        .status-invalid {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .ticket-info {
            border-radius: 10px;
            padding: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        
        .info-value {
            color: #212529;
            text-align: right;
        }
        
        .qr-scanner {
            text-align: center;
            margin: 30px 0;
        }
        
        .qr-placeholder {
            width: 200px;
            height: 200px;
            background: #e9ecef;
            border: 2px dashed #adb5bd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #6c757d;
        }
        
        .manual-input {
            margin-top: 20px;
        }
        
        .validation-count {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-block {
            flex: 1;
        }
        
        .sound-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        @media (max-width: 576px) {
            .validation-container {
                border-radius: 15px;
            }
            
            .validation-content {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    
    <div class="validation-container">
        <div class="validation-header">
            <div class="validation-icon">
                @if($status === 'valid')
                    <!-- Ticket válido - Mostrar datos -->
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                @else
                    <!-- Ticket inválido - Mostrar icono de error -->
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                @endif
                <i class="bi bi-ticket-perforated"></i>
            </div>
        </div>
        
        <div class="validation-content">

            <!-- Validation Result -->
            <div id="validationResult">
                
                @if($status === 'valid')
                    <!-- Ticket válido - Mostrar datos -->
                    <div class="ticket-info text-center">
                        @if($isPhotographer ?? false)
                        <h2 class="mb-3" id="respuesta">
                            Fotógrafo
                        </h2>
                        @else
                        <h1 class="mb-3" id="respuesta">
                            {{ $locacion ?? '' }}
                        </h1>
                        <p class="fs-4 mb-0">Asiento {{ $asiento ?? '' }}</p>
                        @endif
                    </div>
                @else
                    <!-- Ticket inválido - Mostrar icono de error -->
                    <div class="ticket-info text-center" id="ticketInfo">
                        <h1 class="mb-3 text-danger">
                            Inválido
                        </h1>
                    </div>
                @endif

                @if($ticket->contador_validaciones > 1)
                    <h5 class="text-danger text-center"><i class="bi bi-x-circle-fill text-danger"></i> Codigo YA validado</h5>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function closeWindow() {
        // Intenta cerrar la ventana/pestaña actual
        window.close();
    }
    
    // Alternativa si prefieres manejarlo directamente
    document.getElementById('nextTicket')?.addEventListener('click', function() {
        window.close();
    });

</script>

</body>
</html>