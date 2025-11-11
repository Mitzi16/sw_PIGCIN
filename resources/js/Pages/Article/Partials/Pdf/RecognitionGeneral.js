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
        
        //const authorsArray = response.data.authors;

        if (!authorsArray || authorsArray.length === 0) {
            alert("No se encontraron autores para este artículo.");
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
            articleTitle, // o usa apiArticleTitle
            articleId,
            article_type_name,   // <--- NUEVO
            knowledge_area_name  // <--- NUEVO
        );
        //const pdfUrl = await getPdf(authorsWithoutDegrees, articleTitle, articleId);

        return pdfUrl;

    } catch (error) {
        console.error("Error al generar el certificado grupal:", error);
        alert("Hubo un error al obtener los autores del artículo.");
        return null;
    }
}

export default async function getPdf(authors, title, id, articleTypeName, knowledgeAreaName) { 
    const signedText = await getSignedText(id);
    // let legend = "Por su invaluable presentación del Artículo:"; // Ya no se usa
    
    let text = ""; // 'text' será la cadena final de autores
    if (Array.isArray(authors)) {
        // Es un arreglo (GRUPAL), formato "A, B y C"
        if (authors.length > 1) {
            const lastAuthor = authors[authors.length - 1];
            const otherAuthors = authors.slice(0, -1); 
            text = otherAuthors.join(' | ') + ' y ' + lastAuthor;
        } else if (authors.length === 1) {
            text = authors[0]; // Solo un autor en el arreglo
        }
    } else {
        text = authors;
    }

    const urlPdf = "/pdfs/Templates.pdf";
    const existingPdfBytes = await fetch(urlPdf).then((res) =>
        res.arrayBuffer()
    );
    const pdfDoc = await PDFDocument.load(existingPdfBytes);
    const pages = pdfDoc.getPages();
    const firstPage = pages[0];
    const { width, height } = firstPage.getSize();
    const secondPage = pdfDoc.addPage([width, height]);

    const centerX = width / 2;
    let y; // 'y' se definirá dinámicamente

    // --- Definición de Colores ---
    const authorColor = rgb(118 / 255, 117 / 255, 116 / 255); // Gris #767574
    const goldColor = rgb(173 / 255, 132 / 255, 31 / 255);   // Dorado #AD841F
    const defaultColor = rgb(0.2, 0.255, 0.333); // Color por defecto (gris oscuro)

    const fontBold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
    let font = fontBold;
    
    // --- CAMBIO 1: Tamaño de fuente de autores ---
    let fontSize = 13; // Reducido de 15 a 13

    const maxAuthorWidth = 500; // Ancho máximo para el nombre/s
    const wrappedText = wrapText(text, maxAuthorWidth, font, fontSize);
    const textLines = wrappedText.split('\n');
    const lineHeight = fontSize * 1.5; // Espaciado de 1.5

    
    const totalTextBlockHeight = (textLines.length - 1) * lineHeight;
    let currentY = (height / 2 + 30) + (totalTextBlockHeight / 2);

    for (const line of textLines) {
        let textWidth = font.widthOfTextAtSize(line, fontSize);
        
        // --- CAMBIO 2: Centrado de autores ---
        let x = (centerX - textWidth / 2) - 20; // Eliminado el '- 20' para centrar correctamente
        
        firstPage.drawText(line, {
            x: x,
            y: currentY - fontSize / 2, 
            font: font,
            size: fontSize,
            // --- CAMBIO 3: Color de autores ---
            color: authorColor, 
        });
        currentY -= lineHeight; // Mover 'y' hacia abajo para la siguiente línea
    }

    // --- CAMBIO 4: Bloque de texto principal reemplazado ---

    // Cargar la fuente normal (no negrita)
    font = await pdfDoc.embedFont(StandardFonts.Helvetica);
    
    // Ajustar la 'y' para que esté debajo del bloque de autores
    const authorBlockHeight = textLines.length * lineHeight;
    y = (height / 2 + 30) - (authorBlockHeight / 2) - 40; // 40px de espacio

    // Mover la llamada al QR aquí para que no interfiera con el flujo de 'y'
    await addQR(pdfDoc, secondPage, title, text, height, id);

    // --- INICIO DEL NUEVO BLOQUE DE TEXTO ---
    
    // "Por su destacada participación con la Contribución: [articleTypeName],"
    drawText(`Por su destacada participación con la Contribución: ${articleTypeName},`, font, 10, firstPage, y, centerX, defaultColor);
    y -= 16; // Espaciado

    // "en el área: [knowledgeAreaName], con el Título:"
    drawText(`en el área: ${knowledgeAreaName}, con el Título:`, font, 10, firstPage, y, centerX, defaultColor);
    y -= 25; // Más espacio antes del título

    // "[title]" (El título en negrita y más grande)
    drawText(title, fontBold, 12, firstPage, y, centerX, defaultColor);
    y -= 32; // Más espacio después del título

    // "presentada en el marco del 4.° Congreso Internacional de Tecnología y Ciencia"
    drawText("presentada en el marco del 5.° Congreso Internacional de Tecnología y Ciencia", font, 10, firstPage, y, centerX, defaultColor);
    y -= 16; // Espaciado

    // "Aplicada (CITCA), celebrado en el Centro Nacional de Investigación y Desarrollo"
    drawText("Aplicada (CITCA), celebrado en el Centro Nacional de Investigación y Desarrollo", font, 10, firstPage, y, centerX, defaultColor);
    y -= 16; // Espaciado

    // "Tecnológico del 21 al 23 de mayo de 2025."
    drawText("Tecnológico del 26 al 28 de Noviembre de 2025.", font, 10, firstPage, y, centerX, defaultColor);
    y -= 25; // Espacio antes de la fecha
    
    // --- FIN DEL NUEVO BLOQUE DE TEXTO ---


    // --- CAMBIO 5: Color y texto de la fecha ---
    drawText("Cuernavaca, Morelos, 24 de noviembre de 2025", font, 10, firstPage, y, centerX, goldColor);
    
    // SERV (Sello Digital en la segunda página)
    let sealY = height - 50;
    let sealLeftX = 110;
    let titleFontSize = 10;
    secondPage.drawText('Sello Digital:', {
        x: sealLeftX, // <-- Usa el margen izquierdo
        y: sealY - titleFontSize / 2,
        font: font, // (Ya es Helvetica)
        size: titleFontSize,
        color: rgb(0.2, 0.255, 0.333),
    });
    fontSize = 8;
    sealY -= 10;
    const lines = splitAndJustifyText(signedText, font, fontSize, 400);
    console.log(centerX);
    lines.forEach((line, index) => {
        sealY -= fontSize + 3; // Espaciado entre líneas
        // Justificación manual
        let lineWidth = font.widthOfTextAtSize(line, fontSize);
        let currentX = sealLeftX;
        let spaceWidth = 0;
        // let spaceWidth = (400 - lineWidth) / (line.length - 1);
        if (index < lines.length - 1 && line.length > 1) {
            spaceWidth = (400 - lineWidth) / (line.length - 1);
        } else {
            spaceWidth = 0;
            currentX = sealLeftX; 
        }

        for (let char of line) {
            secondPage.drawText(char, {
                x: currentX,
                y: sealY - fontSize / 2,
                font: font,
                size: fontSize,
                color: rgb(0.2, 0.255, 0.333),
                maxWidth: 401,
            });
            currentX += font.widthOfTextAtSize(char, fontSize) + spaceWidth;
        }
    });
    // guardado
    const pdfBytes = await pdfDoc.save();
    // Crear un Blob con los datos del PDF
    const blob = new Blob([pdfBytes], { type: "application/pdf" });
    const url = URL.createObjectURL(blob);
    return url;
}

const getSignedText = async (id) => {
    try {
        const response = await axios.get(route("article.signPdf", id)); // <--- usa 'route()'
        return response.data.signedText;
    } catch (error) {
        console.error("ERROR AXIOS:", error);
        return null;
    }
};

// --- CAMBIO 6: Se añade 'color' como parámetro a drawText ---
const drawText = (text, font, fontSize, page, y, centerX, color = rgb(0.2, 0.255, 0.333)) => {
    const textWidth = font.widthOfTextAtSize(text, fontSize);
    const x = centerX - textWidth / 2;

    page.drawText(text, {
        x: x,
        y: y - fontSize / 2, // Ajustar para centrar verticalmente
        maxWidth: 520,
        font: font,
        color: color, // Usar el parámetro de color
        size: fontSize,
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
    const words = text.split(' ');
    let line = '';
    let result = '';
    for (let n = 0; n < words.length; n++) {
      const testLine = line + words[n] + ' ';
      const testWidth = font.widthOfTextAtSize(testLine, fontSize);
      if (testWidth > width && n > 0) { // <-- Añadido 'n > 0' para evitar saltos en la primera palabra
        result += line.trim() + '\n'; 
        line = words[n] + ' ';
      } else {
        line = testLine;
      }
    }
    result += line.trim(); // <-- .trim() para la última línea
    return result;
  }

const addQR = async (pdfDoc, firstPage, title, user, height, id) => { 
    const qr = await generateQR(id);
    if (!qr) {
        console.error("No se pudo generar el QR");
        return;
    }
    const base64Data = qr.replace(/^data:image\/(png|jpeg);base64,/, '');
    const imageBytes = Uint8Array.from(atob(base64Data), c => c.charCodeAt(0));
    const image = await pdfDoc.embedJpg(imageBytes); 
    const qrFixedSize = 90;
    const qrX = 10;
    const qrY = 580;
    firstPage.drawImage(image, {
        x: qrX,
        y: qrY,
        width: qrFixedSize,
        height: qrFixedSize,
    });

    firstPage.drawRectangle({
        x: qrX,
        y: qrY,
        width: qrFixedSize,
        height: qrFixedSize,
        borderWidth: 1,
        borderColor: rgb(0, 0, 0), // 0, 0, 0 es negro
    });
}

const generateQR = async (id) => { // <-- 1. Se vuelve 'async'
    const verificationUrl = route('certificate.verify', id);
    
    try {
        // 2. Usamos 'await' para esperar el resultado
        const url = await QRCode.toDataURL(
            verificationUrl, // <-- 3. El contenido es la URL
            {
                type: "image/jpeg",
                color: {
                    dark: "#000000",
                    light: "#ffffff",
                },
            }
        );
        return url; // 4. Retornamos la URL generada (data URL)
    
    } catch (error) {
        console.error("Error al generar QR:", error);
        return null; // 5. Manejamos el error
    }
};