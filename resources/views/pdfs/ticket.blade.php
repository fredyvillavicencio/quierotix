<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

<style>
    @page {
        margin: 0;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: DejaVu Sans, Arial, sans-serif;
    }

    .page {
        position: relative;
        width: 800px;   /* Carta en px aprox */
        height: 1035px; /* Carta vertical */
        overflow: hidden;
    }

    /* Fondo REAL (no CSS background) */
    .bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 790px;
        height: 1035px;
        z-index: 0;
    }

    .content {
        position: relative;
        z-index: 2;
        width: 790px;
        height: 1056px;
    }

    .ticket-id {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 12px;
        color: #FFFFFF;
    }

    .qr {
        position: absolute;
        top: 420px;
        left: 50%;
        transform: translateX(-50%);
    }

    .qr img {
        width: 260px;
        height: 260px;
    }

    .info-table {
        position: absolute;
        top: 735px;
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
    }
    
    .padrino {
        position: absolute;
        top: 305px;
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 20px;
    }

    .nombre {
        position: absolute;
        top: 250px;
        width: 100%;
        text-align: center;
        font-size: 36px;
        font-weight: bold;
    }

    .lugar {
        position: absolute;
        top: 330px;
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 20px;
        font-weight: bold;
    }


</style>
</head>

<body>

<div class="page">

    <!-- FONDO -->
@php
        $imagePath = $event->imagen_ticket ? public_path($event->imagen_ticket) : null;
        $imageContents = $imagePath && is_file($imagePath) ? @file_get_contents($imagePath) : false;
        $backgroundImage = $imageContents !== false
            ? 'data:' . (mime_content_type($imagePath) ?: 'image/jpeg') . ';base64,' . base64_encode($imageContents)
            : null;
@endphp
    @if ($backgroundImage)
        <img class="bg" src="{{ $backgroundImage }}">
    @else
        <div class="bg" style="background-color: #f8f9fa;"></div>
    @endif

    <div class="content">

        <!-- QR -->
        <div class="qr">
            <img src="{{ $qrCode }}" width="280" height="280">
        </div>

        <!-- TICKET / UBICACIÓN -->
        <div class="info-table" style="text-align: center;">
            <table cellpadding="0" cellspacing="0" width="90%" align="center">
               <tr>
                    <td width="100%" style="text-align: center; font-size: 48px;">{{ $ticket->numero }}</td>
                </tr>
                <tr>
                    <td width="100%" style="text-align: center;">{{ $ticket->download_label }}</td>
                </tr>             
            </table>
        </div>

        <!-- NOMBRE -->
        <div class="nombre">
            {{ $event->nombre }}
        </div>

        <div class="padrino">
            {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($event->hora)->format('h:i A') }}
        </div>

        
        <!-- lugar -->
        <div class="lugar">
            {{ $event->lugar }}
        </div>


    </div>

</div>

</body>
</html>