import { PDFDocument, StandardFonts, rgb } from "pdf-lib"; 
import axios from "axios";
import QRCode from "qrcode";

/**
 * Llama a esta función desde tu botón "Generar Reconocimiento Grupal".
 */
export async function generateGroupCertificate(articleId, articleTitle) {
    try {
        console.log("Obteniendo autores para el artículo:", articleId);
        
        const response = await axios.get(route("article.getAuthors", articleId));

        const { 
            authors: authorsArray, 
            article_type_name,  
            knowledge_area_name
        } = response.data;
        
        if (!authorsArray || authorsArray.length === 0) {
            console.error("No se encontraron autores para este artículo.");
            return null;
        }
        
        console.log("Autores encontrados:", authorsArray);

       const degreeRegex = /^(Doctor\(a\)|Maestro\(a\)|Ingeniero\(a\)|Licenciado\(a\)|Profesor\(a\)|Estudiante\(a\))\.?\s+/i;
        const authorsWithoutDegrees = authorsArray.map(authorName => {
            // Reemplaza el grado (si lo encuentra) con un string vacío
            return authorName.replace(degreeRegex, ''); 
        });

        const pdfUrl = await getPdf(
            authorsWithoutDegrees, 
            articleTitle, 
            articleId,
            article_type_name,  
            knowledge_area_name  
        );

        return pdfUrl;

    } catch (error) {
        console.error("Error al generar el certificado grupal:", error);
        console.error("Hubo un error al obtener los autores del artículo.");
        return null;
    }
}

export default async function getPdf(authors, title, id, articleTypeName, knowledgeAreaName) { 
    const signedText = await getSignedText(id);
    
    let text = ""; 
    if (Array.isArray(authors)) {
        // Es un arreglo (GRUPAL), formato "A | B y C"
        if (authors.length > 1) {
            const lastAuthor = authors[authors.length - 1];
            const otherAuthors = authors.slice(0, -1); 
            text = otherAuthors.join(' | ') + ' y ' + lastAuthor;
        } else if (authors.length === 1) {
            text = authors[0]; 
        }
    } else {
        text = authors;
    }

    const urlPdf = "/pdfs/Reconocimiento_a25_CITCApdf.pdf";
    const existingPdfBytes = await fetch(urlPdf).then((res) =>
        res.arrayBuffer()
    );
    const pdfDoc = await PDFDocument.load(existingPdfBytes);
    const pages = pdfDoc.getPages();
    const firstPage = pages[0];
    const { width, height } = firstPage.getSize();
    const secondPage = pdfDoc.addPage([width, height]);

    const centerX = width / 2;
    let y; 

    // --- Definición de Colores ---
    const authorColor = rgb(118 / 255, 117 / 255, 116 / 255); // Gris #767574
    const goldColor = rgb(173 / 255, 132 / 255, 31 / 255);   // Dorado #AD841F
    const defaultColor = rgb(0.2, 0.255, 0.333); // Color por defecto (gris oscuro)
    const footerColor = rgb(0.5, 0.5, 0.5); // Gris solicitado para el pie de página

    const fontBold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
    let font = await pdfDoc.embedFont(StandardFonts.Helvetica); // Cargar Helvetica
    const fontMono = await pdfDoc.embedFont(StandardFonts.Courier); // <--- NUEVO: Cargar fuente monoespaciada
    
    // --- Dibujo de Autores (Página 1) ---
    let fontSize = 15; 
    const maxAuthorWidth = 500; 
    const wrappedText = wrapText(text, maxAuthorWidth, fontBold, fontSize); // Usamos fontBold para autores
    const textLines = wrappedText.split('\n');
    const lineHeight = fontSize * 1.5; 

    const totalTextBlockHeight = (textLines.length - 1) * lineHeight;
    let currentY = (height / 2 + 30) + (totalTextBlockHeight / 2);

    for (const line of textLines) {
        let textWidth = fontBold.widthOfTextAtSize(line, fontSize);
        let x = (centerX - textWidth / 2) - 20; 
        
        firstPage.drawText(line, {
            x: x,
            y: currentY - fontSize / 2, 
            font: fontBold,
            size: fontSize,
            color: authorColor, 
        });
        currentY -= lineHeight; 
    }

    // Ajustar la 'y' para que esté debajo del bloque de autores
    const authorBlockHeight = textLines.length * lineHeight;
    y = (height / 2 + 30) - (authorBlockHeight / 2) - 40; // 40px de espacio

    // --- DIBUJO DEL QR EN LA SEGUNDA PÁGINA (Nuevo estilo) ---
    await addQR(pdfDoc, secondPage, height, id); // <-- Llama a la función actualizada

    // --- INICIO DEL BLOQUE DE TEXTO (Página 1) ---
    
    // "Por su destacada participación con la Contribución: [articleTypeName],"
    drawText(`Por su destacada participación con la Contribución: ${articleTypeName},`, font, 10, firstPage, y, centerX - 20, defaultColor);
    y -= 16; 

    // "en el área: [knowledgeAreaName], con el Título:"
    drawText(`en el área: ${knowledgeAreaName}, con el Título:`, font, 10, firstPage, y, centerX - 20, defaultColor);
    y -= 25; 

    // "[title]" (El título en negrita y más grande)
    drawText(title, fontBold, 12, firstPage, y, centerX - 20, defaultColor);
    y -= 32; 

    // "presentada en el marco del 5.° Congreso Internacional de Tecnología y Ciencia"
    drawText("presentada en el marco del 5.° Congreso Internacional de Tecnología y Ciencia Aplicada", font, 10, firstPage, y, centerX - 25, defaultColor);
    y -= 16; 

    // "Aplicada (CITCA), celebrado en el Centro Nacional de Investigación y Desarrollo"
    drawText(" (CITCA), celebrado en el Centro Nacional de Investigación y Desarrollo", font, 10, firstPage, y, centerX - 25, defaultColor);
    y -= 16; 

    // "Tecnológico del 26 al 28 de Noviembre de 2025."
    drawText("Tecnológico del 26 al 28 de Noviembre de 2025.", font, 10, firstPage, y, centerX - 20, defaultColor);
    y -= 25; 
    
    // --- FIN DEL BLOQUE DE TEXTO (Página 1) ---

    // Fecha
    drawText("Cuernavaca, Morelos, 24 de noviembre de 2025", font, 10, firstPage, y, centerX - 20, goldColor);
    
    // --- DIBUJO DEL SELLO DIGITAL Y TEXTO LEGAL (Página 2 - Nuevo estilo) ---
    
    // Definir geometría de posicionamiento
    const qrX = 50;
    const qrSize = 90;
    const topMarginY = height - 120; // Referencia de la parte superior del QR
    const spacing = 20;
    const rightPageMargin = width - 50;

    const sealLeftX = qrX + qrSize + spacing; // Posición X de inicio del sello (a la derecha del QR)
    const sealWidth = rightPageMargin - sealLeftX; // Ancho máximo del bloque de sello
    let sealY = topMarginY + 90; // Posición Y de inicio (cercana a la parte superior de la página)

    // 1. Título del Sello Digital
    drawText('Sello Digital:', font, 10, secondPage, sealY, sealLeftX, footerColor, 'left');
    sealY -= 15;
    const selloFontSize = 8;

    // 2. Texto del Sello Digital (usando fontMono y left alignment)
    const lines = splitAndJustifyText(signedText, fontMono, selloFontSize, sealWidth); 
    const selloLineHeight = selloFontSize + 3;

    for (const line of lines) {
        // Usa drawText con align='left' y fontMono
        drawText(line, fontMono, selloFontSize, secondPage, sealY, sealLeftX, footerColor, 'left');
        sealY -= selloLineHeight;
    }

    // 3. Texto Legal (Debajo del Sello)
    const legalText = "La firma electrónica que sustituye a la firma autógrafa del firmante, garantiza la integridad de la constancia y producirá los mismos efectos que las leyes otorgan a los documentos con firma autógrafa";
    const legalFontSize = 8;
    const legalLineHeight = 9;

    // Usamos wrapText para manejar el salto de línea del texto legal
    const validationLines = wrapText(legalText, sealWidth, font, legalFontSize).split('\n');

    sealY -= 8; // Espacio entre el sello y el texto legal
    for (const line of validationLines) {
        // Usa drawText con align='left' y font normal
        drawText(line, font, legalFontSize, secondPage, sealY, sealLeftX, footerColor, 'left');
        sealY -= legalLineHeight;
    }
    // -----------------------------------------------------------------

    // guardado
    const pdfBytes = await pdfDoc.save();
    // Crear un Blob con los datos del PDF
    const blob = new Blob([pdfBytes], { type: "application/pdf" });
    const url = URL.createObjectURL(blob);
    return url;
}

const getSignedText = async (id) => {
    try {
        const response = await axios.get(route("article.signPdf", id)); 
        return response.data.signedText;
    } catch (error) {
        console.error("ERROR AXIOS:", error);
        return null;
    }
};

/**
 * Función mejorada para dibujar texto con soporte de alineación 'center' (predeterminado) o 'left'.
 * @param {string} text - El texto a dibujar.
 * @param {PDFDocument.PDFPage} page - La página del PDF.
 * @param {number} y - Posición Y (vertical) de la línea de base.
 * @param {number} xPos - Posición X de referencia (centro para 'center', inicio para 'left').
 * @param {PDFDocument.RGB} color - El color del texto.
 * @param {string} align - Alineación ('center' o 'left').
 */
const drawText = (text, font, fontSize, page, y, xPos, color = rgb(0.2, 0.255, 0.333), align = 'center') => {
    let x;
    if (align === 'left') { 
        x = xPos;
    } else {
        const textWidth = font.widthOfTextAtSize(text, fontSize);
        x = xPos - textWidth / 2;
    }
    page.drawText(text, { 
        x: x, 
        y: y - fontSize / 2, // Ajustar para centrar verticalmente
        maxWidth: 520, 
        font: font, 
        color: color, 
        size: fontSize 
    });
};

const splitAndJustifyText = (text, font, fontSize, maxWidth) => {
    if (!text) return []; // Guarda contra texto nulo o vacío
    const words = text.split(""); // Dividir por caracteres
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

const wrapText = (text, width, font, fontSize) => {
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
}

/**
 * Dibuja el QR en la segunda página con el posicionamiento estándar.
 */
const addQR = async (pdfDoc, page, height, id) => { 
    const qr = await generateQR(id);
    if (!qr) {
        console.error("No se pudo generar el QR");
        return;
    }
    const base64Data = qr.replace(/^data:image\/(png|jpeg);base64,/, '');
    const imageBytes = Uint8Array.from(atob(base64Data), c => c.charCodeAt(0));
    const image = await pdfDoc.embedJpg(imageBytes); 

    // Posicionamiento de personal_recognition.js (Segunda página, esquina superior izquierda)
    const qrFixedSize = 90;
    const qrX = 50; 
    const qrY = height - 120; // Alineado con el Top Margin

    page.drawImage(image, {
        x: qrX,
        y: qrY,
        width: qrFixedSize,
        height: qrFixedSize,
    });

    page.drawRectangle({
        x: qrX,
        y: qrY,
        width: qrFixedSize,
        height: qrFixedSize,
        borderWidth: 1,
        borderColor: rgb(0, 0, 0), // 0, 0, 0 es negro
    });
}

const generateQR = async (id) => { 
    const verificationUrl = route('certificate.verify', id);
    
    try {
        const url = await QRCode.toDataURL(
            verificationUrl, 
            {
                type: "image/jpeg",
                color: {
                    dark: "#000000",
                    light: "#ffffff",
                },
            }
        );
        return url; 
    
    } catch (error) {
        console.error("Error al generar QR:", error);
        return null; 
    }
};