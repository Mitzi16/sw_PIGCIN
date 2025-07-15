import { PDFDocument, StandardFonts, rgb } from "pdf-lib"; // Asegúrate de importar lo necesario
import axios from "axios";
import QRCode from "qrcode";

export default async function getPdf(user, title, id) {
    const signedText = await getSignedText(id);
    let legend = "Por su invaluable presentación del Artículo:";
    let text = user;

    const urlPdf = "/pdfs/Template.pdf";
    const existingPdfBytes = await fetch(urlPdf).then((res) =>
        res.arrayBuffer()
    );
    const pdfDoc = await PDFDocument.load(existingPdfBytes);
    const pages = pdfDoc.getPages();
    const firstPage = pages[0];
    const { width, height } = firstPage.getSize();

    const centerX = width / 2;
    let y = height / 2 + 30;

    let font = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
    let fontSize = 20;
    let textWidth = font.widthOfTextAtSize(text, fontSize);

    let x = centerX - textWidth / 2;
    firstPage.drawText(text, {
        x: x,
        y: y - fontSize / 2, // Ajustar para centrar verticalmente
        maxWidth: 500,
        font: font,
        size: fontSize,
    });

    fontSize = 12;
    font = await pdfDoc.embedFont(StandardFonts.Helvetica);
    textWidth = font.widthOfTextAtSize(legend, fontSize);
    y -= 70;
    x = centerX - textWidth / 2;

    firstPage.drawText(legend, {
        x: x,
        y: y - fontSize / 2, // Ajustar para centrar verticalmente
        maxWidth: 500,
        font: font,
        color: rgb(0.2, 0.255, 0.333),
        size: fontSize,
    });

    await addQR(pdfDoc, firstPage, title, user);

    drawText(title, font, 12, firstPage, (y -= 16), centerX);
    drawText("En el II Congreso Internacional de Tecnología y Ciencia Aplicada, desarrollado en el Tecnológico", font, 12, firstPage, (y -= 16), centerX);
    drawText("Nacional de México/Centro Nacional de Investigación y Desarrollo Tecnológico, TecNM/CENIDET,", font, 12, firstPage, (y -= 16), centerX);
    drawText("del 22 al 24 de mayo de 2024.", font, 12, firstPage, (y -= 16), centerX);
    drawText("Cuernavaca, Morelos, 24 de mayo de 2024", font, 12, firstPage, (y -= 50), centerX);
    // SERV
    drawText(`Sello Digital:`, font, 10, firstPage, (y -= 100), centerX);
    fontSize = 6;
    y -= 6;
    const lines = splitAndJustifyText(signedText, font, fontSize, 400);
    console.log(centerX);
    lines.forEach((line, index) => {
        y -= fontSize + 3; // Espaciado entre líneas
        // Justificación manual
        let lineWidth = font.widthOfTextAtSize(line, fontSize);
        let currentX = centerX - lineWidth / 2;
        let spaceWidth = 0;
        // let spaceWidth = (400 - lineWidth) / (line.length - 1);
        if (index < lines.length - 1 && line.length > 1) {
            spaceWidth = (400 - lineWidth) / (line.length - 1);
        } else {
            spaceWidth = 0;
            currentX = 106;
        }

        for (let char of line) {
            firstPage.drawText(char, {
                x: currentX,
                y: y - fontSize / 2,
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
        const response = await axios.get(route("article.signPdf", id));
        return response.data.signedText;
    } catch (error) {
        console.error("ERROR AXIOS:", error);
        return null;
    }
};

const drawText = (text, font, fontSize, page, y, centerX) => {
    const textWidth = font.widthOfTextAtSize(text, fontSize);
    const x = Math.max(centerX - textWidth / 2, 52);

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
    const words = text.split(""); // Dividir por caracteres
    let lines = [];
    let currentLine = words[0];

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
      if (testWidth > width) {
        result += line + '\n';
        line = words[n] + ' ';
      } else {
        line = testLine;
      }
    }
    result += line;
    return result;
  }

const addQR = async (pdfDoc, firstPage, title, user) => {
    const qr = generateQR(title, user);
    const base64Data = qr.replace(/^data:image\/(png|jpeg);base64,/, '');
    const imageBytes = Uint8Array.from(atob(base64Data), c => c.charCodeAt(0));
    const image = await pdfDoc.embedJpg(imageBytes);
    const { width, height } = image.scale(0.5); // Escalar al 50%
    firstPage.drawImage(image, {
        x: 25,
        y: 70,
        width,
        height,
    });
}

const generateQR = (title, user) => {
    let qr;
    QRCode.toDataURL(
        `Titulo: ${title}\nPostulante: ${user}`,
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