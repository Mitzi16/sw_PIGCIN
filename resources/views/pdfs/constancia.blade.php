<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carta de Invitación {{ $article->article_folio ?? '' }}</title>

<style>
/* ================================
   CONFIGURACIÓN GENERAL DE PÁGINA
================================= */
@page {
    size: A4;
    margin: 80px 40px 80px 40px; /* Márgenes reducidos */
}

html, body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    line-height: 1.5;
    color: #000;
    margin: 0;
    padding: 0;
    width: 100%;
}

body {
    padding-top: 100px; /* espacio reducido para header */
    padding-bottom: 70px; /* espacio reducido para footer */
    position: relative;
}

/* ================================
   ENCABEZADO
================================= */
.header {
    position: fixed;
    top: 30px; /* ajustado al nuevo margen */
    left: 0;
    right: 0;
    height: 80px; /* altura reducida */
    text-align: center;
}

.header-images {
    display: block;
    width: 100%;
    text-align: center;
    margin: 0;
    padding: 0;
}

.header-images img {
    height: 50px; /* tamaño reducido */
    display: inline-block;
    margin: 0 8px; /* espacio reducido entre logos */
}

.header-text {
    font-size: 9px;
    font-weight: bold;
    text-align: right;
    margin-top: 2px;
    padding-right: 5px; /* padding reducido */
}

/* ================================
   PIE DE PÁGINA
================================= */
.footer {
    position: fixed;
    bottom: 30px; /* ajustado al nuevo margen */
    left: 0;
    right: 0;
    height: 60px; /* altura reducida */
    text-align: center;
}

.footer img {
    width: 100%;
    height: auto;
    max-width: 100%;
}

/* ================================
   CONTENIDO PRINCIPAL
================================= */
main {
    margin-top: 10px;
    width: 100%;
}

.right { 
    text-align: right; 
    margin: 0;
    padding: 0;
}
.center { 
    text-align: center; 
    margin: 0;
    padding: 0;
}
.bold { 
    font-weight: bold; 
}
.section-space { 
    margin-top: 15px; /* espacio reducido */
    margin-bottom: 10px;
    margin: 1.5%;
}
p { 
    text-align: justify; 
    margin-bottom: 8px; /* espacio reducido */
    margin-top: 0;
    padding: 0;
}

.p {
    font-size: 11px;
    margin-left: 1.5%;
    margin-right: 1.5%;
    text-align: justify;
}

/* ================================
   BLOQUE FINAL (Firmas y QR)
================================= */
.final-content-wrapper {
    margin-top: 20px; /* espacio reducido */
    page-break-inside: avoid;
}

/* === BLOQUE "ATENTAMENTE" === */
.atentamente-block {
    margin-bottom: 8px; /* espacio reducido */
}

.atentamente-block p {
    line-height: 1.1;
    margin: 0;
    padding: 0;
}

/* === BLOQUE DE FIRMAS === */
.signatures-container {
    text-align: center;
    margin-bottom: 8px; /* espacio reducido */
    page-break-after: avoid;
    width: 100%;
}

.signatures-row {
    width: 100%;
    margin-top: 8px; /* espacio reducido */
    margin-bottom: 8px;
}

.signature {
    width: 48%;
    font-weight: bold;
    font-size: 9px;
    line-height: 1.4;
    text-align: center;
    display: inline-block;
    vertical-align: top;
    margin: 0;
    padding: 0;
}

.signature a {
    color: #000;
    text-decoration: none;
}

.signature-right {
    text-align: center;
    float: right;
    margin-right: 1.5%;
}

.signature-center {
    margin-top: 6px; /* espacio reducido */
    display: block;
    text-align: center;
    font-weight: bold;
    font-size: 9px;
    line-height: 1.4;
    clear: both;
    margin-bottom: 0;
}

/* === BLOQUE QR Y SELLO DIGITAL === */
.validation-container {
    margin-top: 12px; /* espacio reducido */
    page-break-inside: avoid;
    width: 100%;
}

.qr-code {
    width: 18%;
    text-align: center;
    display: inline-block;
    vertical-align: top;
    margin: 0;
    padding: 0;
}

.qr-code img {
    width: 100%;
    height: auto;
    max-width: 90px; /* tamaño ligeramente reducido */
}

.qr-code-text {
    font-size: 6px;
    margin-top: 2px; /* espacio reducido */
    text-align: justify;
    font-weight: bold;
}

.digital-seal {
    width: 78%;
    display: inline-block;
    vertical-align: top;
    margin: 0;
    padding: 0;
}

.digital-seal .seal-title {
    font-weight: bold;
    font-size: 7px;
    margin-bottom: 2px; /* espacio reducido */
}

.digital-seal .seal-text {
    font-size: 7px;
    line-height: 1.2;
    color: #333;
    text-align: justify;
    word-wrap: break-word;
    overflow-wrap: break-word;
    margin: 0;
    padding: 0;
}

/* Eliminar cualquier padding o margen adicional */
* {
    box-sizing: border-box;
}
</style>
</head>

<body>

<div class="header">
    <div class="header-images">
        <img src="{{ public_path('img/R.png') }}" alt="Logo 1">
        <img src="{{ public_path('img/lgo.png') }}" alt="Logo 2">
        <img src="{{ public_path('img/bn.png') }}" alt="Logo 3">
    </div>
    <div class="header-text">
        Centro Nacional de Investigación y Desarrollo Tecnológico <br>
        Departamento de Ingeniería Electrónica
    </div>
</div>

<main>
    <div class="right">
        Cuernavaca, Mor. 31 de octubre de 2025
    </div>

    <div class="section-space">
        <span class="bold">ASUNTO:</span> Carta de aceptación e invitación a participar en el 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA 2025)
    </div>

    <p class="p">Estimados(as) autores(as):</p>

    <p class="p"><strong>{{ $authors_list }}</strong></p>

    <p class="p">Afiliaciones: <strong>{{ $institutions_list ?? 'N/A' }}</strong></p>

    <p class="p">
        Por este medio, el Comité Organizador del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA 2025)
        tiene el agrado de informarle que su contribución:
    </p>

    <p class="center bold p">"{{ $article->title }}"</p>

    <p class="p">
        Tipo de contribución: <strong>{{ $article_type_name }}</strong><br>
        ID de contribución: <strong>{{ $article->article_folio }}</strong>
    </p>

    <p class="p">
        Ha sido <strong>{{ $article->articleStatus->name ?? 'aceptada' }}</strong> para presentarse dentro del programa académico
        del congreso, tras un proceso de revisión por pares especialistas en el área.
        El CITCA 2025 se celebrará del 26 al 28 de noviembre de 2025 en las instalaciones del
        Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET), ubicado en Cuernavaca, Morelos, México.
    </p>

    <p class="p">
        Les extendemos una cordial invitación a presentar su trabajo y participar en las actividades programadas,
        incluyendo conferencias magistrales, sesiones técnicas y espacios de intercambio académico.
        Agradecemos su contribución y quedamos atentos a su participación en el 5to CITCA 2025.
    </p>

    <p class="p">Reciban un cordial saludo.</p>

    <!-- BLOQUE FINAL -->
    <div class="final-content-wrapper">
        <div class="signatures-container">
            <div class="atentamente-block">
                <p class="center bold">A T E N T A M E N T E</p>
                <p class="center" style="font-size: 10px;">
                    <em>Excelencia en Educación Tecnológica®</em><br>
                    "Conocimiento y Tecnología al Servicio de México"
                </p>
            </div>

            <div class="signatures-row">
                <div class="signature">
                    Dra. Marisol Cervantes Bobadilla<br>
                    Editora General del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)<br>
                    Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET) TecNM<br>
                    <a href="mailto:citca_editores@cenidet.tecnm.mx">citca_editores@cenidet.tecnm.mx</a>
                </div>

                <div class="signature signature-right">
                    Dr. Manuel Adam Medina<br>
                    Presidente del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)<br>
                    Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET)  TecNM<br>
                    <a href="mailto:citca@cenidet.tecnm.mx">citca@cenidet.tecnm.mx</a>
                </div>
            </div>

            <div class="signature-center">
                Dr. Ricardo Fabricio Escobar Jiménez<br>
                Editor en Jefe Revista Tecnología y Ciencia Aplicada<br>
                Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET)  TecNM<br>
                <a href="mailto:vin_cenidet@tecnm.mx">vin_cenidet@tecnm.mx</a>
            </div>
        </div>

        <div class="validation-container">
            <div class="qr-code">
                <img src="{{ $qrCodeBase64 ?? '' }}" alt="Código QR de Verificación">
            </div>
            <div class="digital-seal">
                <p class="seal-title">Sello Digital:</p>
                <p class="seal-text">{{ $signedText ?? 'Sello no disponible' }}</p>
                <p class="qr-code-text">
                La firma electrónica que sustituye a la firma autógrafa del firmante, 
                garantiza la integridad de la constancia y producirá los mismos efectos que las leyes 
                otorgan a los documentos con firma autógrafa</p>
            </div>
            
        </div>
    </div>
</main>

<div class="footer">
    <img src="{{ public_path('img/piep.png') }}" alt="Pie de página">
</div>

</body>
</html>