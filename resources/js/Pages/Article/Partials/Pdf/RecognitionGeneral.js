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
        
        const authorsArray = response.data.authors;

        if (!authorsArray || authorsArray.length === 0) {
            alert("No se encontraron autores para este artículo.");
            return null;
        }
        
        console.log("Autores encontrados:", authorsArray);

        const pdfUrl = await getPdf(authorsArray, articleTitle, articleId);

        return pdfUrl;

    } catch (error) {
        console.error("Error al generar el certificado grupal:", error);
        alert("Hubo un error al obtener los autores del artículo.");
        return null;
    }
}

export default async function getPdf(authors, title, id) { 
    const signedText = await getSignedText(id);
    let legend = "Por su invaluable presentación del Artículo:";
    
    let text = ""; // 'text' será la cadena final de autores
    if (Array.isArray(authors)) {
        // Es un arreglo (GRUPAL), formato "A, B y C"
        if (authors.length > 1) {
            const lastAuthor = authors[authors.length - 1];
            const otherAuthors = authors.slice(0, -1); 
            text = otherAuthors.join(', ') + ' y ' + lastAuthor;
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

    const fontBold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
    let font = fontBold;
    let fontSize = 15;

    const maxAuthorWidth = 500; // Ancho máximo para el nombre/s
    const wrappedText = wrapText(text, maxAuthorWidth, font, fontSize);
    const textLines = wrappedText.split('\n');
    const lineHeight = fontSize * 1.5; // Espaciado de 1.2

   
    const totalTextBlockHeight = (textLines.length - 1) * lineHeight;
    let currentY = (height / 2 + 30) + (totalTextBlockHeight / 2);

    for (const line of textLines) {
        let textWidth = font.widthOfTextAtSize(line, fontSize);
        let x = (centerX - textWidth / 2 ) - 20; // Centrar cada línea
        
        firstPage.drawText(line, {
            x: x,
            y: currentY - fontSize / 2, 
            font: font,
            size: fontSize,
        });
        currentY -= lineHeight; // Mover 'y' hacia abajo para la siguiente línea
    }

    fontSize = 12;
    font = await pdfDoc.embedFont(StandardFonts.Helvetica);
    let textWidth = font.widthOfTextAtSize(legend, fontSize);
    
    // Ajustar la 'y' de la leyenda para que esté debajo del bloque de autores
    const authorBlockHeight = textLines.length * lineHeight;
    y = (height / 2 + 30) - (authorBlockHeight / 2) - 40; // 40px de espacio

    let x = (centerX - textWidth / 2) - 20; // Centrar la leyenda

    firstPage.drawText(legend, {
        x: x,
        y: y - fontSize / 2, 
        maxWidth: 500,
        font: font,
        color: rgb(0.2, 0.255, 0.333),
        size: fontSize,
    });

    await addQR(pdfDoc, secondPage, title, text, height); // <--- CAMBIO: Pasa 'text' (la cadena de autores) al QR

    drawText(title, fontBold, 12, firstPage, (y -= 32), (centerX - 20));
    drawText("En el II Congreso Internacional de Tecnología y Ciencia Aplicada, desarrollado en el Tecnológico", font, 10, firstPage, (y -= 32), (centerX - 20));
    drawText("Nacional de México/Centro Nacional de Investigación y Desarrollo Tecnológico, TecNM/CENIDET,", font, 10, firstPage, (y -= 16), (centerX - 20));
    drawText("del 22 al 24 de mayo de 2024.", font, 10, firstPage, (y -= 16), (centerX - 20));
    drawText("Cuernavaca, Morelos, 24 de mayo de 2024", font, 10, firstPage, (y -= 25), centerX );
    // SERV
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

const drawText = (text, font, fontSize, page, y, centerX) => {
    const textWidth = font.widthOfTextAtSize(text, fontSize);
    const x = centerX - textWidth / 2;

    page.drawText(text, {
        x: x,
        y: y - fontSize / 2, // Ajustar para centrar verticalmente
        maxWidth: 520,
        font: font,
        color: rgb(0.2, 0.255, 0.333),
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

const addQR = async (pdfDoc, firstPage, title, user, height) => { 
    const qr = generateQR(title, user); 
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

const generateQR = (title, user) => { 
    let qr;
    QRCode.toDataURL(
        `Titulo: ${title}\nPostulante: ${user}`, // 'user' es la cadena de autores
        {
            type: "image/jpeg",
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