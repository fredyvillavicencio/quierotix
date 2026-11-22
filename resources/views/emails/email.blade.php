<table bgcolor="#0a2d0d" cellpadding="0" cellspacing="10" width="100%" style="font-family:Helvetica,Arial,sans-serif;font-size:14px;color:#5d6264">
    <tbody><tr>
        <td align="center">
            <table width="590" cellpadding="0" cellspacing="0">
                <tbody><tr>
                    <td align="center">
                        <table bgcolor="#ffffff" width="100%" cellpadding="0" cellspacing="0">
                            <tbody><tr>
                                <td align="left">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tbody><tr>
                                            <td align="center" bgcolor="#ffffff">

                                                <table width="100%" cellpadding="0" cellspacing="0">
                                                    <tbody><tr>
                                                        <td align="center">
                                                            <table bgcolor="#FFFFFF" width="40%" cellpadding="0" cellspacing="15" style="font-size:20px">
                                                                <tbody><tr>
                                                                    <td align="center">
                                                                        <img align="absbottom" src="https://quierotix.com/images/logo_quierotix.png" height="60px" class="CToWUd" data-bit="iit">
                                                                    </td>
                                                                </tr>
                                                            </tbody></table>
                                                        </td>
                                                    </tr>
                                                </tbody></table>

                <table width="100%" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr>
                        <td align="center">
                            <table bgcolor="#ffffff" width="100%" cellpadding="0" cellspacing="0">
                                <tbody><tr>
                                    <td align="center">
                                        <table width="100%" cellpadding="0" cellspacing="20">
                                            <tbody><tr>
                                                    <td align="center" valign="top">
                                                        <img src="{{ url('public') }}/{{ $event->imagen }}" width="200px" alt="{{ $event->nombre }}" class="CToWUd" data-bit="iit">
                                                    </td>
                                                    <td align="left">
                                                        <table width="100%" cellpadding="0" cellspacing="0" style="font-size:17px">
                                                            <tbody>
                                                            <tr>
                                                                <td height="30">
                                                                    <b style="font-size: 21px;">{{ $event->nombre }}</b> 
                                                                </td>
                                                            </tr><tr>
                                                            </tr><tr>
                                                                <td height="30">
                                                                    {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }}<br>{{ \Carbon\Carbon::parse($event->hora)->format('h:i a') }}<br><br>
                                                                </td>
                                                                </tr><tr>
                                                                    <td height="40">
                                                                        <span style="font-weight:bold">{{ $event->lugar }}</span><br>
                                                                        @if ($event->direccion)
                                                                            {{ $event->direccion }}<br>
                                                                        @endif
                                                                        {{ $event->ciudad ?? '' }}
                                                                    </td>
                                                                </tr>
                                                        </tbody></table>
                                                    </td> 
                                            </tr>
                                        </tbody></table>
                                    </td>
                                </tr>
                            </tbody></table>
                        </td>
                    </tr>

                <tr><td>

                </td></tr>
                </tbody>
                </table>

                <table width="100%" cellpadding="0" cellspacing="10">
                    <tbody>
                        <td align="left">
                            <table width="100%" cellpadding="8" cellspacing="0">
                                <tbody>
                                    <tr>
                                    <td align="left" style="font-weight:bold;color:#383838;font-size:16px">
                                        {{ $participant->nombre }} {{ $participant->apellido }}, descarga tus Tickets:
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left">
                                            <table width="95%" cellpadding="0" cellspacing="5">
                                                <tbody>
                                                    @for($i = 0; $i < count($tickets); $i++)
                                                        @php
                                                            $ticketNumber = $i + 1;
                                                            $downloadUrl = $downloadUrls[$i] ?? '#';
                                                            $emailTicket = $tickets[$i] ?? null;
                                                            $downloadLabel = $emailTicket?->download_label ?? "Ticket {$ticketNumber}";
                                                        @endphp
                                                        <tr>
                                                        <td align="left" width="100%">
                                                            <a style="text-decoration:none;color:#020201;font-weight:bold;display:block;padding:15px;width:100%;text-align:center;background-color:#a7f801;background-image:linear-gradient(135deg,#15d403 0%,#a7f801 55%,#fcef03 100%);border-radius:9px;" href="{{ $downloadUrl }}" target="_blank">
                                                                <img src="https://quierotix.com/images/descarga.png" height="15px"> Descargar {{ $downloadLabel }}
                                                            </a>
                                                        </td>
                                                        </tr>
                                                    @endfor

                                                @if (count($tickets) > 2)
                                                    <tr>
                                                        <td align="left">
                                                            &nbsp;
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td align="left">
                                                            <a style="text-decoration:none;color:#020201;font-weight:bold;display:block;padding:15px;width:100%;text-align:center;background-color:#a7f801;background-image:linear-gradient(135deg,#15d403 0%,#a7f801 55%,#fcef03 100%);border-radius:9px;" href="{{ $allPdfsUrl }}" target="_blank">
                                                                <img src="https://quierotix.com/images/descarga.png" height="15px"> Descargar todos en ZIP
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody></table>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="left">
                                        <ul>
                                            <li>NO compartas o publiques tus tickets con nadie.</li>
                                            <li>Tus tickets pueden ser validados UNA sola vez.</li>
                                            <li>Al compartir tus tickets alguien puede utilizarlos antes.</li>
                                        </ul>
                                    </td>
                                </tr>

                            </tbody></table>
                        </td>
                    </tr>
                </tbody></table>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody></table>
                        <table width="100%" cellpadding="30" cellspacing="0" style="font-size:13px;color:#FFF">
                            <tbody><tr>
                                <td align="center">
                                    © {{ date('Y') }} QuieroTIX.com
                                </td>
                            </tr>
                        </tbody></table>
                    </td>
                </tr>
            </tbody></table>
        </td>
    </tr>
</tbody>
</table>