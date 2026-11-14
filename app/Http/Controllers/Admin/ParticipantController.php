<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Services\EmailService;
use App\Services\SeatAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Construir la consulta base
        $query = Participant::with('event')
            ->withCount('tickets')  // Agregar conteo de tickets
            ->orderBy('created_at', 'desc');

        // Aplicar búsqueda si existe
        if ($request->has('search') && ! empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('apellido', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Paginar resultados
        $participants = $query->paginate(25);

        // Obtener estadísticas para las tarjetas
        $totalParticipants = Participant::count();
        $totalTickets = DB::table('tickets')->count();
        $totalPDFs = DB::table('participants')
            ->select(DB::raw('SUM(JSON_LENGTH(pdf_paths)) as total_pdfs'))
            ->first()->total_pdfs ?? 0;

        // Pasar el término de búsqueda a la vista
        $searchTerm = $request->search ?? '';

        return view('admin.participants.index', compact(
            'participants',
            'totalParticipants',
            'totalTickets',
            'totalPDFs',
            'searchTerm'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener todos los eventos para el select
        $events = Event::orderBy('nombre')->get();

        return view('admin.participants.create', compact('events'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'pdf_paths' => 'nullable|array',
            'pdf_paths.*' => 'string|max:500',
        ]);

        // Crear el participante
        $participant = Participant::create($validated);

        // Generar tickets automáticamente (7 tickets por participante)
        $this->generateTicketsForParticipant($participant);

        return redirect()->route('admin.participants.index')
            ->with('success', 'Participante creado exitosamente. Se generaron 7 tickets automáticamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $participant = Participant::with(['event', 'tickets'])
            ->findOrFail($id);

        return view('admin.participants.show', compact('participant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $participant = Participant::findOrFail($id);
        $events = Event::orderBy('nombre')->get();

        return view('admin.participants.edit', compact('participant', 'events'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $participant = Participant::findOrFail($id);

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'pdf_paths' => 'nullable|array',
            'pdf_paths.*' => 'string|max:500',
        ]);

        $participant->update($validated);

        return redirect()->route('admin.participants.index')
            ->with('success', 'Participante actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $participant = Participant::findOrFail($id);

        // Eliminar tickets relacionados primero
        $participant->tickets()->delete();

        // Eliminar el participante
        $participant->delete();

        return redirect()->route('admin.participants.index')
            ->with('success', 'Participante eliminado exitosamente junto con sus tickets.');
    }

    /**
     * Importar participantes desde CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'event_id' => 'required|exists:events,id',
        ]);

        // Procesar el archivo CSV
        $file = $request->file('csv_file');
        $eventId = $request->event_id;

        // Aquí iría la lógica para procesar el CSV
        // Por ahora, solo un mensaje de éxito

        return redirect()->route('admin.participants.index')
            ->with('success', 'Archivo CSV recibido. La importación se procesará en segundo plano.');
    }

    /**
     * Generar tickets para un participante usando SeatAssignmentService
     */
    private function generateTicketsForParticipant(Participant $participant)
    {
        try {
            $seatAssignmentService = new SeatAssignmentService;
            $tickets = $seatAssignmentService->assignSeatsToParticipant($participant);

            \Log::info('Tickets generados exitosamente usando SeatAssignmentService (ParticipantController)', [
                'participant_id' => $participant->id,
                'tickets_count' => count($tickets),
                'first_seat' => $tickets[0]->seat_code,
                'last_seat' => $tickets[count($tickets) - 1]->seat_code,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar tickets usando SeatAssignmentService (ParticipantController)', [
                'participant_id' => $participant->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Exportar participantes a CSV
     */
    public function export()
    {
        $participants = Participant::with('event')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="participantes_'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($participants) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, ['ID', 'Evento', 'Nombre', 'Apellido', 'Email', 'Tickets', 'PDFs', 'Fecha Registro']);

            // Datos
            foreach ($participants as $participant) {
                fputcsv($file, [
                    $participant->id,
                    $participant->event->nombre,
                    $participant->nombre,
                    $participant->apellido,
                    $participant->email,
                    $participant->tickets()->count(),
                    is_array($participant->pdf_paths) ? count($participant->pdf_paths) : 0,
                    $participant->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Enviar correo a un participante específico
     */
    public function sendEmail(Request $request, string $id)
    {
        $participant = Participant::with(['event', 'tickets'])->findOrFail($id);

        // Validar que tenga tickets
        if ($participant->tickets->isEmpty()) {
            return redirect()->route('admin.participants.index')
                ->with('error', 'El participante no tiene tickets asignados.');
        }

        // Validar que tenga PDFs generados
        if (empty($participant->pdf_paths)) {
            return redirect()->route('admin.participants.index')
                ->with('error', 'El participante no tiene PDFs generados.');
        }

        try {
            $emailService = new EmailService;
            $emailService->sendParticipantEmail($participant);

            return redirect()->route('admin.participants.index')
                ->with('success', 'Correo enviado exitosamente a '.$participant->email);

        } catch (\Exception $e) {
            return redirect()->route('admin.participants.index')
                ->with('error', 'Error al enviar correo: '.$e->getMessage());
        }
    }

    public function redirectByEmail(Request $request)
    {

        $participant = Participant::where('email', $request->email)->first();

        if ($participant) {
            return redirect()->route('participants.preview.email', ['participant' => $participant->id]);
        } else {

            $participant = Participant::where('carne', $request->email)->first();

            if ($participant) {

                return redirect()->route('participants.preview.email', ['participant' => $participant->id]);

            } else {

                return "<script>
                        alert('No se encontró ningún alumno registrado con ese email o carné, porfavor comunicarse al WhatsApp de contacto para soporte.');
                        window.history.back();
                    </script>";
            }
        }

    }

    /**
     * Preview del email que se enviará a un participante
     */
    public function previewEmail(string $id)
    {
        $participant = Participant::with(['event', 'tickets'])->findOrFail($id);

        // Validar que tenga tickets
        if ($participant->tickets->isEmpty()) {
            return redirect()->route('admin.participants.index')
                ->with('error', 'El participante no tiene tickets asignados.');
        }

        // Validar que tenga PDFs generados
        if (empty($participant->pdf_paths)) {
            return redirect()->route('admin.participants.index')
                ->with('error', 'El participante no tiene PDFs generados.');
        }

        // Generar URLs de descarga para cada ticket
        $downloadUrls = [];
        foreach ($participant->tickets as $ticket) {
            $downloadUrls[] = route('admin.tickets.download.pdf', $ticket->id);
        }

        // URL para descargar todos los PDFs en ZIP
        $allPdfsUrl = route('admin.participants.download.pdfs', $participant->id);

        // Renderizar la vista de preview
        return view('emails.email', [
            'participant' => $participant,
            'event' => $participant->event,
            'tickets' => $participant->tickets,
            'downloadUrls' => $downloadUrls,
            'allPdfsUrl' => $allPdfsUrl,
        ]);
    }

    public function previewEmailFotografo(string $id)
    {
        $participant = Participant::with(['event', 'tickets'])->findOrFail($id);

        // Validar que tenga tickets
        if ($participant->tickets->isEmpty()) {
            return redirect()->route('admin.participants.index')
                ->with('error', 'El participante no tiene tickets asignados.');
        }

        // Validar que tenga PDFs generados
        if (empty($participant->pdf_paths)) {
            return redirect()->route('admin.participants.index')
                ->with('error', 'El participante no tiene PDFs generados.');
        }

        // Generar URLs de descarga para cada ticket
        $downloadUrls = [];
        foreach ($participant->tickets as $ticket) {
            $downloadUrls[] = route('admin.tickets.download.pdf', $ticket->id);
        }

        // URL para descargar todos los PDFs en ZIP
        $allPdfsUrl = route('admin.participants.download.pdfs', $participant->id);

        // Renderizar la vista de preview
        return view('emails.email_fotografos', [
            'participant' => $participant,
            'event' => $participant->event,
            'tickets' => $participant->tickets,
            'downloadUrls' => $downloadUrls,
            'allPdfsUrl' => $allPdfsUrl,
        ]);
    }

    /**
     * Descargar todos los PDFs de un participante en un archivo ZIP
     */
    public function downloadPdfs(string $id)
    {
        $participant = Participant::with(['event', 'tickets'])->findOrFail($id);

        // Validar que tenga PDFs generados
        if (empty($participant->pdf_paths)) {
            return redirect()->route('admin.participants.index')
                ->with('error', 'El participante no tiene PDFs generados.');
        }

        // Obtener las rutas de PDFs (ya está casteado a array por el modelo)
        $pdfPaths = $participant->pdf_paths;

        if (empty($pdfPaths) || ! is_array($pdfPaths)) {
            return redirect()->route('admin.participants.index')
                ->with('error', 'No se encontraron rutas de PDFs válidas.');
        }

        // Crear un archivo ZIP temporal
        $zipFileName = 'tickets_'.$participant->id.'_'.time().'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        // Asegurar que el directorio temporal existe
        $tempDir = dirname($zipPath);
        if (! file_exists($tempDir)) {
            if (! mkdir($tempDir, 0755, true)) {
                \Log::error('No se pudo crear directorio temporal', [
                    'participant_id' => $participant->id,
                    'temp_dir' => $tempDir,
                ]);

                return redirect()->route('admin.participants.index')
                    ->with('error', 'Error al crear directorio temporal para el archivo ZIP.');
            }
        }

        $zip = new \ZipArchive;
        $zipOpenResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if ($zipOpenResult !== true) {
            \Log::error('Error al abrir archivo ZIP', [
                'participant_id' => $participant->id,
                'zip_path' => $zipPath,
                'zip_error' => $zipOpenResult,
                'zip_status' => $zip->getStatusString(),
            ]);

            return redirect()->route('admin.participants.index')
                ->with('error', 'Error al crear el archivo ZIP. Código: '.$zipOpenResult);
        }

        $filesAdded = 0;
        $missingFiles = [];
        $ticketsByPath = $participant->tickets->keyBy('archivo_pdf');
        $tickets = $participant->tickets->values();

        // Agregar cada PDF al archivo ZIP
        foreach ($pdfPaths as $index => $pdfPath) {
            $ticketNumber = $index + 1;
            $ticket = $ticketsByPath->get($pdfPath) ?? $tickets->get($index);
            $zipEntryName = $ticket?->download_file_name ?? "Ticket_{$ticketNumber}.pdf";

            // Asegurar que la ruta sea relativa a storage/app
            if (is_string($pdfPath)) {
                // Intentar ruta almacenada (sin private/)
                $pdfFullPath = storage_path('app/'.ltrim($pdfPath, '/'));

                // Si no existe, intentar con prefijo private/ (corrección para rutas antiguas)
                if (! file_exists($pdfFullPath)) {
                    $alternativePath = 'private/'.ltrim($pdfPath, '/');
                    $pdfFullPath = storage_path('app/'.$alternativePath);

                    \Log::info('Usando ruta alternativa para PDF', [
                        'participant_id' => $participant->id,
                        'ticket_number' => $ticketNumber,
                        'original_path' => $pdfPath,
                        'alternative_path' => $alternativePath,
                        'file_exists' => file_exists($pdfFullPath),
                    ]);
                }

                if (file_exists($pdfFullPath)) {
                    if ($zip->addFile($pdfFullPath, $zipEntryName)) {
                        $filesAdded++;
                    } else {
                        \Log::warning('Error al agregar PDF al ZIP', [
                            'participant_id' => $participant->id,
                            'pdf_path' => $pdfPath,
                            'full_path' => $pdfFullPath,
                        ]);
                    }
                } else {
                    $missingFiles[] = [
                        'ticket_number' => $ticketNumber,
                        'path' => $pdfPath,
                        'full_path' => $pdfFullPath,
                    ];

                    \Log::warning('PDF no encontrado para participante (ambas rutas)', [
                        'participant_id' => $participant->id,
                        'participant_name' => $participant->nombre.' '.$participant->apellido,
                        'ticket_number' => $ticketNumber,
                        'pdf_path' => $pdfPath,
                        'full_path' => $pdfFullPath,
                        'alternative_path_tried' => isset($alternativePath) ? $alternativePath : null,
                    ]);
                }
            }
        }

        // Cerrar el archivo ZIP
        if (! $zip->close()) {
            \Log::error('Error al cerrar archivo ZIP', [
                'participant_id' => $participant->id,
                'zip_path' => $zipPath,
                'zip_status' => $zip->getStatusString(),
            ]);

            // Intentar eliminar el archivo ZIP corrupto
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            return redirect()->route('admin.participants.index')
                ->with('error', 'Error al finalizar la creación del archivo ZIP.');
        }

        // Verificar que el archivo ZIP se creó correctamente
        if (! file_exists($zipPath)) {
            \Log::error('Archivo ZIP no creado', [
                'participant_id' => $participant->id,
                'zip_path' => $zipPath,
                'files_added' => $filesAdded,
            ]);

            return redirect()->route('admin.participants.index')
                ->with('error', 'El archivo ZIP no se creó correctamente.');
        }

        // Verificar que el archivo ZIP no esté vacío
        if (filesize($zipPath) === 0) {
            \Log::warning('Archivo ZIP creado vacío', [
                'participant_id' => $participant->id,
                'zip_path' => $zipPath,
                'files_added' => $filesAdded,
            ]);

            // Eliminar ZIP vacío
            unlink($zipPath);

            return redirect()->route('admin.participants.index')
                ->with('error', 'No se pudieron agregar archivos PDF al ZIP. Los archivos pueden no existir.');
        }

        if ($filesAdded === 0) {
            // Eliminar ZIP vacío (aunque ya verificamos filesize, por si acaso)
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            $errorMessage = 'No se encontraron archivos PDF válidos para descargar.';
            if (! empty($missingFiles)) {
                $errorMessage .= ' Los archivos PDF parecen no existir en el sistema de archivos.';
            }

            return redirect()->route('admin.participants.index')
                ->with('error', $errorMessage);
        }

        // Si algunos archivos faltaban, registrar advertencia
        if (! empty($missingFiles)) {
            \Log::warning('Algunos PDFs no se encontraron al crear ZIP', [
                'participant_id' => $participant->id,
                'participant_name' => $participant->nombre.' '.$participant->apellido,
                'files_added' => $filesAdded,
                'files_missing' => count($missingFiles),
                'missing_files' => $missingFiles,
            ]);
        }

        \Log::info('ZIP creado exitosamente', [
            'participant_id' => $participant->id,
            'zip_path' => $zipPath,
            'zip_size' => filesize($zipPath),
            'files_added' => $filesAdded,
        ]);

        // Descargar el archivo ZIP
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
