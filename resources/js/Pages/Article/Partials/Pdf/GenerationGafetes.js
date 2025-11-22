import { PDFDocument, StandardFonts, rgb } from "pdf-lib";
import QRCode from "qrcode";

// ========================================================================
// CONFIGURACIÓN GENERAL
// ========================================================================

// 1. URL Base para el código QR
const BASE_QR_URL = "programa/CITCA2025/";

// 2. Mapeo de Roles (Traducción de Código PHP a Texto de Gafete)
const ROLE_LABELS = {
    'DUAL_EDITOR_REVISOR': 'EDITOR/REVISOR',
    'Admin': 'COMITÉ ORGANIZADOR',
    'Admin-Revista': 'COMITÉ ORGANIZADOR',
    'Editor': 'COMITÉ ORGANIZADOR',
    'Revisor': 'REVISOR',
    'Staff': 'EQUIPO ORGANIZADOR',
    'Moderador': 'EQUIPO ORGANIZADOR',
    'Conferencista': 'CONFERENCISTA',
    'Tallerista': 'TALLERISTA',
    'Asistente': 'ASISTENTE',
    'Postulante': 'POSTULANTE',
    'DEFAULT': 'PARTICIPANTE' 
};

// ========================================================================
// CONSTANTES DE POSICIONAMIENTO (EL MAPA DEL TESORO)
// ========================================================================

// Puntos de Anclaje (Esquina inferior izquierda de cada celda)
const CELL_ANCHORS = [
    { x: 36, y: 522 }, { x: 219, y: 522 }, { x: 402, y: 522 }, // Fila 1
    { x: 36, y: 279 }, { x: 219, y: 279 }, { x: 402, y: 279 }, // Fila 2
    { x: 36, y: 36 },  { x: 219, y: 36 },  { x: 402, y: 36 }  // Fila 3
];

const GAFETE_USABLE_WIDTH = 174;

// ========================================================================
// OFFSETS RELATIVOS INTERNOS (CONTROL TOTAL DE AJUSTE FINO)
// ========================================================================

// --- AJUSTE VERTICAL DE TEXTOS (Por Fila) ---
// IMPORTANTE: Entre mayor el número, más arriba aparece el texto.
// Orden deseado: Nombre (Arriba), Institución (Medio), Rol (Abajo).

// Fila 1 (Superior)
const NAME_Y_ROW1 = 190;
const INST_Y_ROW1 = 160;
const ROLE_Y_ROW1 = 130; // <--- Nuevo: Altura específica para el Rol en Fila 1

// Fila 2 (Media)
const NAME_Y_ROW2 = 160;
const INST_Y_ROW2 = 130;
const ROLE_Y_ROW2 = 95; // <--- Nuevo: Altura específica para el Rol en Fila 2

// Fila 3 (Inferior)
const NAME_Y_ROW3 = 130;
const INST_Y_ROW3 = 100;
const ROLE_Y_ROW3 = 75; // <--- Nuevo: Altura específica para el Rol en Fila 3


// --- AJUSTE HORIZONTAL DE CENTRADO DE TEXTOS (Por Columna) ---
// Afecta a Nombre, Institución y Rol simultáneamente.
const TEXT_CENTER_NUDGE_COL1 = -18;
const TEXT_CENTER_NUDGE_COL2 = -6;
const TEXT_CENTER_NUDGE_COL3 = 15;


// --- QR HORIZONTAL ESPECÍFICO POR COLUMNA ---
const QR_X_OFFSET_COL1 = 100;
const QR_X_OFFSET_COL2 = 120;
const QR_X_OFFSET_COL3 = 135;

// --- QR VERTICAL ESPECÍFICO POR FILA ---
const QR_Y_OFFSET_ROW1 = 60;
const QR_Y_OFFSET_ROW2 = 30;
const QR_Y_OFFSET_ROW3 = -1;

const QR_SIZE = 50;


// ========================================================================
// FUNCIÓN PRINCIPAL
// ========================================================================
export async function generateGafetesPdf(usersList) {
    try {
        if (!usersList || usersList.length === 0) {
            alert("No hay usuarios para generar gafetes.");
            return null;
        }

        const pdfDoc = await PDFDocument.create();

        // Cargar la plantilla de fondo 3x3
        const urlPdf = "/pdfs/TemplateGafete.pdf";
        const templateArrayBuffer = await fetch(urlPdf).then(res => res.arrayBuffer());
        const templatePdf = await PDFDocument.load(templateArrayBuffer);

        // Fuentes
        const fontBold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
        const fontRegular = await pdfDoc.embedFont(StandardFonts.Helvetica);
        
        const colorBlack = rgb(0, 0, 0);
        
        const itemsPerPage = 9;
        let currentPage = null;

        // ==============================
        // BUCLE PRINCIPAL
        // ==============================
        for (let i = 0; i < usersList.length; i++) {

            // 1. Control de Páginas
            if (i % itemsPerPage === 0) {
                const [templatePage] = await pdfDoc.copyPages(templatePdf, [0]);
                currentPage = pdfDoc.addPage(templatePage);
            }

            const user = usersList[i];
            const positionIndex = i % itemsPerPage;

            // --- DETERMINAR FILA Y COLUMNA ACTUAL ---
            const rowIndex = Math.floor(positionIndex / 3); // 0, 1 o 2
            const colIndex = positionIndex % 3;           // 0, 1 o 2

            // 2. OBTENER EL ANCLAJE CORRECTO
            const anchor = CELL_ANCHORS[positionIndex];
            const centerXBase = anchor.x + (GAFETE_USABLE_WIDTH / 2);

            // ==============================
            // SELECCIÓN DE VARIABLES DE AJUSTE (Fila/Columna)
            // ==============================
            
            // Agregamos currentRoleY a las variables
            let currentNameY, currentInstY, currentRoleY, currentQrY, currentQrX, textNudgeX;

            // A) Selección por FILA (Alturas Y)
            if (rowIndex === 0) {
                currentNameY = NAME_Y_ROW1;
                currentInstY = INST_Y_ROW1;
                currentRoleY = ROLE_Y_ROW1; // <--- Selección Altura Rol Fila 1
                currentQrY = QR_Y_OFFSET_ROW1;
            } else if (rowIndex === 1) {
                currentNameY = NAME_Y_ROW2;
                currentInstY = INST_Y_ROW2;
                currentRoleY = ROLE_Y_ROW2; // <--- Selección Altura Rol Fila 2
                currentQrY = QR_Y_OFFSET_ROW2;
            } else {
                currentNameY = NAME_Y_ROW3;
                currentInstY = INST_Y_ROW3;
                currentRoleY = ROLE_Y_ROW3; // <--- Selección Altura Rol Fila 3
                currentQrY = QR_Y_OFFSET_ROW3;
            }

            // B) Selección por COLUMNA (Posiciones X y Nudges)
            if (colIndex === 0) {
                currentQrX = QR_X_OFFSET_COL1;
                textNudgeX = TEXT_CENTER_NUDGE_COL1;
            } else if (colIndex === 1) {
                currentQrX = QR_X_OFFSET_COL2;
                textNudgeX = TEXT_CENTER_NUDGE_COL2;
            } else {
                currentQrX = QR_X_OFFSET_COL3;
                textNudgeX = TEXT_CENTER_NUDGE_COL3;
            }

            // El centro horizontal ajustado es el mismo para los 3 textos
            const finalTextCenterX = centerXBase + textNudgeX;

            // ==============================
            // PREPARACIÓN DE DATOS DEL USUARIO
            // ==============================
            
            const userName = balanceName(user.name || "");
            const institution = user.institution || ""; 
            // Obtener texto del ROL
            const roleLabelText = ROLE_LABELS[user.role] || ROLE_LABELS['DEFAULT'];
            // URL completa para el QR
            const qrUrlData = `${BASE_QR_URL}${user.user_id}`;


            // ==============================
            // DIBUJAR ELEMENTOS
            // ==============================

            // --- DIBUJAR NOMBRE ---
            drawCenteredTextMultiLine(
                currentPage,
                userName,
                fontBold,
                10,
                finalTextCenterX,
                anchor.y + currentNameY,
                colorBlack,
                GAFETE_USABLE_WIDTH - 10,
                1.4
            );

            // --- DIBUJAR INSTITUCIÓN ---
            drawCenteredTextMultiLine(
                currentPage,
                institution,
                fontRegular,
                8,
                finalTextCenterX,
                anchor.y + currentInstY,
                colorBlack,
                GAFETE_USABLE_WIDTH - 10,
                1.2
            );

            // --- NEW DIBUJAR ETIQUETA DEL ROL (Debajo de la Institución) ---
            drawCenteredTextMultiLine(
                currentPage,
                roleLabelText,
                fontBold, // Usamos fuente regular
                9,           // Tamaño intermedio (ej. 9)
                finalTextCenterX, // Mismo centro ajustado
                anchor.y + currentRoleY, // <--- Altura específica por fila
                colorBlack,
                GAFETE_USABLE_WIDTH - 10,
                1.2
            );

            // --- DIBUJAR QR ---
            await drawQrCode(
                pdfDoc,
                currentPage,
                qrUrlData,
                anchor.x + currentQrX,
                anchor.y + currentQrY,
                QR_SIZE
            );
        }

        const pdfBytes = await pdfDoc.save();
        const blob = new Blob([pdfBytes], { type: "application/pdf" });
        return URL.createObjectURL(blob);

    } catch (error) {
        console.error("Error:", error);
        alert("Error generando gafetes: " + error.message);
        return null;
    }
}


// ========================================================================
// HELPERS (Sin cambios)
// ========================================================================
// (Asegúrate de incluir aquí abajo las funciones balanceName, 
//  drawCenteredTextMultiLine, wrapText y drawQrCode que ya tenías)

function balanceName(name) {
    if (!name) return "";
    const clean = name.toString().trim();
    if (clean.length < 20) return clean;
    const words = clean.split(" ");
    if (words.length <= 2) return clean;
    const middle = Math.floor(clean.length / 2);
    let length = 0;
    let splitIndex = 0;
    let bestDiff = Infinity;
    for (let i = 0; i < words.length; i++) {
        length += words[i].length + 1;
        const diff = Math.abs(length - middle);
        if (diff < bestDiff) {
            bestDiff = diff;
            splitIndex = i;
        }
    }
    return (
        words.slice(0, splitIndex + 1).join(" ") +
        "\n" +
        words.slice(splitIndex + 1).join(" ")
    );
}

function drawCenteredTextMultiLine(page, text, font, fontSize, x, startY, color, maxWidth, lineHeightFactor = 1.2) {
    if (!text) return;
    const lines = wrapText(text, maxWidth, font, fontSize);
    const lineHeight = fontSize * lineHeightFactor;
    let y = startY; 

    for (const line of lines) {
        const w = font.widthOfTextAtSize(line, fontSize);
        page.drawText(line, {
            x: x - w / 2,
            y,
            size: fontSize,
            font,
            color
        });
        y -= lineHeight;
    }
}

function wrapText(text, maxWidth, font, fontSize) {
    const paragraphs = text.split("\n");
    let finalLines = [];
    for (let paragraph of paragraphs) {
        const words = paragraph.split(" ");
        let line = words[0];
        for (let i = 1; i < words.length; i++) {
            const testLine = line + " " + words[i];
            if (font.widthOfTextAtSize(testLine, fontSize) < maxWidth) {
                line = testLine;
            } else {
                finalLines.push(line);
                line = words[i];
            }
        }
        finalLines.push(line);
    }
    return finalLines;
}

async function drawQrCode(pdfDoc, page, data, x, y, size) {
    try {
        const qrDataUrl = await QRCode.toDataURL(data, {
            margin: 0,
            scale: 4,
            color: { dark: "#000000", light: "#0000" }
        });

        const bytes = await fetch(qrDataUrl).then(r => r.arrayBuffer());
        const img = await pdfDoc.embedPng(bytes);

        page.drawImage(img, {
            x,
            y,
            width: size,
            height: size
        });
    } catch (err) {
        console.error("Error QR:", err);
    }
}