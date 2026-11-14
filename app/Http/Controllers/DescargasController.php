<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DescargasController extends Controller
{
    public function descargarGuia()
    {
        // return response()->json(['message' => 'Texto de prueba'], 200);
        
        $rutaArchivo = storage_path('app/public/guia_fotografos.pdf');
        
        // Verificar si el archivo existe
        if (!file_exists($rutaArchivo)) {
            abort(404, 'El archivo no existe');
        }
        
        // Descargar el archivo con un nombre personalizado
        return response()->download($rutaArchivo, 'guia_fotografos.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="guia_fotografo.pdf"'
        ]);
    }
    

    public function descargarTicketFotografos()
    {
        
        $rutaArchivo = storage_path('app/public/ticket_fotografos.pdf');
        
        // Verificar si el archivo existe
        if (!file_exists($rutaArchivo)) {
            abort(404, 'El archivo no existe');
        }
        
        // Descargar el archivo con un nombre personalizado
        return response()->download($rutaArchivo, 'ticket_fotografos.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ticket_fotografos.pdf"'
        ]);
    }    
    
    public function descargarInfoFotografos()
    {
        
        $rutaArchivo = storage_path('app/public/info_fotografos.pdf');
        
        // Verificar si el archivo existe
        if (!file_exists($rutaArchivo)) {
            abort(404, 'El archivo no existe');
        }
        
        // Descargar el archivo con un nombre personalizado
        return response()->download($rutaArchivo, 'info_fotografos.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="info_fotografos.pdf"'
        ]);
    }
    
}