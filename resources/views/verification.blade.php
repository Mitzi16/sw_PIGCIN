<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Constancia</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f7f6;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
        }
        .container {
            max-width: 700px;
            width: 100%;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-top: 5px solid;
        }
        .icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 20px;
        }
        /* Caso Exitoso */
        .status-success { border-color: #28a745; }
        .status-success .icon { color: #28a745; }
        .status-success h2 { color: #28a745; }
        
        /* Caso Fallido */
        .status-error { border-color: #dc3545; }
        .status-error .icon { color: #dc3545; }
        .status-error h2 { color: #dc3545; }

        h2 {
            text-align: center;
            margin-top: 0;
        }
        p {
            line-height: 1.6;
            text-align: center;
            font-size: 1.1em;
            color: #555;
        }
        .data-list {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .data-item {
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        .data-item strong {
            display: block;
            color: #111;
            margin-bottom: 5px;
        }

        .section-title {
            text-align: center;
            color: #333;
            font-size: 1.2em;
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .extra-data .data-item {
            text-align: center;
        }
        .signature-box {
            font-family: monospace;
            font-size: 0.9em;
            width: 100%;
            box-sizing: border-box; 
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            resize: none;
            height: 100px; 
            color: #555;
            word-break: break-all; 
        }
    </style>
</head>
<body>

    @if($data)
        <div class="container status-success">
            <div class="icon">✅</div>
            <h2>Constancia Válida</h2>
            <p>El documento con el siguiente folio es auténtico y ha sido verificado en nuestra base de datos:</p>
            
            <div class="data-list">
                <div class="data-item">
                    <strong>Folio:</strong>
                    <span>{{ $data['folio'] }}</span>
                </div>
                <div class="data-item">
                    <strong>Título del Trabajo:</strong>
                    <span>{{ $data['title'] }}</span>
                </div>
                <div class="data-item">
                    <strong>Institución que avala y expide:</strong>
                    <span>TecNM/Cenidet</span>
                </div>
                <div class="data-item">
                    <strong>Director: </strong>
                    <span>Lic. Lorenzo Octavio Hernández Robles Arenas</span>
                </div>
                <div class="data-item">
                    <strong>Estatus:</strong>
                    <span>{{ $data['status'] }}</span>
                </div>
            </div>

            <h3 class="section-title">Datos del Participante</h3>
            <div class="data-list extra-data">
                <div class="data-item">
                    <strong>Participante:</strong>
                    <span>{{ $participant_name }}</span>
                </div>
                <div class="data-item">
                    <strong>Rol:</strong>
                    <span>{{ $participant_role }}</span>
                </div>
            </div>

            <div class="data-list extra-data">
                <h3 class="section-title">Sello Digital de Verificación</h3>
                    <textarea readonly class="signature-box">{{ $selloDigital }}</textarea>

                <div class="data-item">
                    <strong>Identificador:</strong>
                    <textarea readonly class="signature-box">{{ $signedText }}</textarea>
                </div>
            </div>

        </div>

    @else
        <div class="container status-error">
            <div class="icon">❌</div>
            <h2>Constancia No Válida</h2>
            <p>
                El folio <strong>{{ $folio }}</strong> no se ha encontrado en nuestra base de datos 
                o no corresponde a un artículo aceptado.
            </p>
            <p style="font-size: 0.9em; color: #777; margin-top: 20px;">
                Si cree que esto es un error, por favor contacte al administrador del sistema.
            </p>
        </div>
    @endif

</body>
</html>