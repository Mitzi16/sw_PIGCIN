<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta de Invitación {{ $article->article_folio ?? '' }}</title>
    <style>
        @page {
            size: A4;
            margin: 80px 70px 100px 70px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #000;
            position: relative;
        }

        /* === Encabezado === */
        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 100px;
            text-align: center;
        }

        .header-images {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .header-images img {
            height: 60px;
            object-fit: contain;
        }

        .header-text {
            font-size: 9px;
            font-weight: bold;
            text-align: right;
            margin-top: 4px;
        }

        /* === Pie de página === */
        footer {
            position: fixed;
            bottom: -70px;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
        }

        footer img {
            width: 100%;
            height: auto;
        }

        /* === Contenido principal === */
        main {
            margin-top: 40px;
        }

        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .section-space { margin-top: 20px; }
        p { text-align: justify; margin-bottom: 10px; }

        /* === Bloque de firmas === */
       .signatures-container {
    margin-top: 10px;
    text-align: center;
    page-break-inside: avoid;
}

/* 1. ESTE ES EL CONTENEDOR 'TABLE' */
.signatures-row {
    display: table;      /* Trátalo como una tabla */
    width: 100%;         /* Ocupa todo el ancho */
    margin: 0;           /* Resetea el margen que tenía */
    border-spacing: 20px; /* Esto crea el espacio ENTRE las firmas */
    border-collapse: separate; /* Necesario para que funcione el 'border-spacing' */
}

/* 2. ESTA ES LA CELDA 'TABLE-CELL' (se aplica a ambas firmas) */
.signature {
    display: table-cell;   /* Trátalo como una celda de tabla */
    width: 50%;            /* Cada celda ocupa la mitad */
    font-weight: bold;
    font-size: 9px;
    line-height: 1.4;
    vertical-align: top; /* <-- Esta es la ALINEACIÓN VERTICAL clave */
    box-sizing: border-box;
}

.signature a {
    color: #000;
    text-decoration: none;
}

/* 3. SOLO ALINEAMOS EL TEXTO */
.signature-left {
    text-align: left;
}

.signature-right {
    text-align: right;
}

        .signature-center {
            margin-top: 25px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            line-height: 1.4;
        }

        .p {
            font-size: 11px;
        }

        /* === Ajuste de espaciado solo para el bloque ATENTAMENTE === */
        .atentamente-block p {
            line-height: 1.0;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    <header>
        <div class="header-images">
            <img src="{{ public_path('img/R.png') }}" alt="Logo 1">
            <img src="{{ public_path('img/lgo.png') }}" alt="Logo 2">
            <img src="{{ public_path('img/bn.png') }}" alt="Logo 3">
        </div>
        <div class="header-text ">
            Centro Nacional de Investigación y Desarrollo tecnológico <br>
            Departamento Ingeniería Electrónica
        </div>
    </header>

    <main>
        <div class="right">
            Cuernavaca, Mor. 31 de octubre de 2025
        </div>

        <div class="section-space">
            ASUNTO: Carta de aceptación e invitación a participar en el 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA 2025)
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

        <div class="signatures-container">
            <div class="atentamente-block">
                <p class="center bold">A T E N T A M E N T E</p>
                <p class="center" style="font-size: 10px;">
                    <em>Excelencia en Educación Tecnológica®</em><br>
                    “Conocimiento y Tecnología al Servicio de México”
                </p>
            </div>

            <div class="signatures-row">
                <div class="signature signature-left">
                    Dra. Marisol Cervantes Bobadilla<br>
                    Editora General del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)<br>
                    Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET) TecNM<br>
                    <a href="mailto:citca_editores@cenidet.tecnm.mx">citca_editores@cenidet.tecnm.mx</a> 
                </div>

                <div class="signature signature-right">
                    Dr. Manuel Adam Medina<br>
                    Presidente del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)<br>
                    Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET) TecNM<br>
                    <a href="mailto:citca@cenidet.tecnm.mx">citca@cenidet.tecnm.mx</a> 
                </div>
            </div>

            <div class="signature-center">
                Dr. Ricardo Fabricio Escobar Jiménez<br>
                Editor en Jefe Revista Tecnología y Ciencia Aplicada<br>
                Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET) TecNM<br>
                <a href="mailto:vin_cenidet@tecnm.mx">vin_cenidet@tecnm.mx</a>
            </div>
        </div>
    </main>

    <footer>
        <img src="{{ public_path('img/piep.png') }}" alt="Pie de página">
    </footer>
 
</body>
</html>