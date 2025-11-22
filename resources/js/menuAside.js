import {
    mdiAccountCircle,
    mdiMonitor,
    mdiBullhorn,
    mdiDomain,
    mdiShieldCrown,
    mdiViewList,
    mdiViewModule,
    mdiLockCheckOutline,
    mdiAccountSupervisor,
    mdiAccount,
    mdiBook,
    mdiShieldLock,
    mdiFileDocumentCheck,
    mdiReceiptText,
} from "@mdi/js";

/**
 * Helper reutilizable.
 * Verifica si un array de roles de usuario contiene CUALQUIERA de los roles objetivo.
 * @param {string[]} userRoles - Roles del usuario. E.g., ['Revisor']
 * @param {string[]} targetRoles - Roles que buscamos. E.g., ['Admin', 'Editor']
 * @returns {boolean}
 */
const userHasAnyRole = (userRoles, targetRoles) => {
    // .some() es muy eficiente, se detiene en el primer 'true' que encuentra
    return userRoles.some(role => targetRoles.includes(role));
};

/**
 * @param {string[]} userRoles -  array con los nombres de los roles del usuario. E.g., ['revisor', 'editor']
 * @returns {Array} 
 */
export const getMenu = (userRoles = []) => {
    const isRevisor = userRoles.includes("Revisor");
    const isEditor = userRoles.includes("Editor");

    let articleMenuItem;

    if (isRevisor && isEditor) {
        // Caso 1: Tiene AMBOS roles. Muestra el submenú.
        articleMenuItem = {
            label: "Artículos",
            icon: mdiBook,
            permission: "article.index", // Permiso padre para ver el menú
            menu: [
                {
                    route: "article.diffuse",
                    label: "Artículos para Distribuir",
                    icon: mdiBullhorn,
                    permission: "article.index",
                },
                {
                    route: "article.qualify",
                    label: "Artículos para Evaluar",
                    icon: mdiFileDocumentCheck,
                    permission: "article.index",
                },
            ],
        };
    } else if (isRevisor) {
        // Caso 2: Es SÓLO Revisor
        articleMenuItem = {
            route: "article.qualify",
            label: "Artículos para Evaluar",
            icon: mdiFileDocumentCheck,
            permission: "article.index",
        };
    } else if (isEditor) {
        // Caso 3: Es SÓLO Editor
        articleMenuItem = {
            route: "article.diffuse",
            label: "Artículos para Distribuir",
            icon: mdiBullhorn,
            permission: "article.index",
        };
    } else {
        // Caso 4: Rol genérico
        articleMenuItem = {
            route: "article.index",
            label: "Artículos",
            icon: mdiBook,
            permission: "article.index",
        };
    }

    const menu = [
        {
            route: "dashboard",
            icon: mdiMonitor,
            label: "Inicio",
            to: "/dashboard",
        },
        {
            route: "profile.edit",
            label: "Perfil",
            icon: mdiAccountCircle,
        },
        {
            label: "Seguridad",
            icon: mdiShieldLock,
            role: "Admin",
            permission: "menu.seguridad",
            menu: [
                {
                    label: "Modulos",
                    route: "module.index",
                    icon: mdiViewModule,
                    permission: "module.index",
                },
                {
                    label: "Permisos",
                    route: "permission.index",
                    icon: mdiLockCheckOutline,
                    permission: "permission.index",
                },
                {
                    label: "Roles",
                    route: "role.index",
                    icon: mdiAccountSupervisor,
                    permission: "role.index",
                },
                {
                    label: "Usuarios",
                    route: "user.index",
                    icon: mdiAccount,
                    permission: "user.index",
                },
                {
                    label: "Reconocimientos",
                    route: "reconocimiento.index",
                    icon: mdiShieldCrown,
                    permission: "reconocimiento.index",
                },
            ],
        },
        {
            isDivider: true,
        },
        {
            label: "Catálogos",
            icon: mdiViewList,
            permission: "menu.catalogo",
            menu: [
                {
                    route: "call.index",
                    label: "Convocatorias",
                    icon: mdiBullhorn,
                    permission: "call.index",
                },
                {
                    label: "Tipos de Artículos",
                    route: "articleType.index",
                    icon: mdiViewModule,
                    permission: "articleType.index",
                },
                {
                    label: "Programas de Estudio",
                    route: "knowledgeArea.index",
                    icon: mdiViewModule,
                    permission: "knowledgeArea.index",
                },
                {
                    label: "Áreas Prioritarias",
                    route: "knowledgeSubArea.index",
                    icon: mdiViewModule,
                    // permission: "knowledgeArea.index",
                },
                {
                    label: "Instituciones",
                    route: "institution.index",
                    icon: mdiDomain,
                    permission: "institution.index",
                },
                {
                    route: "criterion.index",
                    label: "Rúbricas de Evaluación",
                    icon: mdiFileDocumentCheck,
                    permission: "criterion.index",
                },
            ],
        },
        articleMenuItem, 
        {
            route: "paymentVoucher.index",
            label: "Pagos",
            icon: mdiReceiptText,
            permission: "paymentVoucher.index",
        },
        {
            label: "Gráficas",
            icon: mdiViewList,
            permission: "menu.graficas",
            menu: [
                {
                    route: "article.graphs.institution",
                    label: "Artículos por institución",
                    icon: mdiBullhorn,
                },
                {
                    label: "Artículos por tipo de artículo",
                    route: "article.graphs.articleType",
                    icon: mdiViewModule,
                },
                {
                    label: "Artículos por estatus",
                    route: "article.graphs.status",
                    icon: mdiViewModule,
                },
                {
                    label: "Artículos por fecha de postulación",
                    route: "article.graphs.date",
                    icon: mdiViewModule,
                    // permission: "knowledgeArea.index",
                },
                {
                    label: "Artículos por usuario",
                    route: "article.graphs.user",
                    icon: mdiDomain,
                },
                {
                    label: "Artículos por programa de estudio",
                    route: "article.graphs.program",
                    icon: mdiDomain,
                },
                {
                    label: "Artículos por área prioritaria",
                    route: "article.graphs.area",
                    icon: mdiDomain,
                },
            ],
        },
        {
            label: "Generar Gafetes",
            route: "generate.gafetes",
            icon: mdiBook,
            role: "Admin"
        },
    ];

    if (userHasAnyRole(userRoles, ["Postulante", "Admin", "Admin-Revista"])){
         menu.push({
            label: "Diapositivas",
            route: "diapositivas.index",
            icon: mdiBook,
        });
    }

    // "Constancias"
    if (userHasAnyRole(userRoles, ["Postulante", "Admin"])) {
        menu.push({
            label: "Constancias",
            route: "constancia.name",
            icon: mdiShieldCrown,
        });
    }

    //  Reconocimientos"
    const rolesForRecognition = [
        "Admin",
        "Editor",
        "Revisor",
        "Tallerista",
        "Conferencista",
        "Admin-Revista",
        "Asistente",
        "Moderador",
        "Staff",
    ];

    if (userHasAnyRole(userRoles, rolesForRecognition)) {
        menu.push({
            label: "Generar Reconocimiento de Participación",
            route: "constancia.generate",
            icon: mdiShieldCrown,
        });
    }

    return menu;
};