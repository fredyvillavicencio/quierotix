<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CsvImportService
{
    /**
     * Importa participantes desde un archivo CSV
     */
    public function importParticipants(Event $event, UploadedFile $file): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'participants' => [],
        ];

        // Leer el archivo CSV
        $csvData = $this->readCsvFile($file);
        
        if (empty($csvData)) {
            throw new \Exception('El archivo CSV está vacío o no tiene el formato correcto');
        }

        // Procesar cada fila
        foreach ($csvData as $rowNumber => $row) {
            try {
                $participant = $this->processRow($event, $row, $rowNumber);
                $results['participants'][] = $participant;
                $results['success']++;
            } catch (ValidationException $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'errors' => $e->errors(),
                    'data' => $row,
                ];
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'errors' => [$e->getMessage()],
                    'data' => $row,
                ];
            }
        }

        return $results;
    }

    /**
     * Lee el archivo CSV y devuelve los datos como array
     */
    private function readCsvFile(UploadedFile $file): array
    {
        $data = [];
        $handle = fopen($file->getPathname(), 'r');
        
        if ($handle === false) {
            throw new \Exception('No se pudo abrir el archivo CSV');
        }

        // Leer encabezados
        $headers = fgetcsv($handle);
        
        if ($headers === false) {
            fclose($handle);
            throw new \Exception('El archivo CSV no tiene encabezados válidos');
        }

        // Normalizar encabezados (minúsculas, sin espacios)
        $headers = array_map(function ($header) {
            return strtolower(trim($header));
        }, $headers);

        // Leer filas de datos
        while (($row = fgetcsv($handle)) !== false) {
            // Saltar filas vacías
            if (empty(array_filter($row))) {
                continue;
            }

            // Combinar encabezados con valores
            $rowData = array_combine($headers, $row);
            $data[] = $rowData;
        }

        fclose($handle);
        return $data;
    }

    /**
     * Procesa una fila del CSV
     */
    private function processRow(Event $event, array $row, int $rowNumber): Participant
    {
        // Validar datos de la fila
        $validator = Validator::make($row, [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ], [
            'nombre.required' => 'El nombre es requerido',
            'apellido.required' => 'El apellido es requerido',
            'email.required' => 'El email es requerido',
            'email.email' => 'El email debe ser válido',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Crear el participante
        $participant = Participant::create([
            'event_id' => $event->id,
            'nombre' => trim($row['nombre']),
            'apellido' => trim($row['apellido']),
            'email' => trim($row['email']),
            'pdf_paths' => null,
        ]);

        // Asignar asientos al participante
        $seatService = new SeatAssignmentService();
        $seatService->assignSeatsToParticipant($participant);

        return $participant;
    }

    /**
     * Valida el archivo CSV antes de importar
     */
    public function validateCsvFile(UploadedFile $file): array
    {
        $errors = [];

        // Validar extensión
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['csv', 'txt'])) {
            $errors[] = 'El archivo debe ser un CSV (.csv o .txt)';
        }

        // Validar tamaño (máximo 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file->getSize() > $maxSize) {
            $errors[] = 'El archivo no debe exceder los 5MB';
        }

        // Leer encabezados para validar estructura
        try {
            $csvData = $this->readCsvFile($file);
            
            if (empty($csvData)) {
                $errors[] = 'El archivo CSV está vacío';
            } else {
                // Verificar que tenga las columnas requeridas
                $firstRow = $csvData[0];
                $requiredColumns = ['nombre', 'apellido', 'email'];
                
                foreach ($requiredColumns as $column) {
                    if (!isset($firstRow[$column])) {
                        $errors[] = "Falta la columna requerida: {$column}";
                    }
                }
            }
        } catch (\Exception $e) {
            $errors[] = 'Error al leer el archivo CSV: ' . $e->getMessage();
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'row_count' => count($csvData ?? []),
        ];
    }

    /**
     * Genera una plantilla CSV para descargar
     */
    public function generateTemplate(): string
    {
        $headers = ['nombre', 'apellido', 'email'];
        $examples = [
            ['Juan', 'Pérez', 'juan.perez@example.com'],
            ['María', 'Gómez', 'maria.gomez@example.com'],
            ['Carlos', 'Rodríguez', 'carlos.rodriguez@example.com'],
        ];

        $output = fopen('php://temp', 'r+');
        
        // Escribir encabezados
        fputcsv($output, $headers);
        
        // Escribir ejemplos
        foreach ($examples as $example) {
            fputcsv($output, $example);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Exporta participantes a CSV
     */
    public function exportParticipants(Event $event): string
    {
        $participants = Participant::where('event_id', $event->id)
            ->with('tickets')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $headers = [
            'Nombre',
            'Apellido',
            'Email',
            'Asientos Asignados',
            'PDFs Generados',
        ];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);

        foreach ($participants as $participant) {
            $seatCodes = $participant->tickets
                ->map(function ($ticket) {
                    return $ticket->seat_code;
                })
                ->implode(', ');

            $pdfsGenerated = !empty($participant->pdf_paths) ? 'Sí' : 'No';

            fputcsv($output, [
                $participant->nombre,
                $participant->apellido,
                $participant->email,
                $seatCodes,
                $pdfsGenerated,
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}