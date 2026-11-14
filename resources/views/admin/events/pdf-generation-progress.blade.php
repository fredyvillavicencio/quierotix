<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generando PDFs - {{ $event->nombre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .progress-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        
        .progress-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .progress-icon {
            font-size: 4rem;
            color: #0b6b00;
            margin-bottom: 20px;
        }
        
        .progress-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .progress-subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .progress-bar-container {
            margin: 40px 0;
        }
        
        .progress-bar {
            height: 25px;
            border-radius: 12px;
            background-color: #f0f0f0;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            border-radius: 12px;
            transition: width 0.5s ease;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background-image: linear-gradient(
                -45deg,
                rgba(255, 255, 255, 0.2) 25%,
                transparent 25%,
                transparent 50%,
                rgba(255, 255, 255, 0.2) 50%,
                rgba(255, 255, 255, 0.2) 75%,
                transparent 75%,
                transparent
            );
            background-size: 50px 50px;
            animation: move 2s linear infinite;
        }
        
        @keyframes move {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 50px 50px;
            }
        }
        
        .progress-percentage {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #020201;
            font-weight: bold;
            font-size: 14px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .progress-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #0b6b00;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .progress-message {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #15d403;
        }
        
        .message-text {
            color: #333;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .message-time {
            color: #888;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .errors-container {
            margin-top: 30px;
            display: none;
        }
        
        .error-item {
            background: #ffe6e6;
            border: 1px solid #ffcccc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            color: #cc0000;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #15d403;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-refresh {
            background: #15d403;
            color: #020201;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-refresh:hover {
            background: #a7f801;
            transform: translateY(-2px);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .status-processing {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .status-completed {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .status-failed {
            background: #ffebee;
            color: #d32f2f;
        }
        
        .completion-message {
            text-align: center;
            padding: 30px;
            background: #e8f5e9;
            border-radius: 10px;
            margin-top: 30px;
            display: none;
        }
        
        .completion-icon {
            font-size: 3rem;
            color: #388e3c;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="progress-container">
        <div class="progress-header">
            <div class="progress-icon">
                <i class="bi bi-file-pdf"></i>
            </div>
            <h1 class="progress-title">Generando PDFs</h1>
            <p class="progress-subtitle">{{ $event->nombre }}</p>
            <div id="statusBadge" class="status-badge status-processing">
                <span class="loading-spinner"></span> Procesando...
            </div>
        </div>
        
        <div class="progress-bar-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%">
                    <div class="progress-percentage" id="progressPercentage">0%</div>
                </div>
            </div>
        </div>
        
        <div class="progress-message">
            <p class="message-text" id="currentMessage">Iniciando generación de PDFs...</p>
            <p class="message-time" id="lastUpdate">Última actualización: --:--:--</p>
        </div>
        
        <div class="errors-container" id="errorsContainer">
            <h5><i class="bi bi-exclamation-triangle"></i> Errores encontrados:</h5>
            <div id="errorsList"></div>
        </div>
        
        <div class="completion-message" id="completionMessage">
            <div class="completion-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <h3 id="completionTitle">¡Generación completada!</h3>
            <p id="completionText"></p>
            <div class="action-buttons">
                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-success">
                    <i class="bi bi-eye"></i> Ver Evento
                </a>
            </div>
        </div>
        
        <div class="action-buttons" id="actionButtons">
            <a href="{{ route('admin.events.show', $event) }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Volver al Evento
            </a>
            <button class="btn-refresh" onclick="checkProgress()">
                <i class="bi bi-arrow-clockwise"></i> Actualizar
            </button>
        </div>
    </div>

    <script>
        const eventId = {{ $event->id }};
        const jobId = '{{ $jobId }}';
        let checkInterval;
        let autoRefresh = true;
        
        function updateProgress(data) {
            // Actualizar porcentaje
            const percentage = data.percentage || 0;
            document.getElementById('progressFill').style.width = percentage + '%';
            document.getElementById('progressPercentage').textContent = percentage + '%';
            
            // Actualizar mensaje
            if (data.message) {
                document.getElementById('currentMessage').textContent = data.message;
            }
            
            // Actualizar hora
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            document.getElementById('lastUpdate').textContent = `Última actualización: ${timeString}`;
            
            // Actualizar estado
            const statusBadge = document.getElementById('statusBadge');
            statusBadge.innerHTML = '';
            
            if (data.status === 'processing') {
                statusBadge.className = 'status-badge status-processing';
                statusBadge.innerHTML = '<span class="loading-spinner"></span> Procesando...';
            } else if (data.status === 'completed') {
                statusBadge.className = 'status-badge status-completed';
                statusBadge.innerHTML = '<i class="bi bi-check-circle"></i> Completado';
                autoRefresh = false;
                showCompletion(data);
            } else if (data.status === 'failed') {
                statusBadge.className = 'status-badge status-failed';
                statusBadge.innerHTML = '<i class="bi bi-x-circle"></i> Falló';
                autoRefresh = false;
                showError(data);
            } else if (data.status === 'not_found') {
                statusBadge.className = 'status-badge status-failed';
                statusBadge.innerHTML = '<i class="bi bi-question-circle"></i> No encontrado';
                autoRefresh = false;
            }
            
            // Actualizar estadísticas si están disponibles
            if (data.total_participants !== undefined) {
                document.getElementById('participantsCount').textContent = data.total_participants;
            }
            if (data.processed_participants !== undefined) {
                document.getElementById('processedCount').textContent = data.processed_participants;
            }
            if (data.total_tickets !== undefined) {
                document.getElementById('ticketsCount').textContent = data.total_tickets;
            }
            if (data.tickets_with_pdfs !== undefined) {
                document.getElementById('pdfsGenerated').textContent = data.tickets_with_pdfs;
            }
            
            // Mostrar errores si existen
            if (data.errors && data.errors.length > 0) {
                const errorsContainer = document.getElementById('errorsContainer');
                const errorsList = document.getElementById('errorsList');
                errorsContainer.style.display = 'block';
                
                errorsList.innerHTML = '';
                data.errors.forEach(error => {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-item';
                    errorDiv.innerHTML = `
                        <strong>Participante: ${error.participant_name}</strong><br>
                        <small>Error: ${error.error}</small>
                    `;
                    errorsList.appendChild(errorDiv);
                });
            }
        }
        
        function showCompletion(data) {
            const completionMessage = document.getElementById('completionMessage');
            const completionTitle = document.getElementById('completionTitle');
            const completionText = document.getElementById('completionText');
            const actionButtons = document.getElementById('actionButtons');
            
            completionTitle.textContent = '¡Generación completada!';
            completionText.textContent = data.message || 'Todos los PDFs han sido generados exitosamente.';
            
            completionMessage.style.display = 'block';
            actionButtons.style.display = 'none';
            
            // Redirigir automáticamente después de 5 segundos
            setTimeout(() => {
                window.location.href = "{{ route('admin.events.show', $event) }}";
            }, 5000);
        }
        
        function showError(data) {
            const completionMessage = document.getElementById('completionMessage');
            const completionTitle = document.getElementById('completionTitle');
            const completionText = document.getElementById('completionText');
            const actionButtons = document.getElementById('actionButtons');
            
            completionTitle.textContent = 'Error en la generación';
            completionText.textContent = data.error || 'Ocurrió un error durante la generación de PDFs.';
            
            completionMessage.style.display = 'block';
            actionButtons.style.display = 'none';
        }
        
        function checkProgress() {
            fetch(`/tickets/admin/events/${eventId}/pdf-generation/progress/${jobId}/api`)
                .then(response => response.json())
                .then(data => {
                    updateProgress(data);
                    
                    // Si aún está procesando, seguir actualizando automáticamente
                    if (autoRefresh && data.status === 'processing') {
                        clearInterval(checkInterval);
                        checkInterval = setInterval(checkProgress, 2000);
                    } else {
                        clearInterval(checkInterval);
                    }
                })
                .catch(error => {
                    console.error('Error al obtener progreso:', error);
                    document.getElementById('currentMessage').textContent = 'Error al conectar con el servidor.';
                });
        }
        
        // Iniciar cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            checkProgress();
            checkInterval = setInterval(checkProgress, 2000);
        });
        
        // Detener actualización automática cuando el usuario abandona la página
        window.addEventListener('beforeunload', function() {
            clearInterval(checkInterval);
        });
    </script>
</body>
</html>