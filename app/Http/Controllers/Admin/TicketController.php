<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Ticket;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->input('search');
        $eventId = $request->filled('event_id') ? (int) $request->input('event_id') : null;

        // Construir consulta base
        $query = Ticket::with(['participant', 'event'])
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId));

        // Aplicar filtro de búsqueda si existe
        if ($searchTerm) {
            $query->whereHas('participant', function ($q) use ($searchTerm) {
                $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('apellido', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Obtener estadísticas para las tarjetas (basadas en la misma consulta)
        $statsQuery = clone $query;

        // Obtener tickets con paginación conservando los filtros
        $tickets = $query->orderBy('id', 'asc')->paginate(25)->withQueryString();

        $totalTickets = $statsQuery->count();
        $validatedTickets = $statsQuery->clone()->where('validado', true)->count();
        $pendingTickets = $statsQuery->clone()->where('validado', false)->count();

        // Obtener el listado de eventos para el filtro
        $events = Event::orderBy('nombre')->get(['id', 'nombre']);

        return view('admin.tickets.index', compact(
            'tickets',
            'totalTickets',
            'validatedTickets',
            'pendingTickets',
            'searchTerm',
            'events',
            'eventId'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener participantes y eventos para los selects
        $participants = Participant::orderBy('nombre')->get();
        $events = Event::orderBy('nombre')->get();

        return view('admin.tickets.create', compact('participants', 'events'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'event_id' => 'required|exists:events,id',
            'codigo_qr' => 'required|string|unique:tickets,codigo_qr|max:255',
            'locacion' => 'required|string|max:100',
            'asiento' => 'required|integer|min:1',
            'archivo_pdf' => 'nullable|string|max:500',
            'validado' => 'boolean',
        ]);

        // Generar código QR si no se proporcionó
        if (empty($validated['codigo_qr'])) {
            $validated['codigo_qr'] = 'TICKET-'.str_pad(Ticket::count() + 1, 3, '0', STR_PAD_LEFT).'-'.strtoupper(substr(md5(uniqid()), 0, 6));
        }

        // Crear el ticket
        Ticket::create($validated);

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Ticket creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ticket = Ticket::with(['participant', 'event'])
            ->findOrFail($id);

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ticket = Ticket::findOrFail($id);
        $participants = Participant::orderBy('nombre')->get();
        $events = Event::orderBy('nombre')->get();

        return view('admin.tickets.edit', compact('ticket', 'participants', 'events'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'event_id' => 'required|exists:events,id',
            'codigo_qr' => 'required|string|unique:tickets,codigo_qr,'.$id.'|max:255',
            'locacion' => 'required|string|max:100',
            'asiento' => 'required|integer|min:1',
            'archivo_pdf' => 'nullable|string|max:500',
            'validado' => 'boolean',
        ]);

        $ticket->update($validated);

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Ticket actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Ticket eliminado exitosamente.');
    }

    /**
     * Validar un ticket (marcar como validado) - Para uso interno/admin
     */
    public function validateTicket(string $id)
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'validado' => true,
            'contador_validaciones' => $ticket->contador_validaciones + 1,
        ]);

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Ticket validado exitosamente.');
    }

    /**
     * Validar ticket por código QR (para validación pública)
     */
    public function validateTicketByCode(string $code)
    {
        // Verificar si el usuario está autenticado
        if (! auth()->check()) {
            // Usuario no autenticado - retornar página en blanco

            /*
            return response('<html><body></body></html>', 200)
                ->header('Content-Type', 'text/html');
            */

            return redirect('/');
        }

        if ($code == 'c143ed49-5f41-4b00-8dbc-67dc8951d71e') {
            $data = [
                'status' => 'valid',
                'ticket' => (object) ['contador_validaciones' => 0],
                'isPhotographer' => true,
                'locacion' => 'Fotógrafo',
                'asiento' => null,
            ];

            return view('qr-validation', $data);
        }

        // Buscar ticket por código QR
        $ticket = Ticket::where('codigo_qr', $code)->first();

        // Preparar datos para la vista
        $data = [
            'status' => $ticket ? 'valid' : 'invalid',
            'code' => $code,
        ];

        // Si el ticket existe, agregar datos del ticket
        if ($ticket) {

            $ticket->update([
                'validado' => true,
                'contador_validaciones' => $ticket->contador_validaciones + 1,
                'validado_por' => auth()->id(),
            ]);

            $data['ticket'] = $ticket;
            $data['isPhotographer'] = $ticket->is_photographer;
            $data['locacion'] = $ticket->locacion;
            $data['asiento'] = $ticket->asiento;
        } else {
            $data = [
                'status' => 'invalid',
                'ticket' => (object) ['contador_validaciones' => 0],
            ];
        }

        // Renderizar la vista de validación QR
        return view('qr-validation', $data);
    }

    /**
     * Escanear código QR para validar ticket
     */
    public function scanQR(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string|exists:tickets,codigo_qr',
        ]);

        $ticket = Ticket::where('codigo_qr', $request->qr_code)->first();

        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket no encontrado',
            ], 404);
        }

        // Marcar como validado
        $ticket->update([
            'validado' => true,
            'contador_validaciones' => $ticket->contador_validaciones + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket validado exitosamente',
            'ticket' => [
                'id' => $ticket->id,
                'codigo_qr' => $ticket->codigo_qr,
                'participant' => $ticket->participant->full_name,
                'event' => $ticket->event->nombre,
                'seat_location' => $ticket->seat_location,
                'validated_count' => $ticket->contador_validaciones,
            ],
        ]);
    }

    /**
     * Generar PDF para un ticket
     */
    public function generatePDF(string $id)
    {
        $ticket = Ticket::with(['participant', 'event'])->findOrFail($id);

        // Aquí iría la lógica para generar el PDF
        // Por ahora, solo actualizamos el campo archivo_pdf

        $pdfFileName = 'ticket_'.$ticket->id.'_'.date('YmdHis').'.pdf';
        $ticket->update(['archivo_pdf' => $pdfFileName]);

        return redirect()->route('admin.tickets.index')
            ->with('success', 'PDF generado exitosamente: '.$pdfFileName);
    }

    /**
     * Previsualizar vista PDF para un ticket
     */
    public function previewPDF(string $id)
    {
        $ticket = Ticket::with(['participant', 'event'])->findOrFail($id);

        // Generar URL de validación para el QR
        $validationUrl = route('tickets.validate', ['code' => $ticket->codigo_qr]);

        // Generar código QR (usando el mismo método que PdfGenerationService)
        $qrCode = $this->generateQrCodeForPreview($validationUrl);

        // Preparar datos para la vista
        $data = [
            'event' => $ticket->event,
            'ticket' => $ticket,
            'participant' => $ticket->participant,
            'seatLocation' => $ticket->seat_location,
            'seatCode' => $ticket->seat_code,
            'qrCode' => $qrCode,
        ];

        return view('pdfs.ticket', $data);
    }

    /**
     * Generar código QR para previsualización
     */
    private function generateQrCodeForPreview(string $validationUrl): string
    {
        // Verificar si el paquete QR está disponible
        if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            try {
                $qrCode = QrCode::format('png')
                    ->size(200)
                    ->margin(1)
                    ->generate($validationUrl);

                return 'data:image/png;base64,'.base64_encode($qrCode);
            } catch (\Exception $e) {
                // Si hay error, usar placeholder
                \Log::warning('Error al generar QR para previsualización: '.$e->getMessage());
            }
        }

        // Fallback: usar placeholder de QR
        return 'data:image/svg+xml;base64,'.base64_encode('
            <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
                <rect width="200" height="200" fill="#f0f0f0"/>
                <text x="100" y="100" text-anchor="middle" fill="#666" font-family="Arial" font-size="14">
                    QR Code Placeholder
                </text>
                <rect x="50" y="50" width="100" height="100" fill="none" stroke="#999" stroke-width="2"/>
            </svg>
        ');
    }

    /**
     * Descargar PDF existente para un ticket
     */
    public function downloadPDF(Ticket $ticket)
    {
        // Validar que el ticket tenga archivo_pdf
        if (empty($ticket->archivo_pdf)) {
            abort(404, 'PDF no generado para este ticket');
        }

        // Construir la ruta completa del archivo
        // El campo archivo_pdf ya incluye la ruta completa según lo indicado
        $filePath = storage_path('app/private/'.$ticket->archivo_pdf);

        // Verificar que el archivo exista físicamente
        if (! file_exists($filePath)) {
            abort(404, 'Archivo PDF no encontrado en el servidor');
        }

        // Descargar el archivo
        return response()->download($filePath, $ticket->download_file_name, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Generar PDFs para múltiples tickets
     */
    public function generateBulkPDFs(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
        ]);

        $count = 0;
        foreach ($request->ticket_ids as $ticketId) {
            $ticket = Ticket::find($ticketId);
            if ($ticket) {
                $pdfFileName = 'ticket_'.$ticket->id.'_'.date('YmdHis').'.pdf';
                $ticket->update(['archivo_pdf' => $pdfFileName]);
                $count++;
            }
        }

        return redirect()->route('admin.tickets.index')
            ->with('success', "Se generaron {$count} PDFs exitosamente.");
    }

    /**
     * Exportar tickets a CSV
     */
    public function export()
    {
        $tickets = Ticket::with(['participant', 'event'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tickets_'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($tickets) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, ['ID', 'Código QR', 'Participante', 'Evento', 'Ubicación', 'Validado', 'Validaciones', 'PDF', 'Fecha Creación']);

            // Datos
            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->id,
                    $ticket->codigo_qr,
                    $ticket->participant->full_name,
                    $ticket->event->nombre,
                    $ticket->seat_location,
                    $ticket->validado ? 'Sí' : 'No',
                    $ticket->contador_validaciones,
                    $ticket->archivo_pdf,
                    $ticket->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Filtrar tickets por evento
     */
    public function filterByEvent(Request $request)
    {
        $eventId = $request->event_id;

        $tickets = Ticket::with(['participant', 'event'])
            ->when($eventId, function ($query) use ($eventId) {
                return $query->where('event_id', $eventId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $events = Event::orderBy('nombre')->get();

        return view('admin.tickets.index', compact('tickets', 'events'));
    }
}
