<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventPurchaseController as AdminEventPurchaseController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\DescargasController;
// use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\EventPurchaseController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurrenteWebhookController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

// Página pública de inicio: listado de eventos
Route::get('/', [HomeController::class, 'index'])->name('home');

// Página pública del sorteo de tickets de regalo
Route::get('/regalos', [GiftController::class, 'index'])->name('gifts.index');
Route::get('/regalos/aleatorio', [GiftController::class, 'randomTicket'])->name('gifts.random');

// Detalle público de un evento (sin autenticación)
Route::get('/eventos/{event}', [HomeController::class, 'show'])->name('events.public.show');

// Webhook público de pagos de Recurrente (sin verificación de firma por ahora)
Route::post('/recurrente/pagos', RecurrenteWebhookController::class)
    ->name('recurrente.pagos');

// Compra pública de tickets (sin autenticación)
Route::get('/eventos/{event}/comprar', [EventPurchaseController::class, 'create'])
    ->name('events.public.purchase.create');
Route::post('/eventos/{event}/comprar', [EventPurchaseController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('events.public.purchase.store');
Route::get('/eventos/{event}/compra/{participant}', [EventPurchaseController::class, 'success'])
    ->middleware('signed')
    ->name('events.public.purchase.success');

// Páginas públicas de resultado de compra (sin integración con el flujo actual)
Route::get('/compra_completa', function () {
    return view('public.purchase-complete');
})->name('purchase.complete');

Route::get('/compra_error', function () {
    return view('public.purchase-error');
})->name('purchase.error');

Route::get('/dashboard', function () {
    return redirect('/admin');
})->name('dashboard');

/*
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/

Route::get('/qr-validation', function () {
    return view('qr-validation');
});

Route::get('/qr', function () {
    return view('qr');
});

Route::get('/estadisticas', function () {
    return view('admin.statistics.index');
});

Route::get('/admin/estadisticas', [StatisticsController::class, 'index'])->name('admin.statistics');

// Rutas públicas para validación de tickets
Route::get('/tickets/validate/{code}', [TicketController::class, 'validateTicketByCode'])
    ->name('tickets.validate');

Route::get('/tickets/{ticket:codigo}/download-pdf', [TicketController::class, 'downloadPDF'])
    ->name('tickets.download.pdf');

// Descarga de PDFs
Route::get('/admin/participants/{participant}/download-pdfs', [ParticipantController::class, 'downloadPdfs'])
    ->name('admin.participants.download.pdfs');

/*
Route::get('/descargar-guia', function () {
    return response()->json(['message' => 'Texto de prueba'], 200);
})->name('descargar.guia');
*/

Route::get('/descargar-guia', [DescargasController::class, 'descargarGuia'])->name('descargar.guia');

Route::get('/descargar-ticket-fotografos', [DescargasController::class, 'descargarTicketFotografos'])->name('descargar.ticket.fotografos');
Route::get('/descargar-info-fotografos', [DescargasController::class, 'descargarInfoFotografos'])->name('descargar.info.fotografos');

Route::get('/admin/participants/find-by-email', [ParticipantController::class, 'redirectByEmail'])
    ->name('participants.find.by.email');

// Preview de email para participante
Route::get('/admin/participants/{participant}/preview-email', [ParticipantController::class, 'previewEmail'])
    ->name('participants.preview.email');

Route::get('/admin/participants/{participant}/preview-email-fotografo', [ParticipantController::class, 'previewEmailFotografo'])
    ->name('participants.preview.email.fotografo');

// Rutas de administración (requieren autenticación y ser admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard de administración

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de eventos
    Route::resource('events', EventController::class);

    // Compra manual de tickets desde el panel administrativo
    Route::get('/events/{event}/purchase', [AdminEventPurchaseController::class, 'create'])
        ->name('events.purchase.create');
    Route::post('/events/{event}/purchase', [AdminEventPurchaseController::class, 'store'])
        ->name('events.purchase.store');
    Route::get('/events/{event}/purchase/{participant}/success', [AdminEventPurchaseController::class, 'success'])
        ->name('events.purchase.success');

    // Gestión de participantes
    Route::resource('participants', ParticipantController::class);

    // Gestión de tickets
    Route::resource('tickets', TicketController::class);

    // Estadísticas del sistema
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    Route::post('/statistics/export-pdf', [StatisticsController::class, 'exportPDF'])->name('statistics.export.pdf');
    Route::get('/statistics/export-excel', [StatisticsController::class, 'exportExcel'])->name('statistics.export.excel');
    Route::get('/statistics/realtime', [StatisticsController::class, 'getRealTimeStats'])->name('statistics.realtime');

    // Rutas adicionales para participantes
    Route::post('/participants/import', [ParticipantController::class, 'import'])->name('participants.import');
    Route::get('/participants/export', [ParticipantController::class, 'export'])->name('participants.export');

    // Rutas adicionales para tickets
    Route::post('/tickets/scan-qr', [TicketController::class, 'scanQR'])->name('tickets.scan.qr');
    Route::post('/tickets/{ticket}/validate', [TicketController::class, 'validateTicket'])->name('tickets.validate');
    Route::post('/tickets/{ticket}/generate-pdf', [TicketController::class, 'generatePDF'])->name('tickets.generate.pdf');

    Route::get('/tickets/{ticket}/preview-pdf', [TicketController::class, 'previewPDF'])->name('tickets.preview.pdf');
    Route::post('/tickets/generate-bulk-pdfs', [TicketController::class, 'generateBulkPDFs'])->name('tickets.generate.bulk.pdfs');
    Route::get('/tickets/export', [TicketController::class, 'export'])->name('tickets.export');
    Route::get('/tickets/filter-by-event', [TicketController::class, 'filterByEvent'])->name('tickets.filter.by.event');

    // Importación de CSV
    Route::get('/events/{event}/import', [EventController::class, 'showImportForm'])
        ->name('events.import.form');
    Route::post('/events/{event}/import', [EventController::class, 'importParticipants'])
        ->name('events.import');

    Route::get('/events/{event}/import-fotografos', [EventController::class, 'showImportFotografosForm'])
        ->name('events.import-fotografos.form');
    Route::post('/events/{event}/import-fotografos', [EventController::class, 'importFotografos'])
        ->name('events.import-fotografos');

    // Generación de PDFs
    Route::post('/events/{event}/generate-pdfs', [EventController::class, 'generatePdfs'])
        ->name('events.generate.pdfs');

    Route::get('/tickets/{ticket:codigo}/download-pdf-admin', [TicketController::class, 'downloadPDF'])->name('tickets.download.pdfadmin');

    // Descarga de PDFs
    Route::get('/participants/{participant}/download-pdfs-admin', [ParticipantController::class, 'downloadPdfs'])
        ->name('participants.download.pdfsadmin');

    // Página de progreso de generación de PDFs
    Route::get('/events/{event}/pdf-generation/progress/{jobId}', [EventController::class, 'showPdfGenerationProgress'])
        ->name('events.pdf-generation.progress');

    // API para obtener progreso de generación de PDFs
    Route::get('/events/{event}/pdf-generation/progress/{jobId}/api', [EventController::class, 'getPdfGenerationProgress'])
        ->name('events.pdf-generation.progress.api');

    // Envío de emails
    Route::post('/events/{event}/send-emails', [EventController::class, 'sendEmails'])
        ->name('events.send.emails');

    // Envío de email a participante individual
    Route::post('/participants/{participant}/send-email', [ParticipantController::class, 'sendEmail'])
        ->name('participants.send.email');

    // Estadísticas por evento
    Route::get('/events/{event}/statistics', [EventController::class, 'statistics'])
        ->name('events.statistics');

    // Mapa de asientos
    Route::get('/events/{event}/seat-map', [EventController::class, 'seatMap'])
        ->name('events.seat.map');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
