<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateEventPdfsJob;
use App\Models\Event;
use App\Models\EventLocation;
use App\Models\Participant;
use App\Models\Ticket;
use App\Services\EmailService;
use App\Services\SeatAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        /*
        $events = Event::withCount(['participants', 'tickets'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('codigo_evento', 'like', "%{$search}%")
                      ->orWhere('nombre', 'like', "%{$search}%");
                });
            })
            ->orderBy('fecha', 'desc')
            ->paginate(10);
        */

        $events = Event::withCount(['participants', 'tickets'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('codigo_evento', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%");
                });
            })
            ->orderBy('fecha', 'desc')
            ->paginate(10);

        $events->getCollection()->transform(function ($event) {
            // PDFs generados
            $event->pdfs_generated = Ticket::where('event_id', $event->id)
                ->whereNotNull('archivo_pdf')
                ->where('archivo_pdf', '!=', '')
                ->count();

            // Emails enviados
            $event->emails_sent = Participant::where('event_id', $event->id)
                ->where('email_sent', true)
                ->count();

            return $event;
        });

        // Statistics
        $totalEvents = Event::count();
        $activeEvents = Event::where('fecha', '>=', now()->format('Y-m-d'))->count();
        $totalParticipants = Participant::count();
        $totalTickets = Ticket::count();

        return view('admin.events.index', compact(
            'events',
            'totalEvents',
            'activeEvents',
            'totalParticipants',
            'totalTickets'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_evento' => 'required|string|max:50|unique:events',
            'recurrente_product_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('events', 'recurrente_product_id'),
            ],
            'recurrente_product_url' => 'nullable|url|max:2048',
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'lugar' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'precio' => 'required|numeric|min:0|max:99999999.99',
            'imagen' => 'nullable|image',
            'imagen_ticket' => 'nullable|image',
            'imagen_cover' => 'nullable|image',
        ]);

        $locations = $this->extractLocations($request);
        $eventData = Arr::except($validated, ['imagen', 'imagen_ticket', 'imagen_cover']);

        $event = DB::transaction(function () use ($eventData, $locations) {
            $event = Event::create($eventData);
            $this->syncEventLocations($event, $locations);

            return $event;
        });

        // Procesar imagen principal si se subió
        if ($request->hasFile('imagen')) {
            $this->processImage($request->file('imagen'), $event, 'imagen');
        }

        // Procesar imagen de ticket si se subió
        if ($request->hasFile('imagen_ticket')) {
            $this->processImage($request->file('imagen_ticket'), $event, 'imagen_ticket');
        }

        // Procesar imagen cover de la página de compra si se subió
        if ($request->hasFile('imagen_cover')) {
            $this->processImage($request->file('imagen_cover'), $event, 'imagen_cover');
        }

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Evento creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->loadCount(['participants', 'tickets']);
        $pdfsGenerated = $event->tickets()->whereRaw("archivo_pdf IS NOT NULL AND archivo_pdf != ''")->count();
        $participants = $event->participants()->oldest()->paginate(10);
        $tickets = $event->tickets()->orderBy('id', 'asc')->paginate(10);

        $totalEmailSent = Participant::where('event_id', $event->id)
            ->where('email_sent', true)
            ->count();

        return view('admin.events.show', compact('event', 'participants', 'tickets', 'pdfsGenerated', 'totalEmailSent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $event->load('locations');

        $soldByLocation = Ticket::where('event_id', $event->id)
            ->select('locacion', DB::raw('COUNT(*) as total'))
            ->groupBy('locacion')
            ->pluck('total', 'locacion');

        $event->locations->each(function (EventLocation $location) use ($soldByLocation) {
            $location->setAttribute('vendidos', (int) ($soldByLocation[$location->nombre] ?? 0));
        });

        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'codigo_evento' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('events')->ignore($event->id),
            ],
            'recurrente_product_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('events', 'recurrente_product_id')->ignore($event->id),
            ],
            'recurrente_product_url' => 'nullable|url|max:2048',
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'hora' => 'required',
            'lugar' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'precio' => 'required|numeric|min:0|max:99999999.99',
            'imagen' => 'nullable|image',
            'imagen_ticket' => 'nullable|image',
            'imagen_cover' => 'nullable|image',
        ]);

        $locations = $this->extractLocations($request);
        $eventData = Arr::except($validated, ['imagen', 'imagen_ticket', 'imagen_cover']);

        DB::transaction(function () use ($event, $eventData, $locations) {
            $event->update($eventData);
            $this->syncEventLocations($event, $locations);
        });

        // Procesar imagen principal si se subió o se solicita eliminar
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($event->imagen && file_exists(public_path($event->imagen))) {
                unlink(public_path($event->imagen));
            }
            $this->processImage($request->file('imagen'), $event, 'imagen');
        } elseif ($request->has('eliminar_imagen') && $request->eliminar_imagen == '1') {
            // Eliminar imagen si se marcó el checkbox
            if ($event->imagen && file_exists(public_path($event->imagen))) {
                unlink(public_path($event->imagen));
            }
            $event->update(['imagen' => null]);
        }

        // Procesar imagen de ticket si se subió o se solicita eliminar
        if ($request->hasFile('imagen_ticket')) {
            // Eliminar imagen anterior si existe
            if ($event->imagen_ticket && file_exists(public_path($event->imagen_ticket))) {
                unlink(public_path($event->imagen_ticket));
            }
            $this->processImage($request->file('imagen_ticket'), $event, 'imagen_ticket');
        } elseif ($request->has('eliminar_imagen_ticket') && $request->eliminar_imagen_ticket == '1') {
            // Eliminar imagen si se marcó el checkbox
            if ($event->imagen_ticket && file_exists(public_path($event->imagen_ticket))) {
                unlink(public_path($event->imagen_ticket));
            }
            $event->update(['imagen_ticket' => null]);
        }

        // Procesar imagen cover de la página de compra si se subió o se solicita eliminar
        if ($request->hasFile('imagen_cover')) {
            // Eliminar imagen anterior si existe
            if ($event->imagen_cover && file_exists(public_path($event->imagen_cover))) {
                unlink(public_path($event->imagen_cover));
            }
            $this->processImage($request->file('imagen_cover'), $event, 'imagen_cover');
        } elseif ($request->has('eliminar_imagen_cover') && $request->eliminar_imagen_cover == '1') {
            // Eliminar imagen si se marcó el checkbox
            if ($event->imagen_cover && file_exists(public_path($event->imagen_cover))) {
                unlink(public_path($event->imagen_cover));
            }
            $event->update(['imagen_cover' => null]);
        }

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Evento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        // Check if event has participants or tickets
        if ($event->participants_count > 0 || $event->tickets_count > 0) {
            return redirect()
                ->route('admin.events.index')
                ->with('error', 'No se puede eliminar el evento porque tiene participantes o tickets asociados.');
        }

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Evento eliminado exitosamente.');
    }

    /**
     * Show import form for participants.
     */
    public function showImportForm(Event $event)
    {
        return view('admin.events.import', compact('event'));
    }

    /**
     * Show import form for fotografos.
     */
    public function showImportFotografosForm(Event $event)
    {
        return view('admin.events.import-fotografos', compact('event'));
    }

    /**
     * Import participants from CSV.
     */
    public function importParticipants(Request $request, Event $event)
    {
        $file = $request->file('csv_file');
        $importMode = $request->input('import_mode', 'add');
        $generateTickets = $request->boolean('generate_tickets', true);
        $delimiter = $request->input('delimiter', ',');

        // Start database transaction
        DB::beginTransaction();

        try {
            // If replace mode, delete existing participants and tickets
            if ($importMode === 'replace') {
                $event->tickets()->delete();
                $event->participants()->delete();
            }

            // Process CSV file
            $path = $file->getRealPath();

            // Check if file is empty
            if (filesize($path) === 0) {
                throw new \Exception('El archivo CSV está vacío.');
            }

            $handle = fopen($path, 'r');

            if (! $handle) {
                throw new \Exception('No se pudo abrir el archivo CSV.');
            }

            $importedCount = 0;
            $errors = [];
            $rowNumber = 0;

            // Read CSV file
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $rowNumber++;

                // Skip header row
                if ($rowNumber === 1) {
                    continue;
                }

                // Validate row has required columns
                if (count($row) < 3) {
                    $errors[] = "Fila {$rowNumber}: No tiene suficientes columnas (se esperaban 3, se encontraron ".count($row).')';

                    continue;
                }

                // Extract data - manejar diferentes formatos
                // Formato 1: nombre,apellido,email (estándar)
                // Formato 2: nombre_completo,apellidos_completos,email (como en prueba_1.csv)

                $carne = trim($row[0] ?? '');
                $nombre = trim($row[1] ?? '');
                $apellido = trim($row[2] ?? '');
                $email = trim($row[3] ?? '');

                // Si el nombre está vacío pero tenemos datos, intentar parsear de otra manera
                if (empty($nombre) && ! empty($row[1])) {
                    // Intentar dividir por el primer espacio
                    $parts = explode(' ', $row[1], 2);
                    if (count($parts) >= 2) {
                        $nombre = trim($parts[0]);
                        $apellido = trim($parts[1]).' '.trim($row[2] ?? '');
                    }
                }

                // Validate required fields
                if (empty($nombre) || empty($email)) {
                    $errors[] = "Fila {$rowNumber}: Campos requeridos vacíos (nombre o email)";

                    continue;
                }

                // Si el apellido está vacío, usar "No especificado"
                if (empty($apellido)) {
                    $apellido = 'No especificado';
                }

                // Validate email format
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Fila {$rowNumber}: Email inválido: {$email}";

                    continue;
                }

                try {
                    // Create participant
                    $participant = Participant::create([
                        'event_id' => $event->id,
                        'carne' => $carne,
                        'nombre' => $nombre,
                        'apellido' => $apellido,
                        'email' => $email,
                        'pdf_paths' => '[]', // JSON string instead of array
                    ]);

                    // Generate tickets if enabled
                    if ($generateTickets) {
                        $this->generateTicketsForParticipant($participant);
                    }

                    $importedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Fila {$rowNumber}: Error al crear participante - ".$e->getMessage();
                }
            }

            fclose($handle);

            // If no participants were imported and there were no errors, check if file was valid
            if ($importedCount === 0 && empty($errors)) {
                throw new \Exception('El archivo CSV no contiene datos válidos o el formato es incorrecto.');
            }

            // Commit transaction
            DB::commit();

            // Prepare success message
            $message = 'Importación completada exitosamente. ';
            $message .= "Importados: {$importedCount} participantes. ";

            if (! empty($errors)) {
                $message .= 'Errores: '.count($errors).'. ';

                // Store errors in session for debugging (limit to 10 errors)
                $errorDetails = array_slice($errors, 0, 10);
                session()->flash('import_errors', $errorDetails);

                // Also store in log
                \Log::warning('Errores de importación CSV', [
                    'event_id' => $event->id,
                    'errors' => $errorDetails,
                ]);
            }

            // Log successful import
            \Log::info('Importación CSV completada', [
                'event_id' => $event->id,
                'imported' => $importedCount,
                'errors' => count($errors),
            ]);

            return redirect()
                ->route('admin.events.show', $event)
                ->with('success', $message);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            \Log::error('Error en importación CSV', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.events.import.form', $event)
                ->with('error', 'Error en la importación: '.$e->getMessage());
        }
    }

    /**
     * Import fotografos from CSV.
     */
    public function importFotografos(Request $request, Event $event)
    {
        $file = $request->file('csv_file');
        $importMode = $request->input('import_mode', 'add');
        $generateTickets = $request->boolean('generate_tickets', true);
        $delimiter = $request->input('delimiter', ',');

        // Start database transaction
        DB::beginTransaction();

        try {
            // If replace mode, delete existing participants and tickets
            if ($importMode === 'replace') {
                $event->tickets()->delete();
                $event->participants()->delete();
            }

            // Process CSV file
            $path = $file->getRealPath();

            // Check if file is empty
            if (filesize($path) === 0) {
                throw new \Exception('El archivo CSV está vacío.');
            }

            $handle = fopen($path, 'r');

            if (! $handle) {
                throw new \Exception('No se pudo abrir el archivo CSV.');
            }

            $importedCount = 0;
            $errors = [];
            $rowNumber = 0;

            // Read CSV file
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $rowNumber++;

                // Skip header row
                if ($rowNumber === 1) {
                    continue;
                }

                // Validate row has required columns
                if (count($row) < 3) {
                    $errors[] = "Fila {$rowNumber}: No tiene suficientes columnas (se esperaban 3, se encontraron ".count($row).')';

                    continue;
                }

                // Extract data - manejar diferentes formatos
                // Formato 1: nombre,apellido,email (estándar)
                // Formato 2: nombre_completo,apellidos_completos,email (como en prueba_1.csv)

                $nombre = trim($row[0] ?? '');
                $apellido = trim($row[1] ?? '');
                $email = trim($row[2] ?? '');

                // Si el nombre está vacío pero tenemos datos, intentar parsear de otra manera
                if (empty($nombre) && ! empty($row[0])) {
                    // Intentar dividir por el primer espacio
                    $parts = explode(' ', $row[0], 2);
                    if (count($parts) >= 2) {
                        $nombre = trim($parts[0]);
                        $apellido = trim($parts[1]).' '.trim($row[1] ?? '');
                    }
                }

                // Validate required fields
                if (empty($nombre) || empty($email)) {
                    $errors[] = "Fila {$rowNumber}: Campos requeridos vacíos (nombre o email)";

                    continue;
                }

                // Si el apellido está vacío, usar "No especificado"
                if (empty($apellido)) {
                    $apellido = 'No especificado';
                }

                // Validate email format
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Fila {$rowNumber}: Email inválido: {$email}";

                    continue;
                }

                try {
                    // Create participant
                    $participant = Participant::create([
                        'event_id' => $event->id,
                        'nombre' => $nombre,
                        'apellido' => $apellido,
                        'email' => $email,
                        'pdf_paths' => '[]', // JSON string instead of array
                    ]);

                    // Generate tickets if enabled
                    if ($generateTickets) {
                        $this->generateTicketsForFotografos($participant);
                    }

                    $importedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Fila {$rowNumber}: Error al crear participante - ".$e->getMessage();
                }
            }

            fclose($handle);

            // If no participants were imported and there were no errors, check if file was valid
            if ($importedCount === 0 && empty($errors)) {
                throw new \Exception('El archivo CSV no contiene datos válidos o el formato es incorrecto.');
            }

            // Commit transaction
            DB::commit();

            // Prepare success message
            $message = 'Importación completada exitosamente. ';
            $message .= "Importados: {$importedCount} participantes. ";

            if (! empty($errors)) {
                $message .= 'Errores: '.count($errors).'. ';

                // Store errors in session for debugging (limit to 10 errors)
                $errorDetails = array_slice($errors, 0, 10);
                session()->flash('import_errors', $errorDetails);

                // Also store in log
                \Log::warning('Errores de importación CSV', [
                    'event_id' => $event->id,
                    'errors' => $errorDetails,
                ]);
            }

            // Log successful import
            \Log::info('Importación CSV completada', [
                'event_id' => $event->id,
                'imported' => $importedCount,
                'errors' => count($errors),
            ]);

            return redirect()
                ->route('admin.events.show', $event)
                ->with('success', $message);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            \Log::error('Error en importación CSV', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.events.import-fotografos.form', $event)
                ->with('error', 'Error en la importación: '.$e->getMessage());
        }
    }

    /**
     * Generate tickets for a participant using SeatAssignmentService.
     */
    private function generateTicketsForParticipant(Participant $participant)
    {
        try {
            $seatAssignmentService = new SeatAssignmentService;
            $tickets = $seatAssignmentService->assignSeatsToParticipant($participant);

            \Log::info('Tickets generados exitosamente usando SeatAssignmentService', [
                'participant_id' => $participant->id,
                'tickets_count' => count($tickets),
                'first_seat' => $tickets[0]->seat_code,
                'last_seat' => $tickets[count($tickets) - 1]->seat_code,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar tickets usando SeatAssignmentService', [
                'participant_id' => $participant->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function generateTicketsForFotografos(Participant $participant)
    {
        try {
            $seatAssignmentService = new SeatAssignmentService;
            $tickets = $seatAssignmentService->assignSeatsToFotografos($participant);

            \Log::info('Tickets generados exitosamente usando SeatAssignmentService', [
                'participant_id' => $participant->id,
                'tickets_count' => count($tickets),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar tickets usando SeatAssignmentService', [
                'participant_id' => $participant->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate PDFs for event tickets.
     */
    public function generatePdfs(Request $request, Event $event)
    {
        try {
            // Generar ID único para este trabajo
            $jobId = uniqid('pdf_generation_', true);

            // Crear entrada inicial en caché antes de despachar el trabajo
            Cache::put("pdf_generation_{$jobId}", [
                'status' => 'processing',
                'percentage' => 0,
                'message' => 'Iniciando generación de PDFs...',
                'updated_at' => now()->toDateTimeString(),
            ], 3600); // 1 hora de expiración

            // Dispatch the job
            GenerateEventPdfsJob::dispatch($event, $jobId);

            // Redirigir a la página de progreso
            return redirect()
                ->route('admin.events.pdf-generation.progress', ['event' => $event, 'jobId' => $jobId])
                ->with('info', 'Generación de PDFs iniciada. Redirigiendo a la página de progreso...');

        } catch (\Exception $e) {
            \Log::error('Error al iniciar generación de PDFs', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.events.show', $event)
                ->with('error', 'Error al iniciar la generación de PDFs: '.$e->getMessage());
        }
    }

    /**
     * Show PDF generation progress page.
     */
    public function showPdfGenerationProgress(Event $event, string $jobId)
    {
        return view('admin.events.pdf-generation-progress', compact('event', 'jobId'));
    }

    /**
     * Get PDF generation progress (API endpoint).
     */
    public function getPdfGenerationProgress(Event $event, string $jobId)
    {
        $progress = Cache::get("pdf_generation_{$jobId}");

        if (! $progress) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Progreso no encontrado. El trabajo puede haber expirado.',
                'percentage' => 0,
            ]);
        }

        return response()->json($progress);
    }

    /**
     * Send emails to participants.
     */
    public function sendEmails(Request $request, Event $event)
    {
        try {
            // Usar el EmailService para enviar emails a todos los participantes
            $emailService = new EmailService;
            $results = $emailService->sendEventEmails($event);

            // Verificar si hubo errores
            if ($results['emails_failed'] > 0) {
                $message = 'Envío de emails completado con algunos errores. ';
                $message .= "Enviados: {$results['emails_sent']}, Fallados: {$results['emails_failed']} de {$results['total_participants']} participantes.";

                // Si hay muchos errores, mostrar solo el primero para no saturar la sesión
                if (! empty($results['errors'])) {
                    $firstError = $results['errors'][0];
                    $message .= ' Primer error: '.$firstError['error'];
                }

                return redirect()
                    ->route('admin.events.show', $event)
                    ->with('warning', $message);
            }

            // Éxito completo
            $message = 'Envío de correos exitoso. ';
            $message .= "Se enviaron {$results['emails_sent']} emails a {$results['total_participants']} participantes.";

            return redirect()
                ->route('admin.events.show', $event)
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Error al enviar emails masivos', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.events.show', $event)
                ->with('error', 'Error al enviar correos: '.$e->getMessage());
        }
    }

    /**
     * Show event statistics.
     */
    public function statistics(Event $event)
    {
        $event->loadCount(['participants', 'tickets']);

        // Obtener estadísticas por locación
        $locationsStatistics = Ticket::where('event_id', $event->id)
            ->select('locacion',
                DB::raw('COUNT(*) as total_tickets'),
                DB::raw('SUM(CASE WHEN validado = 1 THEN 1 ELSE 0 END) as validated_tickets')
            )
            ->groupBy('locacion')
            ->orderBy('locacion')
            ->get();

        // Calcular estadísticas generales
        $totalTickets = $event->tickets_count;
        $totalValidatedTickets = Ticket::where('event_id', $event->id)
            ->where('validado', true)
            ->count();
        $validationPercentage = $totalTickets > 0
            ? round(($totalValidatedTickets / $totalTickets) * 100, 1)
            : 0;

        // Obtener PDFs generados
        $pdfsGenerated = Ticket::where('event_id', $event->id)
            ->whereNotNull('archivo_pdf')
            ->count();

        return view('admin.events.statistics', compact(
            'event',
            'locationsStatistics',
            'totalTickets',
            'totalValidatedTickets',
            'validationPercentage',
            'pdfsGenerated'
        ));
    }

    /**
     * Show seat map for event.
     */
    public function seatMap(Event $event)
    {
        $seatAssignmentService = new SeatAssignmentService;
        $seatStats = $seatAssignmentService->getSeatStatistics($event);

        return view('admin.events.seat-map', [
            'event' => $event,
            'locations' => $seatStats['locations'],
        ]);
    }

    /**
     * Extrae y valida las locaciones enviadas desde el formulario.
     *
     * @return array<int, array{id: int|null, nombre: string, precio: float, total_asientos: int}>
     */
    private function extractLocations(Request $request): array
    {
        $rows = $request->input('locaciones', []);

        if (! is_array($rows)) {
            throw ValidationException::withMessages([
                'locaciones' => 'El formato de las locaciones no es válido.',
            ]);
        }

        $locations = [];
        $seenNames = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $id = isset($row['id']) && is_numeric($row['id']) && (int) $row['id'] > 0
                ? (int) $row['id']
                : null;
            $name = trim((string) ($row['nombre'] ?? ''));
            $price = $row['precio'] ?? '';
            $totalSeats = $row['total_asientos'] ?? '';

            $isEmpty = $id === null
                && $name === ''
                && ($price === '' || $price === null)
                && ($totalSeats === '' || $totalSeats === null);

            if ($isEmpty) {
                continue;
            }

            if ($name === '') {
                $errors["locaciones.{$index}.nombre"] = 'El nombre de la locación es obligatorio.';
            } elseif (mb_strlen($name) > 100) {
                $errors["locaciones.{$index}.nombre"] = 'El nombre no puede superar los 100 caracteres.';
            } elseif (mb_strtolower($name) === mb_strtolower(Ticket::PHOTOGRAPHER_LOCATION)) {
                $errors["locaciones.{$index}.nombre"] = 'Ese nombre está reservado para la acreditación de fotógrafos.';
            } else {
                $normalizedName = mb_strtolower($name);

                if (isset($seenNames[$normalizedName])) {
                    $errors["locaciones.{$index}.nombre"] = 'No puedes repetir el nombre de una locación.';
                }

                $seenNames[$normalizedName] = true;
            }

            if (! is_numeric($price) || (float) $price < 0 || (float) $price > 99999999.99) {
                $errors["locaciones.{$index}.precio"] = 'El precio debe ser un número mayor o igual a 0.';
            }

            if (! is_numeric($totalSeats) || (int) $totalSeats < 1 || (int) $totalSeats > 1000000) {
                $errors["locaciones.{$index}.total_asientos"] = 'La cantidad de asientos debe ser mayor a 0.';
            }

            $locations[] = [
                'id' => $id,
                'nombre' => $name,
                'precio' => round((float) $price, 2),
                'total_asientos' => (int) $totalSeats,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $locations;
    }

    /**
     * Crea, actualiza y elimina locaciones respetando los tickets vendidos.
     *
     * @param  array<int, array{id: int|null, nombre: string, precio: float, total_asientos: int}>  $locations
     */
    private function syncEventLocations(Event $event, array $locations): void
    {
        $existing = $event->locations()->get()->keyBy('id');
        $soldByLocation = Ticket::where('event_id', $event->id)
            ->select('locacion', DB::raw('COUNT(*) as total'))
            ->groupBy('locacion')
            ->pluck('total', 'locacion');

        $errors = [];
        $keptIds = [];

        foreach ($locations as $index => $row) {
            $location = $row['id'] !== null ? $existing->get($row['id']) : null;

            if ($row['id'] !== null && $location === null) {
                $errors["locaciones.{$index}.id"] = 'La locación indicada no pertenece a este evento.';

                continue;
            }

            $oldName = $location?->nombre;
            $sold = (int) ($soldByLocation[$oldName ?? $row['nombre']] ?? 0);

            if ($row['total_asientos'] < $sold) {
                $errors["locaciones.{$index}.total_asientos"] =
                    "No puedes reducir la capacidad por debajo de los {$sold} tickets ya vendidos.";
            }

            $duplicate = $event->locations()
                ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($row['nombre'])])
                ->when($location !== null, fn ($query) => $query->where('id', '!=', $location->id))
                ->exists();

            if ($duplicate) {
                $errors["locaciones.{$index}.nombre"] = 'Ya existe una locación con ese nombre en el evento.';

                continue;
            }

            if ($location !== null) {
                $location->update([
                    'nombre' => $row['nombre'],
                    'precio' => $row['precio'],
                    'total_asientos' => $row['total_asientos'],
                ]);

                if ($oldName !== $row['nombre'] && $sold > 0) {
                    Ticket::where('event_id', $event->id)
                        ->where('locacion', $oldName)
                        ->update(['locacion' => $row['nombre']]);
                }

                $keptIds[] = $location->id;

                continue;
            }

            $created = $event->locations()->create([
                'nombre' => $row['nombre'],
                'precio' => $row['precio'],
                'total_asientos' => $row['total_asientos'],
            ]);

            $keptIds[] = $created->id;
        }

        foreach ($existing as $location) {
            if (in_array($location->id, $keptIds, true)) {
                continue;
            }

            $sold = (int) ($soldByLocation[$location->nombre] ?? 0);

            if ($sold > 0) {
                $errors['locaciones'] =
                    "No puedes eliminar la locación \"{$location->nombre}\" porque tiene {$sold} tickets vendidos.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        foreach ($existing as $location) {
            if (! in_array($location->id, $keptIds, true)) {
                $location->delete();
            }
        }
    }

    /**
     * Procesar y guardar una imagen para el evento
     */
    private function processImage($imageFile, Event $event, string $fieldName)
    {
        // Crear directorio si no existe
        $directory = public_path('images/events');
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Obtener extensión del archivo
        $extension = $imageFile->getClientOriginalExtension();

        // Generar nombre del archivo: {id_evento}_{fieldName}.{extension}
        $filename = bin2hex(random_bytes(8)).$event->id.'_'.$fieldName.'.'.$extension;

        // Mover archivo al directorio
        $imageFile->move($directory, $filename);

        // Guardar ruta en la base de datos
        $event->update([
            $fieldName => 'images/events/'.$filename,
        ]);
    }
}
