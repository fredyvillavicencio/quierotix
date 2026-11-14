<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar CSV - {{ $event->nombre }} - Sistema de Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }
        
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link.active {
            background-color: #3498db;
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .page-header {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            color: #020201;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .import-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #15d403;
            background: #f4ffe0;
        }
        
        .upload-area.dragover {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .upload-icon {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .file-info {
            background: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .csv-preview {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .preview-table {
            font-size: 12px;
        }
        
        .preview-table th {
            background: #f8f9fa;
            position: sticky;
            top: 0;
        }
        
        .instructions-card {
            background: #f8f9fa;
            border-left: 4px solid #15d403;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #15d403;
            color: #020201;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .progress-container {
            margin-top: 30px;
        }
        
        .progress-bar-custom {
            height: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            border-radius: 5px;
            transition: width 0.3s;
        }
        
        @keyframes progressAnimation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .progress-fill.animated {
            background: linear-gradient(90deg, 
                #15d403 0%, 
                #fcef03 25%, 
                #15d403 50%, 
                #fcef03 75%, 
                #15d403 100%);
            background-size: 400% 100%;
            animation: progressAnimation 2s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0">
                                <i class="bi bi-upload"></i>
                                Importar Participantes desde CSV
                            </h1>
                            <p class="mb-0 opacity-75">Evento: {{ $event->nombre }} ({{ $event->codigo_evento }})</p>
                        </div>
                        <a href="{{ route('admin.events.show', $event) }}" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i>
                            Volver al Evento
                        </a>
                    </div>
                </div>



                <!-- Display Session Errors -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Error:</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>¡Éxito!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Advertencia:</strong> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Información:</strong> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Por favor, corrige los siguientes errores:</strong>
                        <ul class="error-list mt-2 mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Import Errors Summary -->
                @if(session('import_errors'))
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Problemas durante la importación:</strong>
                        <ul class="error-list mt-2 mb-0">
                            @foreach(session('import_errors') as $importError)
                                <li>{{ $importError }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Instructions 
                <div class="instructions-card">
                    <h5 class="mb-3">
                        <i class="bi bi-info-circle"></i>
                        Instrucciones para la Importación
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <span class="step-number">1</span>
                                <strong>Prepara tu archivo CSV</strong>
                                <p class="mb-0 small">El archivo debe tener las siguientes columnas en este orden:</p>
                                <ul class="small mb-0">
                                    <li><code>nombre</code> (Nombre del participante)</li>
                                    <li><code>apellido</code> (Apellido del participante)</li>
                                    <li><code>email</code> (Correo electrónico del participante)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <span class="step-number">2</span>
                                <strong>Formato requerido</strong>
                                <p class="mb-0 small">El archivo debe cumplir con estos requisitos:</p>
                                <ul class="small mb-0">
                                    <li>Formato CSV con codificación UTF-8</li>
                                    <li>Separador de columnas: coma (,)</li>
                                    <li>Delimitador de texto: comillas dobles (")</li>
                                    <li>Primera fila como encabezados</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <span class="step-number">3</span>
                                <strong>Proceso automático</strong>
                                <p class="mb-0 small">Al importar, el sistema realizará:</p>
                                <ul class="small mb-0">
                                    <li>Validación de datos y formatos</li>
                                    <li>Generación automática de 7 tickets por participante</li>
                                    <li>Asignación al evento actual</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <span class="step-number">4</span>
                                <strong>Resultados esperados</strong>
                                <p class="mb-0 small">Por cada participante importado:</p>
                                <ul class="small mb-0">
                                    <li>1 registro en la tabla de participantes</li>
                                    <li>7 tickets generados automáticamente</li>
                                    <li>7 PDFs disponibles para generación</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                -->
                
                <!-- Import Form -->
                <div class="import-container">
                    <form action="{{ route('admin.events.import', $event) }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- File Upload -->
                                <div class="mb-4">
                                    <label for="csv_file" class="form-label fw-semibold">
                                        <i class="bi bi-file-earmark-spreadsheet"></i>
                                        Seleccionar Archivo CSV
                                    </label>
                                    <div class="upload-area" id="uploadArea">
                                        <div class="upload-icon">
                                            <i class="bi bi-cloud-upload"></i>
                                        </div>
                                        <h5>Arrastra y suelta tu archivo CSV aquí</h5>
                                        <p class="text-muted">o haz clic para seleccionar</p>
                                        <input type="file" class="form-control d-none" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                                        <button type="button" class="btn btn-primary mt-3" id="browseButton">
                                            <i class="bi bi-folder2-open"></i>
                                            Buscar Archivo
                                        </button>
                                    </div>
                                    <div class="file-info d-none" id="fileInfo">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-file-earmark-spreadsheet"></i>
                                                <span id="fileName" class="fw-semibold"></span>
                                                <span id="fileSize" class="text-muted ms-2"></span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('csv_file')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- CSV Preview -->
                                <div class="csv-preview d-none" id="csvPreview">
                                    <h6 class="mb-3">
                                        <i class="bi bi-eye"></i>
                                        Vista Previa (primeras 5 filas)
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm preview-table" id="previewTable">
                                            <thead>
                                                <tr id="previewHeaders"></tr>
                                            </thead>
                                            <tbody id="previewRows"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Import Options -->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary" id="importButton" disabled>
                                                <i class="bi bi-upload"></i>
                                                Iniciar Importación
                                            </button>
                                            <a href="{{ asset('templates/participantes_template.csv') }}" class="btn btn-outline-secondary" download>
                                                <i class="bi bi-download"></i>
                                                Descargar Plantilla
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Progress Container -->
                    <div class="progress-container d-none" id="progressContainer">
                        <h6 class="mb-3">
                            <i class="bi bi-hourglass-split"></i>
                            Procesando Importación
                        </h6>
                        <div class="progress-bar-custom mb-2">
                            <div class="progress-fill" id="progressBar" style="width: 0%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span id="progressText">0% completado</span>
                            <span id="processedRows">0 de 0 filas procesadas</span>
                        </div>
                        <div class="alert alert-info mt-3 mb-0" id="progressMessage">
                            <i class="bi bi-info-circle"></i>
                            La importación está en progreso. Por favor, no cierre esta ventana.
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // DOM Elements
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('csv_file');
        const browseButton = document.getElementById('browseButton');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const removeFileButton = document.getElementById('removeFile');
        const csvPreview = document.getElementById('csvPreview');
        const previewHeaders = document.getElementById('previewHeaders');
        const previewRows = document.getElementById('previewRows');
        const importButton = document.getElementById('importButton');
        const importForm = document.getElementById('importForm');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const processedRows = document.getElementById('processedRows');
        const progressMessage = document.getElementById('progressMessage');

        // File handling
        browseButton.addEventListener('click', () => fileInput.click());
        
        uploadArea.addEventListener('click', (e) => {
            if (e.target !== browseButton && e.target !== browseButton.querySelector('i')) {
                fileInput.click();
            }
        });

        fileInput.addEventListener('change', handleFileSelect);
        removeFileButton.addEventListener('click', removeFile);

        // Drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadArea.classList.add('dragover');
        }

        function unhighlight() {
            uploadArea.classList.remove('dragover');
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            handleFileSelect();
        }

        function handleFileSelect() {
            const file = fileInput.files[0];
            if (!file) return;

            // Validate file type
            if (!file.name.toLowerCase().endsWith('.csv') && !file.name.toLowerCase().endsWith('.txt')) {
                alert('Por favor, selecciona un archivo CSV o TXT.');
                removeFile();
                return;
            }

            // Update file info
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.classList.remove('d-none');
            importButton.disabled = false;

            // Preview CSV
            previewCSV(file);
        }

        function removeFile() {
            fileInput.value = '';
            fileInfo.classList.add('d-none');
            csvPreview.classList.add('d-none');
            importButton.disabled = true;
            previewHeaders.innerHTML = '';
            previewRows.innerHTML = '';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function previewCSV(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const content = e.target.result;
                const delimiter = document.getElementById('delimiter').value;
                const rows = content.split('\n').slice(0, 6); // First 6 rows (header + 5 data rows)
                
                // Clear previous preview
                previewHeaders.innerHTML = '';
                previewRows.innerHTML = '';
                
                // Parse and display rows
                rows.forEach((row, index) => {
                    const columns = row.split(delimiter);
                    if (index === 0) {
                        // Header row
                        columns.forEach(column => {
                            const th = document.createElement('th');
                            th.textContent = column.trim().replace(/"/g, '');
                            previewHeaders.appendChild(th);
                        });
                    } else if (row.trim()) {
                        // Data row
                        const tr = document.createElement('tr');
                        columns.forEach(column => {
                            const td = document.createElement('td');
                            td.textContent = column.trim().replace(/"/g, '');
                            tr.appendChild(td);
                        });
                        previewRows.appendChild(tr);
                    }
                });
                
                csvPreview.classList.remove('d-none');
            };
            reader.readAsText(file);
        }

        // Delimiter change
        document.getElementById('delimiter').addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                previewCSV(fileInput.files[0]);
            }
        });

        // Form submission - show loading state but allow normal submission
        importForm.addEventListener('submit', function(e) {
            console.log('Form submission started');
            
            // Show progress container with loading message
            if (progressContainer) progressContainer.classList.remove('d-none');
            if (importForm) importForm.classList.add('d-none');
            
            // Update progress message to show real processing
            if (progressMessage) {
                progressMessage.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando archivo CSV. Por favor, espere...';
            }
            
            // Show indeterminate progress animation
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.classList.add('animated');
            }
            if (progressText) progressText.textContent = 'Procesando...';
            if (processedRows) processedRows.textContent = 'Leyendo archivo CSV';
            
            // The form will submit normally, showing real server-side processing
            // No need to prevent default or simulate progress
            console.log('Form will submit normally');
        });

        // Activar el elemento activo en el sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
