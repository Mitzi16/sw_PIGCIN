<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Nuevo artículo - CITCA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }

        .muted {
            color: #718096;
            font-size: 15px;
            line-height: 1.6;
        }

        .note {
            color: #e53e3e;
            font-size: 14px;
            font-weight: 600;
        }

        .small {
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>

<body style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-text-size-adjust: none; background-color: #ffffff; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100% !important;">

    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #edf2f7; margin: 0; padding: 0; width: 100%;">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="header" style="padding: 25px 0; text-align: center;">
                            <div style="display:flex; align-items:center; justify-content:center; gap:10px;">
                                <a href="{{ route('login') }}" style="color: #3d4852; font-size: 19px; font-weight: bold; text-decoration: none;">
                                    CITCA
                                </a>
                                <img src="https://www.huatabampo.tecnm.mx/wp-content/uploads/2019/09/TecNM-logo-216x300.png" alt="TecNM" style="height:50px;">
                            </div>
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" style="background-color: #edf2f7; margin: 0; padding: 0; width: 100%; border: hidden !important;">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #ffffff; border-radius: 2px; margin: 0 auto; padding: 0; width: 570px; box-shadow: 0 2px 0 rgba(0,0,0,0.03);">
                                <tr>
                                    <td class="content-cell" style="padding: 32px; max-width: 100vw;">

                                        <!-- Saludo -->
                                        <h1 style="color: #3d4852; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;">
                                            Estimado(a) {{ $articleData['recipient_name'] ?? $articleData['name'] ?? 'usuario' }}:
                                        </h1>

                                        <!-- Si el destinatario es Reviewer --> 
                                        @if(isset($articleData['recipient_role']) && strtolower($articleData['recipient_role']) === 'reviewer')

                                            <!-- Cuerpo específico para revisores (tu texto original) -->
                                            <p class="muted" style="margin-top: 0; text-align: left;">
                                                Se le informa que se le ha asignado una contribución para su revisión, considerando su área de especialidad. El equipo de CITCA agradece profundamente su valiosa colaboración en este proceso, fundamental para garantizar la calidad académica del congreso.
                                            </p>

                                            <p class="muted" style="text-align:left;">
                                                Se le recuerda que dispone de <strong>5 días naturales</strong> para realizar la revisión. Ofrecemos una disculpa por la premura, ya que es necesario cumplir con las fechas límite establecidas para la emisión de resultados.
                                            </p>

                                            <p class="muted" style="text-align:left; margin-bottom: 18px;">
                                                Puede acceder al artículo mediante el siguiente botón:
                                            </p>

                                            <p class="note" style="margin-top: 0; text-align: left;">
                                                Nota: Si el artículo asignado no corresponde a su área de especialidad, le solicitamos comunicarse a la brevedad al correo:
                                                <a href="mailto:citca_editores@cenidet.tecnm.mx" style="color: #2b6cb0; text-decoration: underline;">citca_editores@cenidet.tecnm.mx</a>
                                            </p>

                                            <!-- Botón -->
                                            <table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 20px 0; text-align:center;">
                                                <tr>
                                                    <td align="center">
                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                            <tr>
                                                                <td>
                                                                    <a href="{{ route('article.show', $articleData['articleId']) }}" class="button button-primary" target="_blank" rel="noopener" style="border-radius: 4px; color: #fff; display: inline-block; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;">
                                                                        Ver artículo
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Asignado por: mostrar el nombre del editor que realizó la asignación -->
                                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 18px;">
                                                <tr>
                                                    <td style="text-align:left;">
                                                        <p class="small" style="margin:0 0 8px 0;">
                                                            Asignado por:
                                                        </p>
                                                        <div style="border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 8px; color: #2d3748; font-weight: 600;">
                                                            {{ $articleData['assigner_name'] ?? 'Editor(a) de CITCA' }}
                                                        </div>

                                                        <p class="small" style="margin-top:10px; color:#4a5568;">
                                                            --------------------------<br>
                                                            Editor(a) de CITCA
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>

                                            <p style="font-size:16px; line-height:1.5; color:#718096; margin-top:18px;">
                                                Atentamente,<br>
                                                El equipo de CITCA
                                            </p>

                                        @else
                                            <!-- Si NO es reviewer: mantiene tus condiciones actuales sobre quién asignó -->
                                            <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left; color: #718096;">
                                                @if(isset($articleData['assigner_role']) && strtolower($articleData['assigner_role']) === 'admin')
                                                    Se le informa que se le ha asignado una contribución para su distribución a revisores (a). El artículo ha sido asignado por el Editor en Jefe (a): <strong>{{ $articleData['assigner_name'] ?? 'Administrador' }}</strong>.
                                                @elseif(isset($articleData['assigner_role']) && strtolower($articleData['assigner_role']) === 'editor')
                                                    El artículo ha sido asignado por el editor <strong>{{ $articleData['assigner_name'] ?? 'Editor' }}</strong>.
                                                @elseif(isset($articleData['assigner_name']))
                                                    El artículo ha sido asignado por <strong>{{ $articleData['assigner_name'] }}</strong>.
                                                @else
                                                    Este es un correo del sistema CITCA, queremos informarte que se te ha asignado un nuevo artículo.
                                                @endif

                                                <br><br>

                                                Puedes revisar el contenido a través del siguiente botón:
                                            </p>

                                            <table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 20px 0; text-align:center;">
                                                <tr>
                                                    <td align="center">
                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                            <tr>
                                                                <td>
                                                                    <a href="{{ route('article.show', $articleData['articleId']) }}" class="button button-primary" target="_blank" rel="noopener" style="border-radius: 4px; color: #fff; display: inline-block; text-decoration: none; background-color: #2d3748; border-bottom: 8px solid #2d3748; border-left: 18px solid #2d3748; border-right: 18px solid #2d3748; border-top: 8px solid #2d3748;">
                                                                        Ver artículo
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>

                                            <p style="font-size:16px; line-height:1.5; color:#718096; margin-top:18px;">
                                                Saludos,<br>
                                                El equipo de CITCA
                                            </p>
                                        @endif

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td>
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; padding: 0; text-align: center; width: 570px;">
                                <tr>
                                    <td class="content-cell" align="center" style="padding: 32px;">
                                        <p style="line-height: 1.5em; margin-top: 0; color: #b0adc5; font-size: 12px; text-align: center;">
                                            © <?php echo date('Y'); ?> CITCA. Todos los derechos reservados.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
