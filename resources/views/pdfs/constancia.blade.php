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
    font-family: DejaVu Sans, sans-serif; /* Fuente base (para header, footer) */
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

/* =========================================
   NUEVO: FORMATO CARTA INVITACIÓN (PÁG 1)
========================================= */
.first-page-content {
    /* Fuente serif elegante. Se aplica solo al contenido de la pág 1 */
    font-family: DejaVu Serif, Georgia, "Times New Roman", serif; 
    page-break-after: avoid; /* Intenta mantener todo el contenido de la pág 1 junto */
}

/* Párrafos principales de la carta */
.first-page-content .p {
    font-size: 10px;      /* Tamaño de letra más legible */
    line-height: 1.6;     /* Más espacio entre líneas */
    margin-bottom: 14px;  /* Más espacio entre párrafos */
    margin-left: 1.5%;
    margin-right: 1.5%;
}

/* Lista de autores */
.author-list {
    font-family: DejaVu Serif, Georgia, "Times New Roman", serif;
    text-align: center;
    font-weight: bold;
    font-size: 12px;
    margin-top: 20px;
    margin-bottom: 5px;
    color: #333;
}

/* Lista de afiliaciones */
.affiliation-list {
    font-family: DejaVu Serif, Georgia, "Times New Roman", serif;
    text-align: center;
    font-style: italic;
    font-size: 11px;
    margin-bottom: 20px;
    color: #555;
}

/* Título del artículo */
.paper-title {
    font-family: DejaVu Serif, Georgia, "Times New Roman", serif;
    text-align: center;
    font-weight: bold;
    font-size: 14px; /* Más grande */
    font-style: italic;
    margin-top: 20px;
    margin-bottom: 20px;
    padding-left: 10%; /* Indentado para centrarlo mejor */
    padding-right: 10%;
    line-height: 1.5;
    color: #111;
}


/* ================================
   BLOQUE DE FIRMAS (PÁG 1) - CON NUEVO FORMATO
================================= */
.signatures-wrapper {
    margin-top: 20px;
    page-break-inside: avoid; /* Intenta no romper el bloque de firmas */
    font-family: DejaVu Serif, Georgia, "Times New Roman", serif; /* Fuente formal */
}

/* === BLOQUE "ATENTAMENTE" === */
.atentamente-block {
    margin-bottom: 8px;
}

.atentamente-block p {
    line-height: 1.1;
    margin: 0;
    padding: 0;
}

.atentamente-block .bold {
    font-size: 12px;
    letter-spacing: 1px; /* Espaciado entre letras */
    margin-bottom: 5px;
}

/* === BLOQUE DE FIRMAS === */
.signatures-container {
    text-align: center;
    margin-bottom: 8px;
    page-break-after: avoid;
    width: 100%;
}

.signatures-row {
    width: 100%;
    margin-top: 8px;
    margin-bottom: 8px;
}

.signature {
    width: 48%;
    font-size: 10px; /* Un poco más grande */
    line-height: 1.5; /* Más espaciado */
    text-align: center;
    display: inline-block;
    vertical-align: top;
    margin: 0;
    padding: 0;
}

/* NUEVO: Clase para el nombre en negrita */
.signature-name {
    font-weight: bold;
    margin-bottom: 2px; /* Espacio entre nombre y cargo */
}

.signature a {
    color: #000;
    text-decoration: none;
    font-size: 9px; /* Email más pequeño */
}

.signature-right {
    text-align: center;
    float: right;
    margin-right: 1.5%;
}

.signature-center {
    margin-top: 6px;
    display: block;
    text-align: center;
    font-size: 10px; /* Un poco más grande */
    line-height: 1.5; /* Más espaciado */
    clear: both;
    margin-bottom: 0;
}

/* =========================================
   BLOQUE DE VALIDACIÓN (PÁG 2)
========================================= */
.second-page-validation {
    page-break-before: always; /* FUERZA EL SALTO A LA PÁGINA 2 */
    margin-top: 20px; /* Espacio desde el header */
    page-break-inside: avoid;
}

/* === BLOQUE QR Y SELLO DIGITAL === */
.validation-container {
    margin-top: 12px;
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
    max-width: 120px;
    border: 1px solid black; /* AÑADIDO: Borde negro de 1px */
    padding: 2px; /* AÑADIDO: Pequeño padding para que el QR no toque el borde */
}

.qr-code-text {
    font-size: 6px;
    margin-top: 2px;
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
    margin-bottom: 2px;
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
    <div class="first-page-content">
        <div class="right">
            Cuernavaca, Mor. 31 de octubre de 2025
        </div>

        <div class="section-space">
            <span class="bold">ASUNTO:</span> Carta de aceptación e invitación a participar en el 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA 2025)
        </div>

        <p class="p">Estimados(as) autores(as):</p>

        <p class="author-list">{{ $authors_list }}</p>
        <p class="affiliation-list">Afiliaciones: {{ $institutions_list ?? 'N/A' }}</p>

        <p class="p">
            Por este medio, el Comité Organizador del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA 2025)
            tiene el agrado de informarle que su contribución:
        </p>

        <p class="paper-title">"{{ $article->title }}"</p>

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
    
    </div> <div class="signatures-wrapper">
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
                    <div class="signature-name">Dra. Marisol Cervantes Bobadilla</div>
                    Editora General del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)<br>
                    Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET) TecNM<br>
                    <a href="mailto:citca_editores@cenidet.tecnm.mx">citca_editores@cenidet.tecnm.mx</a>
                </div>

                <div class="signature signature-right">
                    <div class="signature-name">Dr. Manuel Adam Medina</div>
                    Presidente del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)<br>
                    Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET)  TecNM<br>
                    <a href="mailto:citca@cenidet.tecnm.mx">citca@cenidet.tecnm.mx</a>
                </div>
            </div>

            <div class="signature-center">
                <div class="signature-name">Dr. Ricardo Fabricio Escobar Jiménez</div>
                Editor en Jefe Revista Tecnología y Ciencia Aplicada<br>
                Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET)  TecNM<br>
                <a href="mailto:vin_cenidet@tecnm.mx">vin_cenidet@tecnm.mx</a>
            </div>
        </div>
    </div> <div class="second-page-validation">
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
    </div> </main>

<div class="footer">
    <img src="{{ public_path('img/piep.png') }}" alt="Pie de página">
</div>

</body>
</html>