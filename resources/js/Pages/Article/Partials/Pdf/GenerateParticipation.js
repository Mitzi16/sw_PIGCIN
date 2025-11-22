import { PDFDocument, StandardFonts, rgb } from "pdf-lib";
import axios from "axios"; // Lo mantenemos por si acaso, aunque la llamada se hará en Vue
import QRCode from "qrcode";

/**
 * Llama a esta función desde tu componente Vue.
 * AHORA RECIBE LOS DATOS y la información extra (opcional).
 *
 * @param {object} controllerData - Los datos obtenidos de 'reconocimiento.data'.
 * @param {string|null} extraInfo - El nombre del taller o conferencia (si aplica).
 */
export async function generatePersonalRecognition(controllerData, extraInfo = null) {
    try {
        if (!controllerData || !controllerData.signedText || controllerData.signedText.startsWith("Error")) {
            console.error("El servidor falló al generar el sello.", controllerData);
            alert("No se pudo generar el reconocimiento. El servidor no pudo crear el sello digital.");
            return null;
        }

        console.log("Datos obtenidos:", controllerData);
        console.log("Información extra:", extraInfo);

        // 2. LLAMAR A LA LÓGICA DE DIBUJO DEL PDF
        // Pasamos los datos Y la información extra
        const pdfUrl = await drawRecognitionPdf(controllerData, extraInfo);
        return pdfUrl;

    } catch (error) {
        console.error("Error al generar el reconocimiento personal:", error);
        alert("Hubo un error al generar el reconocimiento.");
        return null;
    }
}

/**
 * Función principal de dibujo de PDF.
 * @param {object} controllerData - El objeto JSON EXACTO del controlador.
 * @param {string|null} extraInfo - El nombre del taller o conferencia.
 */
async function drawRecognitionPdf(controllerData, extraInfo) {
    
    const { 
        participant_name, 
        participant_role, 
        participant_area, 
        verification_url,
        reconocimiento_folio,
        signedText
    } = controllerData;

    // Cargar la plantilla PDF
    const urlPdf = "/pdfs/Reconocimiento_a25_CITCApdf.pdf";
    const existingPdfBytes = await fetch(urlPdf).then((res) => res.arrayBuffer());
    const pdfDoc = await PDFDocument.load(existingPdfBytes);
    const pages = pdfDoc.getPages();
    const firstPage = pages[0];
    const { width, height } = firstPage.getSize();
    const secondPage = pdfDoc.addPage([width, height]);

    const centerX = width / 2;
    let y;

    // --- Colores y Fuentes ---
    const authorColor = rgb(0 / 255, 0 / 255, 0 / 255);
    const goldColor = rgb(173 / 255, 132 / 255, 31 / 255);
    const defaultColor = rgb(0.2, 0.255, 0.333);
    const footerColor = rgb(0.5, 0.5, 0.5); 

    const fontBold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
    // *** La fuente 'font' es necesaria para calcular el ancho de los títulos ***
    const font = await pdfDoc.embedFont(StandardFonts.Helvetica);
    const fontMono = await pdfDoc.embedFont(StandardFonts.Courier);
    
    // --- 1. Dibujar Nombre (firstPage) ---
    let fontSize = 20;
    const maxNameWidth = 500;
    const wrappedName = wrapText(participant_name, maxNameWidth, fontBold, fontSize);
    const nameLines = wrappedName.split('\n');
    const lineHeight = fontSize * 1.5;
    const totalTextBlockHeight = (nameLines.length - 1) * lineHeight;
    let currentY = (height / 2 + 30) + (totalTextBlockHeight / 2);

    for (const line of nameLines) {
        let textWidth = fontBold.widthOfTextAtSize(line, fontSize);
        let x = (centerX - textWidth / 2) - 20; 
        firstPage.drawText(line, {
            x: x, y: currentY - fontSize / 2, 
            font: fontBold, size: fontSize, color: authorColor, 
        });
        currentY -= lineHeight;
    }

    // --- 2. Dibujar Texto Principal (firstPage) ---
    const authorBlockHeight = nameLines.length * lineHeight;
    // Establecer la posición Y inicial del bloque de texto
    y = (height / 2 + 30) - (authorBlockHeight / 2) - 40; 
    
    // ¡CAMBIO IMPORTANTE! Pasamos 'extraInfo' Y el objeto 'font' a 'getParticipationText'
    const participationLines = getParticipationText(participant_role, participant_area, extraInfo, font);

    // Definir el espaciado y ajuste de posición X
    const dynamicLineSpacing = 16;
    const line2Offset = 20; // Separación después de la línea principal/título
    const staticXOffset = 25; // Offset para las líneas estáticas 1 y 2
    const centerOffset = 25; // Offset general para centrar

    // Dibujar las 6 líneas (3 dinámicas + 3 estáticas)
    for (let i = 0; i < participationLines.length; i++) {
    const line = participationLines[i];
    
    // 1. Inicializa la fuente a la regular
    let currentFont = font; 
    let xOffset = centerOffset;
    let lineSpacing = dynamicLineSpacing;

    // APLICAR NEGRITAS AL TÍTULO (Líneas 1 y 2)
    if (i === 1 || i === 2) { 
        currentFont = fontBold; // Usa la fuente en negritas
    }

    // Ajuste de X para las líneas estáticas 1 y 2
    if (i === 3 || i === 4) {
        xOffset = staticXOffset;
        currentFont = font; // Asegurar la fuente regular para las estáticas
    }

    // *** CAMBIO CRUCIAL: USAR currentFont EN LA LLAMADA A drawText ***
    drawText(line, currentFont, 10, firstPage, y, centerX - xOffset, defaultColor);
    
    // Ajuste de Y para el salto de línea extra
    if (i === 2) { // Después de la última línea dinámica (o la tercera línea del título)
        y -= line2Offset; 
        // No es necesario resetear currentFont aquí, ya se hace al inicio del bucle.
    } else {
        y -= lineSpacing;
        // No es necesario resetear currentFont aquí.
    }
}
    
    // --- 3. Dibujar Fecha (firstPage) ---
    drawText("Cuernavaca, Morelos, 28 de noviembre de 2025", font, 10, firstPage, y, centerX - 20, goldColor);
    
    // --- 4. Dibujar QR (secondPage, Izquierda) ---
    await addRecognitionQR(pdfDoc, secondPage, verification_url, height);

    // --- 5. Dibujar Folio y Sello (secondPage, Derecha) ---
    const qrX = 50;
    const qrSize = 90;
    const topMarginY = height - 120;
    const spacing = 20;
    const rightPageMargin = width - 50;

    const sealLeftX = qrX + qrSize + spacing;
    const sealWidth = rightPageMargin - sealLeftX;
    let sealY = topMarginY + 90; // Ajuste de alineación vertical

    drawText(`Folio: ${reconocimiento_folio}`, font, 8, secondPage, sealY, sealLeftX, footerColor, 'left');
    sealY -= 15;
    drawText("Sello Digital (Identificador):", fontBold, 8, secondPage, sealY, sealLeftX, footerColor, 'left');
    sealY -= 15; 

    const selloLines = splitAndJustifyText(signedText, fontMono, 7, sealWidth);
    const selloLineHeight = 8;
    
    for (const line of selloLines) {
        secondPage.drawText(line, { x: sealLeftX, y: sealY, font: fontMono, size: 7, color: footerColor });
        sealY -= selloLineHeight;
    }

    // --- 6. Texto de Validación ---
    sealY -= 8;
    const validationText = "La firma electrónica que sustituye a la firma autógrafa del firmante, garantiza la integridad de la constancia y producirá los mismos efectos que las leyes otorgan a los documentos con firma autógrafa";
    const validationFontSize = 7;
    const validationLineHeight = 8;
    const validationLines = wrapText(validationText, sealWidth, font, validationFontSize).split('\n');

    for (const line of validationLines) {
        drawText(line, font, validationFontSize, secondPage, sealY, sealLeftX, footerColor, 'left');
        sealY -= validationLineHeight;
    }

    // --- 7. Guardar el PDF ---
    const pdfBytes = await pdfDoc.save();
    const blob = new Blob([pdfBytes], { type: "application/pdf" });
    const url = URL.createObjectURL(blob);
    return url;
}


// --- Funciones de Ayuda (Helpers) ---

/**
 * ¡FUNCIÓN MODIFICADA!
 * Ahora acepta 'extraInfo' y el objeto 'font' de pdf-lib.
 * Retorna un array de 6 líneas: [linea_dinamica_1, linea_dinamica_2, linea_dinamica_3 (o vacío), static1, static2, static3]
 */
function getParticipationText(role, area, extraInfo, font) {
    let lines = [];
    
    const staticLines = [
        " En el marco del 5.° Congreso Internacional de Tecnología y Ciencia Aplicada",
        " (CITCA), celebrado en el Centro Nacional de Investigación y Desarrollo",
        "Tecnológico del 26 al 28 de Noviembre de 2025."
    ];
    
    const MAX_TITLE_WIDTH = 480; 
    const FONT_SIZE = 10; // Tamaño de la fuente para el texto principal
    
    switch (role) { 
        case "TALLERISTA":
            lines.push("Por su destacada participación impartiendo el taller:");
            
            const tallerTitle = extraInfo || 'Nombre no especificado';
            // Usa wrapText para dividir el título. Se asume que font.widthOfTextAtSize está disponible.
            const wrappedTallerTitle = wrapText(`"${tallerTitle}"`, MAX_TITLE_WIDTH, font, FONT_SIZE); 
            
            // Añadir las líneas del título (limitado a 3 líneas)
            const tallerTitleLines = wrappedTallerTitle.split('\n').slice(0, 3);
            lines.push(...tallerTitleLines);
            
            // Rellenar con líneas vacías si el título es corto para mantener la estructura de 3 líneas dinámicas
            while (lines.length < 3) { lines.push(""); }
            break;

        case "CONFERENCISTA":
            lines.push("Por su destacada participación como conferencista magistral con la ponencia:");
            
            const confTitle = extraInfo || 'Nombre no especificado';
            // Usa wrapText para dividir el título.
            const wrappedConfTitle = wrapText(`"${confTitle}"`, MAX_TITLE_WIDTH, font, FONT_SIZE); 
            
            // Añadir las líneas del título (limitado a 3 líneas)
            const confTitleLines = wrappedConfTitle.split('\n').slice(0, 3);
            lines.push(...confTitleLines);
            
            // Rellenar con líneas vacías si el título es corto para mantener la estructura de 3 líneas dinámicas
            while (lines.length < 3) { lines.push(""); }
            break;

        case "EDITOR_REVISOR":
            lines.push("Por su destacada doble labor como Editor (a)  de contribuciones en el área:");
            lines.push(` ${area || 'Conocimiento'}`);
            lines.push(""); // Línea de relleno
            break;
        case "REVISOR_RE":
            lines.push("Por su destacada doble labor como Revisor (a)  de contribuciones en el área:");
            lines.push(` ${area || 'Conocimiento'}`);
            lines.push(""); // Línea de relleno
            break;
        case "ADMIN":
            lines.push("Por su destacada e invaluable participación en la actividad de Coordinador (a) General,");
            lines.push(" como integrante del COMITÉ ORGANIZADOR");
            lines.push(""); // Línea de relleno
            break;
        case "EDITOR":
            lines.push("Por su destacada participación como Editor (a) de contribuciones en el área:");
            lines.push(` ${area || 'Conocimiento'} `);
            lines.push(""); // Línea de relleno
            break;
        case "REVISOR":
            lines.push("Por su destacada participación como Revisor (a) de contribuciones en el área:");
            lines.push(` ${area || 'Conocimiento'}`);
            //lines.push("presentadas")
            lines.push(""); // Línea de relleno
            break;
        case "ADMIN-REVISTA":
            lines.push("Por su destacada e invaluable participacion en la actividad de Administrador (a) General de");
            lines.push("Revista, como integrante del COMITÉ ORGANIZADOR");
            lines.push(""); // Línea de relleno
            break;
        case "STAFF":
            lines.push("Por su destacada e invaluable participación como miembro del");
            lines.push("EQUIPO ORGANIZADOR");
            lines.push(""); // Línea de relleno
            break;
        case "MODERADOR":
            lines.push("Por su destacada e invaluable participación como");
            lines.push("MODERADOR (A) DE CONTRIBUCIONES");
            lines.push(""); // Línea de relleno
            break;
        case "ASISTENTE":
        default:
            lines.push("Por su valiosa asistencia y participación en las actividades generales del congreso");
            lines.push("como integrante del COMITÉ ORGANIZADOR");
            lines.push(""); // Línea de relleno
            break;
    }
    
    // Asegurar que siempre hay 3 líneas dinámicas antes de las estáticas
    while (lines.length < 3) { lines.push(""); }
    
    // Agregar las líneas estáticas
    lines.push(...staticLines);

    return lines;
}

// --- El resto de funciones helper (addRecognitionQR, drawText, etc.) no cambian ---

const addRecognitionQR = async (pdfDoc, page, verificationUrl, height) => { 
    // (Sin cambios)
    const qr = await generateRecognitionQR(verificationUrl); 
    if (!qr) { console.error("No se pudo generar el QR"); return; }
    const base64Data = qr.replace(/^data:image\/(png|jpeg);base64,/, '');
    const imageBytes = Uint8Array.from(atob(base64Data), c => c.charCodeAt(0));
    const image = await pdfDoc.embedPng(imageBytes); 
    const qrFixedSize = 90;
    const qrX = 50; 
    const qrY = height - 120;
    page.drawImage(image, { x: qrX, y: qrY, width: qrFixedSize, height: qrFixedSize });
    page.drawRectangle({ x: qrX, y: qrY, width: qrFixedSize, height: qrFixedSize, borderWidth: 1, borderColor: rgb(0, 0, 0) });
}

const generateRecognitionQR = async (url) => { 
    // (Sin cambios)
    if (!url) return null;
    try {
        return await QRCode.toDataURL( url, {
            type: "image/png",
            color: { dark: "#000000", light: "#ffffff" },
        });
    } catch (error) {
        console.error("Error al generar QR:", error);
        return null; 
    }
};

const drawText = (text, font, fontSize, page, y, xPos, color = rgb(0.2, 0.255, 0.333), align = 'center') => {
    // (Sin cambios)
    let x;
    if (align === 'left') { 
        x = xPos;
    } else {
        const textWidth = font.widthOfTextAtSize(text, fontSize);
        x = xPos - textWidth / 2;
    }
    page.drawText(text, { x: x, y: y - fontSize / 2, maxWidth: 520, font: font, color: color, size: fontSize });
};

const wrapText = (text, width, font, fontSize) => {
    // (Sin cambios)
    if (!text) { return ''; }
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

const splitAndJustifyText = (text, font, fontSize, maxWidth) => {
    // (Sin cambios)
    if (!text) return []; 
    const words = text.split(""); 
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