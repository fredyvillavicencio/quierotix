<table bgcolor="#OF2037" cellpadding="0" cellspacing="10" width="100%" style="font-family:Helvetica,Arial,sans-serif;font-size:14px;color:#5d6264">
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
                                                            <table bgcolor="#OF2037" width="40%" cellpadding="0" cellspacing="15" style="font-size:20px">
                                                                <tbody><tr>
                                                                    <td align="center">
                                                                        <img align="absbottom" src="https://actosdeinvestiduraumg.com/tickets/images/umg.png" height="50px" class="CToWUd" data-bit="iit">
                                                                    </td>
                                                                </tr>
                                                            </tbody></table>
                                                        </td>
                                                    </tr>
                                                </tbody></table>

                <table width="100%" cellpadding="0" cellspacing="0">
                    <tbody><tr>
                        <td align="center">
                            <table bgcolor="#B29057" width="60%" cellpadding="5" cellspacing="0" style="font-size:16pt">
                                <tbody><tr>
                                    <td align="center">
                                        <table width="100%" cellpadding="10" cellspacing="0">
                                            <tbody><tr>
                                                <td align="center">
                                                    <span style="color:#FFFFFF;font-weight:bold">{{ $participant->nombre }} {{ $participant->apellido }}</span>
                                                </td>
                                            </tr>
                                        </tbody></table>
                                    </td>
                                </tr>
                            </tbody></table>
                        </td>
                    </tr>

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
                                                    <td align="left" width="303">
                                                        <table width="100%" cellpadding="0" cellspacing="0" style="font-size:17px">
                                                            <tbody>
                                                            <tr>
                                                                <td height="30">
                                                                    <b>Acto Solemne de Investidura Profesional - UMG 2026</b> 
                                                                </td>
                                                            </tr><tr>
                                                            </tr><tr>
                                                                <td height="30">
                                                                    {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($event->hora)->format('h:i A') }}<br><br>
                                                                </td>
                                                                </tr><tr>
                                                                    <td height="40">
                                                                        <span style="font-weight:bold">{{ $event->lugar }}</span><br>
                                                                        {{ $event->direccion ?? '' }}<br>
                                                                        {{ $event->ciudad ?? '' }}
                                                                    </td>
                                                                </tr>
                                                            <tr>
                                                                <td height="2">&nbsp;</td>
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

                </td></tr></tbody></table>

                <table width="100%" cellpadding="10" cellspacing="0" style="border-bottom:solid 1px #888888; border-top:solid 1px #888888">
                    <tbody><tr>
                        <td align="center" valign="top" width="80">
                            <img src="https://actosdeinvestiduraumg.com/tickets/images/descargar.png"  class="CToWUd" data-bit="iit">
                        </td>
                        <td align="left">
                            <table width="100%" cellpadding="8" cellspacing="0">
                                <tbody><tr>
                                    <td align="left" style="font-weight:bold;color:#383838;font-size:15px">
                                        Descarga tus entradas
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left">
                                        <ul>
                                            <li>TODOS los Invitados y Padrinos DEBEN presentar su entrada impresa o en su móvil al ingresar al evento.</li>
                                            <li>Los graduandos NO requieren entrada para su ingreso.</li>
                                            <li>NO SE VENDEN entradas extras ya que el evento es cupo limitado con sillas numeradas.</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left">
                                            <table cellpadding="0" cellspacing="5">
                                                <tbody><tr>
                                                    @for($i = 0; $i < min(7, count($tickets)); $i++)
                                                        @php
                                                            $ticketNumber = $i + 1;
                                                            $downloadUrl = $downloadUrls[$i] ?? '#';
                                                        @endphp
                                                        @if($i % 2 == 0)
                                                            </tr><tr>
                                                        @endif
                                                        <td align="left" width="50%">
                                                            <table bgcolor="#182D4E" cellpadding="12" cellspacing="0" style="font-size:15px;border-radius:3px">
                                                                <tbody><tr>
                                                                    <td align="left">
                                                                        <a style="text-decoration:none;color:#ffffff;font-weight:bold" href="{{ $downloadUrl }}" target="_blank">
                                                                            <img src="https://actosdeinvestiduraumg.com/tickets/images/descarga.png" height="15px"> Descargar entrada {{ $ticketNumber }}
                                                                            @if($i == 6)
                                                                            <br>Padrino / Madrina
                                                                            @endif
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody></table>
                                                        </td>
                                                    @endfor
                                                </tr>
                                                <tr>
                                                    <td align="left" colspan="2">
                                                        <a style="text-decoration:none;color:#ffffff;font-weight:bold;display:block;padding:15px;width:100%;text-align:center;background-color:#182D4E;border-radius:3px" href="{{ $allPdfsUrl }}" target="_blank">
                                                            <img src="https://actosdeinvestiduraumg.com/tickets/images/descarga.png" height="15px"> Descargar todas en ZIP
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody></table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20">
                                        <br>
                                        <strong style="font-weight:bold;color:#383838;font-size:15px;">DESCARGAR Y LEER<br>Información importante aquí:</strong><br>
                                        <a href="https://drive.google.com/drive/folders/1DbigJk0IMa7ZM4lA_rMUJiW5s-g72pgq"  target="_blank" style="font-size:11px">https://drive.google.com/drive/folders/1DbigJk0IMa7ZM4lA_rMUJiW5s-g72pgq</a>
                                        <br>
                                        <br>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td align="left" colspan="2">
                                                        <a style="text-decoration:none;color:#ffffff;font-weight:bold;display:block;padding:15px;text-align:center;background-color:#182D4E;border-radius:3px" href="https://forms.gle/BJv45mdRhHASRCUi7" target="_blank">
                                                            Formulario de Acreditación para Fotógrafo
                                                        </a>
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
                                    © {{ date('Y') }} Universidad Mariano Gálvez
                                </td>
                            </tr>
                        </tbody></table>
                    </td>
                </tr>
            </tbody></table>
        </td>
    </tr>
</tbody></table>
<table width="100%" bgcolor="#e1ff05">
    <tr>
        <td align="center">
            <img src="https://actosdeinvestiduraumg.com/tickets/images/firma-electronica.png" width="600px">
        </td>
    </tr>
</table>