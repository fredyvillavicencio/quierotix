<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Participant;
use App\Services\PdfGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateEventPdfsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $event;
    public $jobId;
    public $timeout = 3600; // 1 hora máximo
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, string $jobId)
    {
        $this->event = $event;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Aumentar límite de memoria y tiempo de ejecución para manejar eventos grandes
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 3600);
            
            $this->updateProgress(0, 'Iniciando generación de PDFs...');
            
            $pdfService = new PdfGenerationService();
            $participants = Participant::where('event_id', $this->event->id)
                ->with('tickets')
                ->get();
            
            $totalParticipants = $participants->count();
            $processed = 0;
            $errors = [];
            
            $this->updateProgress(5, "Encontrados {$totalParticipants} participantes");
            
            foreach ($participants as $participant) {
                try {
                    $this->updateProgress(
                        (int) (($processed / $totalParticipants) * 90) + 5,
                        "Procesando participante: {$participant->nombre} {$participant->apellido}"
                    );
                    
                    // Generar PDFs para el participante
                    $pdfPaths = $pdfService->generateParticipantPdfs($participant);
                    
                    $processed++;
                    $this->updateProgress(
                        (int) (($processed / $totalParticipants) * 90) + 5,
                        "Completado: {$processed}/{$totalParticipants} participantes"
                    );
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'participant_id' => $participant->id,
                        'participant_name' => $participant->nombre . ' ' . $participant->apellido,
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('Error generando PDFs para participante', [
                        'participant_id' => $participant->id,
                        'event_id' => $this->event->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Calcular estadísticas finales
            $totalTickets = $this->event->tickets()->count();
            $ticketsWithPdfs = $this->event->tickets()
                ->where('archivo_pdf', '!=', '')
                ->count();
            
            $this->updateProgress(95, 'Generando estadísticas finales...');
            
            // Guardar resultados en caché
            $results = [
                'status' => 'completed',
                'total_participants' => $totalParticipants,
                'processed_participants' => $processed,
                'total_tickets' => $totalTickets,
                'tickets_with_pdfs' => $ticketsWithPdfs,
                'errors' => $errors,
                'completed_at' => now()->toDateTimeString(),
                'message' => "Generación completada: {$processed}/{$totalParticipants} participantes, {$ticketsWithPdfs}/{$totalTickets} tickets"
            ];
            
            Cache::put("pdf_generation_{$this->jobId}", $results, 3600); // 1 hora
            
            // Actualizar progreso final con estado completed
            $this->updateProgress(100, '¡Generación de PDFs completada!', 'completed');
            
        } catch (\Exception $e) {
            Log::error('Error en GenerateEventPdfsJob', [
                'event_id' => $this->event->id,
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->updateProgress(0, 'Error: ' . $e->getMessage(), 'error');
            
            // Guardar error en caché
            Cache::put("pdf_generation_{$this->jobId}", [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now()->toDateTimeString()
            ], 3600);
            
            throw $e;
        }
    }

    /**
     * Update progress in cache
     */
    private function updateProgress(int $percentage, string $message, string $status = 'processing'): void
    {
        // Obtener progreso existente si hay
        $existingProgress = Cache::get("pdf_generation_{$this->jobId}", []);
        
        // Actualizar solo los campos básicos
        $progress = array_merge($existingProgress, [
            'status' => $status,
            'percentage' => $percentage,
            'message' => $message,
            'updated_at' => now()->toDateTimeString()
        ]);
        
        Cache::put("pdf_generation_{$this->jobId}", $progress, 3600);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateEventPdfsJob failed', [
            'event_id' => $this->event->id,
            'job_id' => $this->jobId,
            'error' => $exception->getMessage()
        ]);
        
        Cache::put("pdf_generation_{$this->jobId}", [
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'failed_at' => now()->toDateTimeString()
        ], 3600);
    }
}