import { PDFDocument, rgb } from "pdf-lib"; 
import fontkit from "@pdf-lib/fontkit"; // <--- 1. IMPORTAR FONTKIT
import axios from "axios";
import QRCode from "qrcode";

/**
 * Llama a esta función desde tu botón "Generar Reconocimiento Grupal".
 */
export async function generateGroupCertificate(articleId, articleTitle) {
    try {
        console.log("Obteniendo autores para el artículo:", articleId);
        
        // Ajusta la ruta de axios según tu configuración de Laravel/Inertia (route o url directa)
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
        return null;
    }
}

export default async function getPdf(authors, title, id, articleTypeName, knowledgeAreaName) { 
    const signedText = await getSignedText(id);
    
    // --- Lógica de formateo de autores ---
    let text = ""; 
    if (Array.isArray(authors)) {
        if (authors.length > 1) {
            const lastAuthor = authors[authors.length - 1];
            const otherAuthors = authors.slice(0, -1); 
            text = otherAuthors.join(' | ') + ' | ' + lastAuthor;
        } else if (authors.length === 1) {
            text = authors[0]; 
        }
    } else {
        text = authors;
    }

    // 1. Cargar PDF base
    const urlPdf = "/pdfs/Reconocimiento_a25_CITCApdf.pdf";
    // Verificamos que cargue el PDF
    const existingPdfBytes = await fetch(urlPdf).then((res) => {
        if (!res.ok) throw new Error("No se encontró la plantilla PDF en " + urlPdf);
        return res.arrayBuffer();
    });

    const pdfDoc = await PDFDocument.load(existingPdfBytes);

    // --- 2. REGISTRAR FONTKIT (Obligatorio) ---
    pdfDoc.registerFontkit(fontkit);

    const pages = pdfDoc.getPages();
    const firstPage = pages[0];
    const { width, height } = firstPage.getSize();
    const secondPage = pdfDoc.addPage([width, height]);

    const centerX = width / 2;
    let y; 

    // --- Definición de Colores ---
    const authorColor = rgb(0 / 255, 0 / 255, 0 / 255);
    const goldColor = rgb(173 / 255, 132 / 255, 31 / 255);   // Dorado #AD841F
    const defaultColor = rgb(0.2, 0.255, 0.333); 
    const footerColor = rgb(0.5, 0.5, 0.5); 

    // --- 3. CARGAR FUENTES ROBOTO (.TTF) ---
    // Usamos las mismas rutas que arreglamos antes: /fonts/ (plural)
    const fontUrl       = "/pdfs/font/Roboto-Regular.ttf";
    const fontBoldUrl   = "/pdfs/font/Roboto-Bold.ttf";
    
    // Nota: Como probablemente no tienes RobotoMono.ttf, usaremos 
    // Roboto-Regular para el sello también. Esto evita errores de carga.
    // Si tienes Roboto-Italic.ttf, puedes cargarlo si lo necesitas.

    let fontBytes, fontBoldBytes;

    try {
        [fontBytes, fontBoldBytes] = await Promise.all([
            fetch(fontUrl).then(res => {
                if(!res.ok) throw new Error(`404: ${fontUrl}`); 
                return res.arrayBuffer();
            }),
            fetch(fontBoldUrl).then(res => {
                if(!res.ok) throw new Error(`404: ${fontBoldUrl}`);
                return res.arrayBuffer();
            })
        ]);
    } catch (e) {
        console.error("Error cargando fuentes .ttf:", e);
        throw new Error("Faltan archivos en public/fonts/. Ver consola.");
    }

    // Incrustar las fuentes
    const font = await pdfDoc.embedFont(fontBytes, { subset: true });
    const fontBold = await pdfDoc.embedFont(fontBoldBytes, { subset: true });
    
    // REEMPLAZO DE COURIER: Usamos la fuente regular para soportar símbolos
    const fontMono = font; 

    // --- Dibujo de Autores (Página 1) ---
    let fontSize = 15; 
    const maxAuthorWidth = 500; 
    const wrappedText = wrapText(text, maxAuthorWidth, fontBold, fontSize); 
    const textLines = wrappedText.split('\n');
    const lineHeight = fontSize * 1.5; 

    const totalTextBlockHeight = (textLines.length - 1) * lineHeight;
    let currentY = (height / 2 + 50) + (totalTextBlockHeight / 2);

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
    y = (height / 2 + 30) - (authorBlockHeight / 2) - 10; 

    // --- DIBUJO DEL QR EN LA SEGUNDA PÁGINA ---
    await addQR(pdfDoc, secondPage, height, id); 

    // --- INICIO DEL BLOQUE DE TEXTO (Página 1) ---
    
    // "Por su destacada participación..."
    drawText(`Por su destacada participación con la Contribución: ${articleTypeName},`, font, 10, firstPage, y, centerX - 20, defaultColor);
    y -= 16; 

    // "en el área..."
    drawText(`en el área: ${knowledgeAreaName}, con el Título:`, font, 10, firstPage, y, centerX - 20, defaultColor);
    y -= 25; 

    // "[TITULO]" (Aquí es donde fallaba el CO2, ahora funcionará)
    const titleFontSize = 12;
    const titleMaxWidth = 500; 
    const wrappedTitle = wrapText(title, titleMaxWidth, fontBold, titleFontSize);
    const titleLines = wrappedTitle.split('\n');
    
    for (const line of titleLines) {
        drawText(line, fontBold, titleFontSize, firstPage, y, centerX - 20, defaultColor);
        y -= (titleFontSize + 4); 
    }

    y -= 10;

    drawText("presentada en el marco del 5.° Congreso Internacional de Tecnología y Ciencia", font, 10, firstPage, y, centerX - 25, defaultColor);
    y -= 16; 

    drawText("Aplicada (CITCA), celebrado en el Centro Nacional de Investigación y Desarrollo", font, 10, firstPage, y, centerX - 25, defaultColor);
    y -= 16; 

    drawText("Tecnológico del 26 al 28 de Noviembre de 2025.", font, 10, firstPage, y, centerX - 20, defaultColor);
    y -= 25; 
    
    // --- FIN DEL BLOQUE DE TEXTO ---

    // Fecha
    drawText("Cuernavaca, Morelos, 24 de noviembre de 2025", font, 10, firstPage, y, centerX - 20, goldColor);
    
    // --- DIBUJO DEL SELLO DIGITAL (Página 2) ---
    
    const qrX = 50;
    const qrSize = 90;
    const topMarginY = height - 120; 
    const spacing = 20;
    const rightPageMargin = width - 50;

    const sealLeftX = qrX + qrSize + spacing; 
    const sealWidth = rightPageMargin - sealLeftX; 
    let sealY = topMarginY + 90; 

    // 1. Título del Sello
    drawText('Sello Digital:', font, 10, secondPage, sealY, sealLeftX, footerColor, 'left');
    sealY -= 15;
    const selloFontSize = 8;

    // 2. Texto del Sello (Usamos fontMono que ahora es Roboto-Regular)
    // Esto asegura que si el sello tiene caracteres raros, no explote el PDF
    const lines = splitAndJustifyText(signedText, fontMono, selloFontSize, sealWidth); 
    const selloLineHeight = selloFontSize + 3;

    for (const line of lines) {
        drawText(line, fontMono, selloFontSize, secondPage, sealY, sealLeftX, footerColor, 'left');
        sealY -= selloLineHeight;
    }

    // 3. Texto Legal
    const legalText = "La firma electrónica que sustituye a la firma autógrafa del firmante, garantiza la integridad de la constancia y producirá los mismos efectos que las leyes otorgan a los documentos con firma autógrafa";
    const legalFontSize = 8;
    const legalLineHeight = 9;

    const validationLines = wrapText(legalText, sealWidth, font, legalFontSize).split('\n');

    sealY -= 8; 
    for (const line of validationLines) {
        drawText(line, font, legalFontSize, secondPage, sealY, sealLeftX, footerColor, 'left');
        sealY -= legalLineHeight;
    }

    // Guardado
    const pdfBytes = await pdfDoc.save();
    const blob = new Blob([pdfBytes], { type: "application/pdf" });
    const url = URL.createObjectURL(blob);
    return url;
}

const getSignedText = async (id) => {
    try {
        // Asegúrate que esta ruta exista en tu backend
        const response = await axios.get(route("article.signPdf", id)); 
        return response.data.signedText;
    } catch (error) {
        console.error("ERROR AXIOS:", error);
        return "ERROR_AL_OBTENER_SELLO_DIGITAL"; 
    }
};

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
        y: y - fontSize / 2, 
        maxWidth: 520, 
        font: font, 
        color: color, 
        size: fontSize 
    });
};

const splitAndJustifyText = (text, font, fontSize, maxWidth) => {
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

const addQR = async (pdfDoc, page, height, id) => { 
    const qr = await generateQR(id);
    if (!qr) {
        console.error("No se pudo generar el QR");
        return;
    }
    const base64Data = qr.replace(/^data:image\/(png|jpeg);base64,/, '');
    const imageBytes = Uint8Array.from(atob(base64Data), c => c.charCodeAt(0));
    const image = await pdfDoc.embedJpg(imageBytes); 

    const qrFixedSize = 90;
    const qrX = 50; 
    const qrY = height - 120; 

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
        borderColor: rgb(0, 0, 0), 
    });
}

const generateQR = async (id) => { 
    // Ajusta la ruta a tu validador
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