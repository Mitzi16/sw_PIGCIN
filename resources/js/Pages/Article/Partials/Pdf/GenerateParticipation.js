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
    const urlPdf = "/pdfs/Templates.pdf";
    const existingPdfBytes = await fetch(urlPdf).then((res) => res.arrayBuffer());
    const pdfDoc = await PDFDocument.load(existingPdfBytes);
    const pages = pdfDoc.getPages();
    const firstPage = pages[0];
    const { width, height } = firstPage.getSize();
    const secondPage = pdfDoc.addPage([width, height]);

    const centerX = width / 2;
    let y;

    // --- Colores y Fuentes ---
    const authorColor = rgb(118 / 255, 117 / 255, 116 / 255);
    const goldColor = rgb(173 / 255, 132 / 255, 31 / 255);
    const defaultColor = rgb(0.2, 0.255, 0.333);
    const footerColor = rgb(0.5, 0.5, 0.5); 

    const fontBold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
    const font = await pdfDoc.embedFont(StandardFonts.Helvetica);
    const fontMono = await pdfDoc.embedFont(StandardFonts.Courier);
    
    // --- 1. Dibujar Nombre (firstPage) ---
    // (Esta sección no cambia)
    let fontSize = 15;
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
    y = (height / 2 + 30) - (authorBlockHeight / 2) - 40; 
    
    // ¡CAMBIO IMPORTANTE! Pasamos 'extraInfo' a 'getParticipationText'
    const [line1, line2, staticLine1, staticLine2, staticLine3] = getParticipationText(participant_role, participant_area, extraInfo);

    // El resto del dibujo de texto es igual
    drawText(line1, font, 10, firstPage, y, centerX - 20, defaultColor);
    y -= 16;
    drawText(line2, font, 10, firstPage, y, centerX - 20, defaultColor);
    y -= 20;
    drawText(staticLine1, font, 10, firstPage, y, centerX - 25, defaultColor);
    y -= 16;
    drawText(staticLine2, font, 10, firstPage, y, centerX - 25, defaultColor);
    y -= 16;
    drawText(staticLine3, font, 10, firstPage, y, centerX - 20, defaultColor);
    y -= 25;

    // --- 3. Dibujar Fecha (firstPage) ---
    drawText("Cuernavaca, Morelos, 28 de noviembre de 2025", font, 10, firstPage, y, centerX - 20, goldColor);
    
    // --- 4. Dibujar QR (secondPage, Izquierda) ---
    await addRecognitionQR(pdfDoc, secondPage, verification_url, height);

    // --- 5. Dibujar Folio y Sello (secondPage, Derecha) ---
    // (Esta sección no cambia)
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
    sealY -= 10; 

    const selloLines = splitAndJustifyText(signedText, fontMono, 7, sealWidth);
    const selloLineHeight = 8;
    
    for (const line of selloLines) {
         secondPage.drawText(line, { x: sealLeftX, y: sealY, font: fontMono, size: 7, color: footerColor });
        sealY -= selloLineHeight;
    }

    // --- 6. Texto de Validación ---
    // (Esta sección no cambia)
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
 * Ahora acepta 'extraInfo' para los roles especiales.
 */
function getParticipationText(role, area, extraInfo) {
    let line1 = "";
    let line2 = "";
    
    switch (role) { 
        case "TALLERISTA":
            line1 = "Por su destacada participación impartiendo el taller:";
            // Si 'extraInfo' tiene valor, lo usamos.
            line2 = `"${extraInfo || 'Nombre no especificado'}"`;
            break;
        case "CONFERENCISTA":
            line1 = "Por su destacada participación como conferencista magistral con la ponencia:";
            // Si 'extraInfo' tiene valor, lo usamos.
            line2 = `"${extraInfo || 'Nombre no especificado'}"`;
            break;
            // case "EDITOR_REVISOR" y "REVISOR_RE" no lo estoy ocupando con el nuevo flujo no es necesario tener esto
        case "EDITOR_REVISOR":
            line1 = "Por su destacada doble labor como Editor (a)  de contribuciones en el área:";
            line2 = ` ${area || 'Conocimiento'}`;
            break;
        case "REVISOR_RE":
            line1 = "Por su destacada doble labor como Revisor (a)  de contribuciones en el área:";
            line2 = ` ${area || 'Conocimiento'}`;
            break;
        case "ADMIN":
            line1 = "Por su destacada e invaluable participación en la actividad de Coordinador (a)";
            line2 = "General, como integrante del COMITÉ ORGANIZADOR";
            break;
        case "EDITOR":
            line1 = "Por su destacada participación como Editor (a) de contribuciones en el área:";
            line2 = ` ${area || 'Conocimiento'} presentadas en el marco`;
            break;
        case "REVISOR":
            line1 = "Por su destacada participación como Revisor (a) de contribuciones en el área:";
            line2 = ` ${area || 'Conocimiento'} presentadas en el marco`;
            break;
        case "ADMIN-REVISTA":
            line1 = "Por su destacada e invaluable participacion en la actividad de Administrador (a) General";
            line2 = "de Revista, como integrante del COMITÉ ORGANIZADOR";
            break;
        case "STAFF":
            line1 = "Por su destacada e invaluable participación como miembro del";
            line2 = "EQUIPO ORGANIZADOR";
            break;
        case "MODERADOR":
            line1 = "Por su destacada e invaluable participación como";
            line2 = "MODERADOR (A) DE CONTRIBUCIONES";
            break;
        case "ASISTENTE":
        default:
            line1 = "Por su valiosa asistencia y participación en las actividades generales del congreso";
            line2 = "como integrante del COMITÉ ORGANIZADOR";
            break;
    }

    const staticLine1 = " En el marco del 5.° Congreso Internacional de Tecnología y Ciencia Aplicada";
    const staticLine2 = " (CITCA), celebrado en el Centro Nacional de Investigación y Desarrollo";
    const staticLine3 = "Tecnológico del 26 al 28 de Noviembre de 2025.";

    return [line1, line2, staticLine1, staticLine2, staticLine3];
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