import { PDFDocument, StandardFonts, rgb } from "pdf-lib";
import QRCode from "qrcode";

/**
 * Función principal para generar la CARTA DE ACEPTACIÓN.
 * * @param {object} articleData Un objeto que contiene toda la información necesaria:
 */
export async function generateAcceptanceLetter(articleData) {
    try {
        console.log("Generando carta para el artículo:", articleData.article_folio);
        
        const signedText = articleData.signedText;

        if (!signedText) {
             throw new Error("No se pudo obtener el sello digital (no se incluyó en los datos).");
        }

        // 2. Generar el PDF
        const pdfUrl = await getLetterPdf(articleData, signedText);

        return pdfUrl;

    } catch (error) {
        console.error("Error al generar la carta de aceptación:", error);
        alert("Hubo un error al generar la carta: " + error.message);
        return null;
    }
}

async function getLetterPdf(data, signedText) {
    
    // 1. Cargar plantilla de hoja membretada
    const urlPdf = "/pdfs/Membrete.pdf"; // ¡Ruta a tu plantilla!
    const existingPdfBytes = await fetch(urlPdf).then((res) =>
        res.arrayBuffer()
    );
    const pdfDoc = await PDFDocument.load(existingPdfBytes);
    const pages = pdfDoc.getPages();
    const firstPage = pages[0];
    const { width, height } = firstPage.getSize(); // Aprox. 612 x 792 (Letter)
    const secondPage = pdfDoc.addPage([width, height]);

    // 2. Cargar Fuentes
    const font = await pdfDoc.embedFont(StandardFonts.Helvetica);
    const fontBold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
    const fontItalic = await pdfDoc.embedFont(StandardFonts.HelveticaOblique);
    const color = rgb(0.2, 0.2, 0.2); // Un color de texto oscuro

    // 3. Definir Layout (¡Ajustar según sea necesario!)
    const leftMargin = 72; // 1 pulgada
    const rightMargin = width - 72;
    const contentWidth = rightMargin - leftMargin; // 468pt
    
    // <-- CAMBIO 1: Más cerca del encabezado (antes 150)
    let y = height - 130; 
    
    // <-- CAMBIO 2: Interlineado 1.0
    const lineHeight = 10; // 10pt font (1.0 interlineado)
    const smallLineHeight = 9; // Para fuente de 8pt y 9pt (1.0 interlineado)
    const paragraphSpacing = 10; // Espacio entre párrafos (equivale a una línea)

    // 4. Dibujar Contenido (Página 1)

    // --- Fecha (Asumida, pero necesaria) ---
    const today = new Date().toLocaleDateString("es-MX", { day: 'numeric', month: 'long', year: 'numeric' });
    const dateStr = `Cuernavaca, Morelos, a 31 de Octubre de 2025`; 
    const dateWidth = font.widthOfTextAtSize(dateStr, 10);
    // Ajustamos la 'y' de la fecha para que use la lógica de drawWrappedText (y - fontSize)
    firstPage.drawText(dateStr, { x: rightMargin - dateWidth, y: y - 10, font: font, size: 10, color });
    y -= (lineHeight * 2.5); // Doble espacio

    // --- ASUNTO ---
    const asuntoText = "Carta de aceptación e invitación a participar en el 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA 2025)";
    const fontSizeAsunto = 10; // Definir para alinear
    // <-- CAMBIO 3: Ajuste de 'y' para alinear en la misma línea base
    firstPage.drawText("ASUNTO:", { x: leftMargin, y: y - fontSizeAsunto, font: fontBold, size: fontSizeAsunto, color }); 
    const asuntoLabelWidth = fontBold.widthOfTextAtSize("ASUNTO: ", fontSizeAsunto);
    y = drawWrappedText(asuntoText, firstPage, font, fontSizeAsunto, leftMargin + asuntoLabelWidth, y, contentWidth - asuntoLabelWidth, lineHeight, 'left', color);
    // <-- CAMBIO 3: Eliminada la línea 'y += lineHeight'
    // <-- CAMBIO 3: Ajuste de espaciado (antes y -= (lineHeight + paragraphSpacing))
    y -= paragraphSpacing; 

    // --- Estimados(as) ---
    y = drawWrappedText("Estimados(as) autores(as):", firstPage, font, 10, leftMargin, y, contentWidth, lineHeight, 'left', color);
    y -= paragraphSpacing;

    // --- Autores y Afiliaciones ---
    // <-- CAMBIO 4: Alinear al centro
    y = drawWrappedText(data.authors_list, firstPage, fontBold, 10, leftMargin, y, contentWidth, lineHeight, 'center', color);
    y -= (lineHeight / 2);
    y = drawWrappedText(`Afiliaciones: ${data.institutions_list || 'N/A'}`, firstPage, font, 10, leftMargin, y, contentWidth, lineHeight, 'left', color);
    y -= paragraphSpacing;

    // --- Párrafo 1 ---
    const p1 = "Por este medio, el Comité Organizador del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA 2025) tiene el agrado de informarle que su contribución:";
    y = drawWrappedText(p1, firstPage, font, 10, leftMargin, y, contentWidth, lineHeight, 'left', color);
    y -= paragraphSpacing;

    // --- Título del Artículo ---
    // Aumentamos el lineHeight para el título a 12 (11pt font + 1)
    y = drawWrappedText(`"${data.article_title}"`, firstPage, fontBold, 11, leftMargin, y, contentWidth, 12, 'center', color);
    y -= paragraphSpacing;

    // --- Detalles de Contribución ---
    // Usamos 'y - 10' para alinear con la lógica de drawWrappedText
    const detailY = y - 10; 
    firstPage.drawText("Tipo de contribución:", { x: leftMargin, y: detailY, font: font, size: 10, color });
    firstPage.drawText(data.article_type_name, { x: leftMargin + 105, y: detailY, font: fontBold, size: 10, color });
    y -= lineHeight; // Mover 'y' para la siguiente línea
    
    // Usamos 'y - 10' para alinear
    const detailY2 = y - 10;
    firstPage.drawText("ID de contribución:", { x: leftMargin, y: detailY2, font: font, size: 10, color });
    firstPage.drawText(data.article_folio, { x: leftMargin + 95, y: detailY2, font: fontBold, size: 10, color });
    y -= (lineHeight + paragraphSpacing); // Mover 'y' y agregar espacio

    // --- Párrafo 2 (Aceptación) ---
    const p2 = `Ha sido Aceptado para presentarse dentro del programa académico del congreso, tras un proceso de revisión por pares especialistas en el área. El CITCA 2025 se celebrará del 26 al 28 de noviembre de 2025 en las instalaciones del Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET), ubicado en Cuernavaca, Morelos, México.`;
    y = drawWrappedText(p2, firstPage, font, 10, leftMargin, y, contentWidth, lineHeight, 'justify', color); // 'justify' es difícil, usamos 'left'
    y -= paragraphSpacing;
    
    // --- Párrafo 3 (Invitación) ---
    const p3 = "Les extendemos una cordial invitación a presentar su trabajo y participar en las actividades programadas, incluyendo conferencias magistrales, sesiones técnicas y espacios de intercambio académico. Agradecemos su contribución y quedamos atentos a su participación en el 5to CITCA 2025.";
    y = drawWrappedText(p3, firstPage, font, 10, leftMargin, y, contentWidth, lineHeight, 'justify', color);
    y -= paragraphSpacing;
    
    // --- Saludo ---
    y = drawWrappedText("Reciban un cordial saludo.", firstPage, font, 10, leftMargin, y, contentWidth, lineHeight, 'left', color);
    y -= (lineHeight * 2.5); // Más espacio

    // --- ATENTAMENTE ---
    const centerX = width / 2;
    y = drawWrappedText("A T E N T A M E N T E", firstPage, fontBold, 10, centerX - (contentWidth/2), y, contentWidth, lineHeight, 'center', color);
    y = drawWrappedText("Excelencia en Educación Tecnológica®", firstPage, fontItalic, 8, centerX - (contentWidth/2), y, contentWidth, smallLineHeight, 'center', color);
    y = drawWrappedText('"Conocimiento y Tecnología al Servicio de México"', firstPage, fontItalic, 8, centerX - (contentWidth/2), y, contentWidth, smallLineHeight, 'center', color);
    y -= (lineHeight * 3.5); // Espacio para firmas físicas/digitales

    // --- Bloques de Firmas ---
    const sigBlockWidth = contentWidth / 2.2; // Ancho de cada bloque de firma
    const sigY = y; // Guardar 'y' para la segunda columna
    const col1_X = leftMargin;
    const col2_X = rightMargin - sigBlockWidth;
    const center_X = centerX - (sigBlockWidth / 2);

    // Columna 1
    let y_col1 = sigY;
    y_col1 = drawWrappedText("Dra. Marisol Cervantes Bobadilla", firstPage, fontBold, 9, col1_X, y_col1, sigBlockWidth, smallLineHeight, 'center', color);
    y_col1 = drawWrappedText("Editora General del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)", firstPage, font, 8, col1_X, y_col1, sigBlockWidth, smallLineHeight, 'center', color);
    y_col1 = drawWrappedText("Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET) TecNM", firstPage, font, 8, col1_X, y_col1, sigBlockWidth, smallLineHeight, 'center', color);
    y_col1 = drawWrappedText("citca_editores@cenidet.tecnm.mx", firstPage, font, 8, col1_X, y_col1, sigBlockWidth, smallLineHeight, 'center', color);

    // Columna 2
    let y_col2 = sigY;
    y_col2 = drawWrappedText("Dr. Manuel Adam Medina", firstPage, fontBold, 9, col2_X, y_col2, sigBlockWidth, smallLineHeight, 'center', color);
    y_col2 = drawWrappedText("Presidente del 5to Congreso Internacional de Tecnología y Ciencia Aplicada (CITCA)", firstPage, font, 8, col2_X, y_col2, sigBlockWidth, smallLineHeight, 'center', color);
    y_col2 = drawWrappedText("Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET) TecNM", firstPage, font, 8, col2_X, y_col2, sigBlockWidth, smallLineHeight, 'center', color);
    y_col2 = drawWrappedText("citca@cenidet.tecnm.mx", firstPage, font, 8, col2_X, y_col2, sigBlockWidth, smallLineHeight, 'center', color);

    // Firma Centrada (debajo de las otras)
    let y_center = Math.min(y_col1, y_col2) - paragraphSpacing; // Ponerla debajo de la más larga
    y_center = drawWrappedText("Dr. Ricardo Fabricio Escobar Jiménez", firstPage, fontBold, 9, center_X, y_center, sigBlockWidth, smallLineHeight, 'center', color);
    y_center = drawWrappedText("Editor en Jefe Revista Tecnología y Ciencia Aplicada", firstPage, font, 8, center_X, y_center, sigBlockWidth, smallLineHeight, 'center', color);
    y_center = drawWrappedText("Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET) TecNM", firstPage, font, 8, center_X, y_center, sigBlockWidth, smallLineHeight, 'center', color);
    y_center = drawWrappedText("vin_cenidet@tecnm.mx", firstPage, font, 8, center_X, y_center, sigBlockWidth, smallLineHeight, 'center', color);


    // 5. Dibujar Contenido (Página 2)
    // --- QR ---
    //const qrContent = `ID: ${data.article_folio}\nTitulo: ${data.article_title}\nAutores: ${data.authors_list}`;
    const qrContent = data.verification_url; // Usar URL de verificación directa
    await addQRToSecondPage(pdfDoc, secondPage, qrContent, height);

    // --- Sello Digital (Replicando lógica original) ---
    let sealY = height - 72; // Margen superior en Pág 2
    
    // Definimos el layout basado en el QR para poner el sello a la DERECHA
    
    // 1. Obtenemos las dimensiones del QR (de la función addQRToSecondPage)
    const qrLeft = 30;
    const qrWidth = 150;
    const spacing = 15; // Espacio que queremos entre el QR y el Sello
    const rightPageMarginX = width - 72; // Posición 'x' del margen derecho (612 - 72 = 540)

    // 2. Calculamos la nueva posición y ancho del bloque del Sello
    let sealLeftX = qrLeft + qrWidth + spacing; // 30 + 90 + 15 = 135
    let sealWidth = rightPageMarginX - sealLeftX; // 540 - 135 = 405

    const sealFontSize = 6;
    const sealColor = rgb(0.2, 0.255, 0.333); // Color del script original

    // 3. Calculamos el centrado del TÍTULO del sello
    const sealTitle = 'Sello Digital:';
    const sealTitleSize = 10;
    const titleWidth = fontBold.widthOfTextAtSize(sealTitle, sealTitleSize);
    
    // Calculamos la 'x' para que el título quede centrado DENTRO del nuevo bloque
    const sealTitleX = sealLeftX + (sealWidth - titleWidth) / 2;

    secondPage.drawText(sealTitle, {
        x: sealTitleX, // Usamos la nueva 'x' centrada
        y: sealY - sealTitleSize, // Ajuste para alinear con lógica de 'y'
        font: fontBold,
        size: sealTitleSize,
        color: sealColor
    });
    
    sealY -= (lineHeight + 5);

    // Reutilizamos la lógica de justificación de caracteres del script original
    // ESTO FUNCIONA AUTOMÁTICAMENTE porque usa las variables 'sealLeftX' y 'sealWidth'
    // que acabamos de calcular.
    const lines = splitAndJustifyText(signedText, font, sealFontSize, sealWidth);
    
    lines.forEach((line, index) => {
        sealY -= (sealFontSize + 2); // Espaciado entre líneas (ajustado para 1.0)
        let lineWidth = font.widthOfTextAtSize(line, sealFontSize);
        let currentX = sealLeftX; // Inicia en la nueva 'x' (135)
        let spaceWidth = 0;

        // Lógica de justificación
        if (index < lines.length - 1 && line.length > 1) {
            // Ajusta al nuevo 'sealWidth' (405)
            spaceWidth = (sealWidth - lineWidth) / (line.length - 1);
        } else {
            spaceWidth = 0;
        }

        for (let char of line) {
            secondPage.drawText(char, {
                x: currentX,
                y: sealY - sealFontSize / 2, // Ajuste de línea base
                font: font,
                size: sealFontSize,
                color: sealColor,
            });
            currentX += font.widthOfTextAtSize(char, sealFontSize) + spaceWidth;
        }
    });

    sealY -= (lineHeight * 2); // Espacio después del sello
    
    // --- Texto de validación QR ---
    // Esto también usa 'sealLeftX' y 'sealWidth', por lo que se ajusta solo.
    const validationText = "La firma electrónica que sustituye a la firma autógrafa del firmante, garantiza la integridad de la constancia y producirá los mismos efectos que las leyes otorgan a los documentos con firma autógrafa";
    drawWrappedText(validationText, secondPage, font, 6, sealLeftX, sealY, sealWidth, smallLineHeight, 'left', sealColor);

    // 6. Guardar y devolver
    const pdfBytes = await pdfDoc.save();
    const blob = new Blob([pdfBytes], { type: "application/pdf" });
    const url = URL.createObjectURL(blob);
    return url;
};

/**
 * [NUEVO HELPER]
 * Dibuja texto con ajuste de línea en una página.
 * Devuelve la coordenada 'y' final después de dibujar.
 */
function drawWrappedText(
    text,
    page,
    font,
    fontSize,
    x,
    y,
    maxWidth,
    lineHeight,
    align = 'left',
    color = rgb(0, 0, 0)
) {
    const lines = wrapText(text, maxWidth, font, fontSize).split('\n');
    let currentY = y;
    const spaceWidth = font.widthOfTextAtSize(' ', fontSize); // Ancho de un espacio normal

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const textWidth = font.widthOfTextAtSize(line, fontSize);
        
        // Coordenada Y (es la misma para todas las alineaciones)
        const lineBaseY = currentY - fontSize;

        // --- INICIO DE LÓGICA DE JUSTIFICACIÓN ---
        // Justificar solo si align es 'justify' Y no es la última línea
        if (align === 'justify' && i < lines.length - 1) {
            const words = line.split(' ');
            const spaceCount = words.length - 1;

            // No justificar si hay 0 o 1 palabra en la línea
            if (spaceCount > 0) {
                // 1. Calcular el espacio total a rellenar
                const gapToFill = maxWidth - textWidth;
                
                // 2. Calcular el espacio extra a añadir a cada "espacio"
                const extraSpacePerGap = gapToFill / spaceCount;
                
                // 3. El espacio total entre palabras será el normal + el extra
                const totalSpacePerGap = spaceWidth + extraSpacePerGap;

                let currentX = x; // Empezar en el margen izquierdo
                
                // 4. Dibujar la línea palabra por palabra
                for (const word of words) {
                    page.drawText(word, {
                        x: currentX,
                        y: lineBaseY,
                        font: font,
                        size: fontSize,
                        color: color,
                    });
                    // Mover la 'X' para la siguiente palabra
                    currentX += font.widthOfTextAtSize(word, fontSize) + totalSpacePerGap;
                }
            } else {
                // Si solo hay una palabra, alinearla a la izquierda
                page.drawText(line, { x, y: lineBaseY, font, size: fontSize, color });
            }
        
        // --- FIN DE LÓGICA DE JUSTIFICACIÓN ---

        } else {
            // Lógica anterior para 'left', 'center', 'right'
            // (También se usa para la última línea de un bloque justificado)
            let currentX = x;

            if (align === 'center') {
                currentX = x + (maxWidth - textWidth) / 2;
            } else if (align === 'right') {
                currentX = x + (maxWidth - textWidth);
            }
            // 'left' usa currentX = x (por defecto)

            page.drawText(line, {
                x: currentX,
                y: lineBaseY, 
                font: font,
                size: fontSize,
                color: color,
            });
        }
        
        currentY -= lineHeight; // Mover 'y' hacia abajo para la siguiente línea
    }
    return currentY; // Devuelve la nueva posición 'y'
}

/**
 * [HELPER ORIGINAL - ELIMINADO]
 * getSignedText ya no es necesario, el script de JS asume
 * que 'articleData.signedText' ya existe.
 */
// const getSignedText = async (id) => { ... }; // <-- ELIMINADO


/**
 * [HELPER ORIGINAL - Mantenido]
 * Divide el texto del sello en líneas justificadas caracter por caracter.
 */
const splitAndJustifyText = (text, font, fontSize, maxWidth) => {
    if (!text) return [];
    const words = text.split(""); // Divide por caracteres
    let lines = [];
    let currentLine = words[0] || "";

    for (let i = 1; i < words.length; i++) {
        let char = words[i];
        let width = font.widthOfTextAtSize(currentLine + char, fontSize);

        if (width < maxWidth) {
            currentLine += char;
        } else {
            lines.push(currentLine);
            currentLine = char;
        }
    }
    lines.push(currentLine);
    return lines;
};

/**
 * [HELPER ORIGINAL - Mantenido]
 * Divide texto en líneas por palabras según un ancho máximo.
 */
const wrapText = (text, width, font, fontSize) => {
    if (!text) return ""; // Guarda contra texto nulo
    const words = text.split(' ');
    let line = '';
    let result = '';
    for (let n = 0; n < words.length; n++) {
        const testLine = line + words[n] + ' ';
        const testWidth = font.widthOfTextAtSize(testLine, fontSize);
        if (testWidth > width && n > 0) {
            result += line.trim() + '\n';
            line = words[n] + ' ';
        } else {
            line = testLine;
        }
    }
    result += line.trim();
    return result;
};

/**
 * [HELPER MODIFICADO]
 * Genera el QR. Ahora toma solo 1 argumento.
 */
const generateQR = (qrContent) => {
    let qr;
    QRCode.toDataURL(
        qrContent, // Contenido dinámico
        {
            type: "image/jpeg",
            errorCorrectionLevel: "L",
            color: {
                dark: "#000000",
                light: "#ffffff",
            },
        },
        function (error, url) {
            if (error) {
                console.error(error);
            } else {
                qr = url;
            }
        }
    );
    return qr;
};

/**
 * [HELPER MODIFICADO]
 * Añade el QR a la segunda página.
 */
const addQRToSecondPage = async (pdfDoc, secondPage, qrContent, height) => {
    const qr = generateQR(qrContent);
    if (!qr) {
        console.error("No se pudo generar el QR");
        return;
    }
    const base64Data = qr.replace(/^data:image\/(png|jpeg);base64,/, '');
    const imageBytes = Uint8Array.from(atob(base64Data), c => c.charCodeAt(0));
    const image = await pdfDoc.embedJpg(imageBytes);
    
    const qrFixedSize = 150;
    // Colocar el QR en la esquina superior izquierda (con margen)
    const qrX = 30; // Margen izquierdo
    const qrY = height - 72 - qrFixedSize; // Margen superior
    
    secondPage.drawImage(image, {
        x: qrX,
        y: qrY,
        width: qrFixedSize,
        height: qrFixedSize,
    });

    secondPage.drawRectangle({
        x: qrX,
        y: qrY,
        width: qrFixedSize,
        height: qrFixedSize,
        borderWidth: 1,
        borderColor: rgb(0, 0, 0),
    });
}