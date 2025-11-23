export const eventData = {
    // DATOS DEL PROGRAMA POR DÍA
    programa: [
        {
            id: 1,
            dia: "Día 1",
            fecha: "Miercoles 26 de Noviembre 2025",
            titulo: "Inauguración, Conferencias Magistrales, Talleres, Ponencias y recesos",
            descripcion: "Consulta el cronograma completo, conferencias Magistrales, Talleres y recesos.",
            pdf: "/pdfs/info/Programa previo 5to CITCA 26novP.pdf", 
            color: "bg-blue-600" // Un color distintivo para la tarjeta
        },
        {
            id: 2,
            dia: "Día 2",
            fecha: "Jueves 27 de Noviembre 2025",
            titulo: "Talleres, Ponencias, Conferencias Magistrales y Nominaciones",
            descripcion: "Consulta el cronograma completo, conferencias Magistrales, Talleres y recesos.",
            pdf: "/pdfs/info/Programa previo 5to CITCA 27nov.pdf",
            color: "bg-emerald-600"
        },
        {
            id: 3,
            dia: "Día 3",
            fecha: "Viernes 28 de Noviembre 2025",
            titulo: "Talleres, Conferencias Magistrales, Ponencias y Clausura",
            descripcion: "Consulta el cronograma completo, Conferencias Magistrales, Talleres y recesos.",
            pdf: "/pdfs/info/Programa previo 5to CITCA 28nov.pdf",
            color: "bg-indigo-600"
        }
    ],

    // DATOS DE CONFERENCIAS (Speakers)
    conferencias: [
        // --- PÁGINA 1 ---
        {
            id: 1,
            speaker: "Dr. Jesús Linares Flores",
            titulo: "Diseño, implementación y control de dos vehículos eléctricos con tecnología motor en rueda BLDC",
            imagen: "/img/conferencista/c1.png", 
            hora: " Miércoles 26 de noviembre a las 9:00 hrs",
            resumen: "Se presenta el diseño, construcción e implementación de controladores de movimiento con rechazo activo de perturbaciones para dos vehículos eléctricos con motores en rueda BLDC. El primero es un vehículo de dos ruedas con control de autobalanceo basado en el modelo carro–péndulo invertido, programado en una tarjeta C2000 DSP de Texas Instruments. El segundo es un vehículo tipo Go-Kart con tracción en las cuatro ruedas, controlado mediante un enfoque multi-agente inteligente fundamentado en la geometría de Ackermann. La dirección electrónica del sistema se implementa en una tarjeta de desarrollo FPGA.",
            semblanza: "El Dr. Jesús Linares Flores es originario de Atlixco, Puebla. Es Licenciado en Electrónica por la BUAP y Maestro en Ciencias por la Universidad de las Américas-Puebla. Obtuvo el Doctorado en Ciencias con especialidad en Ingeniería Eléctrica en el CINVESTAV-IPN y realizó una estancia doctoral en la Universidad Bundeswehr de Múnich, financiada por el DAAD. Desde 2006 es Profesor Investigador Titular C en el Instituto de Electrónica y Mecatrónica de la UTM, del cual fue director entre 2010 y 2016. Es Investigador Nacional Nivel II y Senior Member IEEE. Su trabajo se centra en control de motores y electromovilidad, con amplia producción científica, una patente y dirección de tesis de licenciatura, maestría y doctorado."
        },
        {
            id: 2,
            speaker: "Ing. Jair Axel Lizarraga Velarde",
            titulo: " El uso de IA en la investigación y la academia para profesores y estudiantes: IBM SkillsBuild",
            imagen: "/img/conferencista/c2.png",
            hora: "Miércoles 26 de noviembre a las 12:00 hrs",
            resumen: "Se busca orientar a la comunidad académica en el uso responsable y efectivo de la Inteligencia Artificial para fortalecer la investigación, la enseñanza y el aprendizaje. A través de la plataforma IBM SkillsBuild, los participantes conocerán herramientas y aplicaciones prácticas de IA que facilitan el análisis de información, la generación de contenido académico y el desarrollo de proyectos innovadores. Además, se promoverá una visión crítica y ética sobre la adopción de estas tecnologías en el ámbito educativo y científico.",
            semblanza: "El Ing. Jair Lizárraga es un profesional comprometido con la educación, la tecnología y la innovación, con amplia experiencia en el impulso de iniciativas académicas en América Latina. Como IBM University Program Manager, coordina proyectos educativos en la región y lidera el programa de Embajadores Académicos de IBM, fortaleciendo la conexión entre estudiantes, docentes y tecnologías emergentes como inteligencia artificial, ciencia de datos, computación en la nube e IoT. Ha diseñado proyectos que abordan problemáticas reales, desde asistentes virtuales hasta sistemas inteligentes para agricultura e inclusión educativa. Combina su formación técnica con una visión humana que promueve aprendizaje práctico e impacto social."
        },
        {
            id: 3,
            speaker: "Dr. Omar Lengerke Pérez. ",
            rol: "Rector de las Unidades Tecnológicas de Santander",
            titulo: "De la investigación institucional a la inteligencia artificial: el camino de unasituación de educación superior pública (UTS) hacia la Educación 5.0.",
            imagen: "/img/conferencista/c3.png",
            hora: "Miércoles 26 de noviembre a las 13:00 hrs",
            resumen: "Se presenta la evolución de las Unidades Tecnológicas de Santander (UTS) en el fortalecimiento de su investigación y su aporte al desarrollo regional. Con base en esta experiencia institucional, se analizan los retos que la Industria 4.0 y la inteligencia artificial imponen a la educación superior, resaltando la necesidad de nuevas competencias, modelos pedagógicos innovadores y una visión orientada a la transformación tecnológica. Finalmente, se expone cómo una institución puede prepararse mediante el impulso a la investigación, la adopción de tecnologías emergentes y la formación de talento capaz de responder a las demandas del futuro.",
            semblanza: "El Dr. Omar Lengerke Pérez es un destacado referente en ingeniería y educación superior en Colombia. Originario de Socorro (Santander), es Ingeniero en Sistemas Computacionales, con Maestría en Control y Automatización, Doctorado en Ciencias de la Ingeniería Mecánica y un posdoctorado en Diseño de Máquinas Mecatrónicas y Robótica. Es Investigador Senior reconocido por el Ministerio de Ciencia y ha producido más de 100 publicaciones científicas, además de editar la Revista Colombiana de Computación y varios libros. Sus líneas de investigación incluyen mecatrónica, manufactura flexible, robótica, vehículos inteligentes y equipo biomédico. Desde 2015 es Rector de las UTS, donde ha impulsado infraestructura, calidad académica e investigación, recibiendo múltiples distinciones regionales."
        },
        {
            id: 4,
            speaker: "Profesor Dong Li",
            rol: "Decano de Ingeniería Civil - NEPU",
            titulo: "Aprovechamiento fototérmico solar en acristalamiento aislante integrado con PCM.",
            imagen: "/img/conferencista/c4.png",
            hora: "Jueves 27 de noviembre a las 9:00 hrs",
            resumen: "Se aborda el aprovechamiento fototérmico solar en acristalamientos aislantes integrados con materiales de cambio de fase (PCM), destacando los fundamentos físicos y las ventajas energéticas de esta tecnología para climas fríos y templados. Se presentan modelos teóricos y simulaciones numéricas que demuestran su capacidad para mejorar la eficiencia térmica, reducir cargas de calefacción y estabilizar la temperatura interior. Asimismo, se proponen métricas de diseño que consideran la orientación del edificio, las condiciones climáticas y la integración de nuevos materiales, ofreciendo lineamientos prácticos para soluciones de baja emisión de carbono en aplicaciones arquitectónicas y energéticas.",
            semblanza: "El profesor Dong Li es decano de la Facultad de Ingeniería Civil y Arquitectura de la Universidad del Petróleo del Noreste (NEPU), donde labora desde 2006. Doctor en Termofísica de la Ingeniería, su investigación se centra en transferencia de calor, calefacción limpia, sistemas energéticos multisource y soluciones bajas en carbono para regiones de frío severo. Dirige laboratorios y equipos provinciales en nuevas energías y prevención de desastres. Ha publicado más de 70 artículos SCI, posee más de 30 patentes y participa en consejos editoriales y comités nacionales. Ha recibido múltiples premios, incluido “Longjiang Scholar” y el reconocimiento al 2 % de científicos de mayor impacto."
        },
        // --- PÁGINA 2 ---
        {
            id: 5,
            speaker: "Dr. Francisco Beltrán Carbajal",
            rol: "Investigador Titular C - UAM Azcapotzalco",
            titulo: " Supresión de oscilaciones o vibraciones en sistemas mecánicos diferencialmente planos.",
            imagen: "/img/conferencista/c5.png",
            hora: " Jueves 27 de noviembre a las 12:00 hrs",
            resumen: "Se presenta la formulación del problema de supresión de oscilaciones en sistemas dinámicos diferencialmente planos desde un enfoque mecatrónico. Se revisan métodos de control de vibraciones aplicables a sistemas con planitud diferencial bajo condiciones perturbadas, así como técnicas para estimación de parámetros y oscilaciones. Se propone una estrategia para la estimación en línea de perturbaciones dinámicas completamente desconocidas en sistemas oscilatorios. Se discuten diversos casos de estudio en sistemas mecánicos y mecatrónicos diferencialmente planos. Finalmente, se plantean problemas de investigación abiertos relacionados con la supresión de vibraciones y la identificación de sistemas de conversión de energía que presentan oscilaciones indeseables.",
            semblanza: "El Dr. Francisco Beltrán Carbajal es Ingeniero Electromecánico por el Instituto Tecnológico de Zacatepec y Doctor en Ciencias en Ingeniería Eléctrica con especialidad en Mecatrónica por el CINVESTAV-IPN. Realizó una estancia en Philips Center for Industrial Technology en Holanda y se graduó de la Siemens Automation & Drives Summer School en Alemania. Ha trabajado en la industria automotriz y se ha desempeñado como profesor e investigador en diversas instituciones, destacando su trayectoria en el Tecnológico de Monterrey. Es autor de más de 200 publicaciones y varios libros en control y vibraciones. Actualmente es Profesor-Investigador Titular C en la UAM Azcapotzalco, Investigador Nacional Nivel II y líder de proyectos internacionales en energía y sistemas mecatrónicos."
        },
        {
            id: 6,
            speaker: "Dr. David Arnoldo Valtiérrez Angel",
            rol: "Catedrático - Tecnológico de Delicias",
            titulo: "Aplicaciones de la inteligencia artificial en el paradigma de la educación ¿evolución o retroceso?",
            imagen: "/img/conferencista/c6.png",
            hora: "Jueves 27 de noviembre a las 8:00 hrs. ",
            resumen: "Se analiza el impacto de la Inteligencia Artificial en la educación, destacando su capacidad para automatizar tareas, personalizar el aprendizaje y potenciar la investigación. También aborda riesgos como sesgos, falta de transparencia, dependencia tecnológica y problemas éticos. Se reflexiona sobre el uso de ChatGPT y su influencia en las habilidades de comunicación, el plagio y la labor docente, enfatizando la necesidad de evaluar con mayor rigurosidad. Finalmente, se subraya que la IA representa una evolución cuando se usa ética y responsablemente, sin sustituir el pensamiento humano, sino fortaleciendo las capacidades cognitivas y críticas.",
            semblanza: "El Dr. David Arnoldo Valtiérrez Angel es Doctor en Ciencias con acentuación en Tecnología Educativa por la Universidad Autónoma España de Durango. Cuenta con estudios de Maestría en la Universidad Autónoma de Chihuahua y formación profesional en el Instituto Tecnológico de Delicias, donde actualmente es catedrático en Ciencias Computacionales y Metodología Científica. Ha sido desarrollador, instructor certificado y asesor en concursos nacionales de innovación del Tecnológico Nacional de México. Participa como revisor y ponente en congresos nacionales e internacionales, incluyendo diversas ediciones de Academia Journals. Obtuvo Tercer Lugar Nacional en el Concurso de Prototipos 2017, representando a México en MOSTRATEC, Brasil. Ha realizado estancias académicas en Canadá y visitas formativas en diversos países."
        },
        {
            id: 7,
            speaker: "Dra. Tatiana Romero Castañón",
            rol: "Investigadora - INEEL",
            titulo: " Celdas de combustible PEM y AEM: De la ciencia a la aplicación.",
            imagen: "/img/conferencista/c7.png",
            hora: "Viernes 28 de noviembre a las 9:00 hrs",
            resumen: "Se abordan los principios fundamentales, avances recientes y aplicaciones emergentes de las celdas de combustible PEM y AEM, dos tecnologías clave en la transición hacia sistemas energéticos limpios y de alta eficiencia. Se presentarán los retos científicos asociados a materiales, durabilidad y diseño, así como desarrollos tecnológicos que permiten su integración en movilidad, generación distribuida y almacenamiento energético. A partir de la experiencia del INEEL, se mostrarán casos de aplicación y líneas de innovación que impulsan la adopción de estas tecnologías en México.",
            semblanza: "La Dra. Tatiana Romero Castañón cuenta con más de 27 años de experiencia en investigación y desarrollo de tecnologías de hidrógeno. Es doctora en Ingeniería por la Universidad de British Columbia, Canadá, especializada en celdas de combustible; posee una Maestría en Energías Renovables por el Instituto de Energías Renovables de la UNAM y es Ingeniera Química por la UPAEP. Desde el año 2000 forma parte del Instituto Nacional de Electricidad y Energías Limpias (INEEL), donde cofundó el Laboratorio de Hidrógeno y Celdas de Combustible. Bajo su liderazgo, este evolucionó en 2019 hacia el Laboratorio de Tecnologías de Hidrógeno y Almacenamiento de Energía, dedicado al desarrollo de celdas de combustible, electrolizadores y baterías de flujo, con enfoque en innovación nacional."
        },
        {
            id: 8,
            speaker: "Dr. Roberto Antonio Vázquez",
            rol: "Coordinador de Investigación - Universidad La Salle",
            titulo: " Modelos neuronales de tercera generación y su aplicación en el reconocimiento de patrones",
            imagen: "/img/conferencista/c8.png",
            hora: "Viernes 28 de noviembre a las 12:00 hrs",
            resumen: "Los modelos neuronales de tercera generación, estudiados por casi un siglo, permiten representar con gran precisión el comportamiento de neuronas y regiones corticales. Bajo ciertas condiciones, pueden emular redes de perceptrones e incluso superar su capacidad computacional. Aunque su potencial para modelar neuronas biológicas es amplio, su aplicación en reconocimiento de patrones ha sido poco explorada. En esta plática se muestra cómo estos modelos pueden emplearse en diversos problemas de clasificación, se describen sus principales formulaciones, estrategias de aprendizaje y resultados obtenidos, así como sus ventajas, limitaciones y los retos que enfrentan a corto, mediano y largo plazo.",
            semblanza: "El Dr. Roberto Antonio Vázquez Espinoza de los Monteros es doctor en Ciencias de la Computación por el IPN y especialista en inteligencia artificial aplicada a salud, negocios y educación. Desde 2010 forma parte de la Universidad La Salle, donde actualmente se desempeña como Coordinador de Investigación. Ha impulsado proyectos institucionales con enfoque transdisciplinario orientados a la solución de problemáticas nacionales. Es autor de más de 80 artículos científicos, con más de 1000 citas internacionales. Es miembro del Sistema Nacional de Investigadores y secretario de la mesa directiva de la Sociedad Mexicana de Inteligencia Artificial. Ha impartido cursos y dirigido tesis de licenciatura y posgrado."
        }
    ],

    // DATOS DE TALLERES
   talleres: [
        {
            id: 101,
            titulo: "Aplicación de Diseño de Experimentos Multifactorial para la Optimización de Procesos",
            instructores: [
                {
                    nombre: "M.C Emilio Garduño Hernández",
                    institucion: "Tecnológico de Monterrey",
                    imagen: "/img/tallerista/t101.png", 
                    semblanza: "Ingeniero Industrial y de Sistemas egresado del Tecnológico de Monterrey con Maestría en Ciencias de la ingeniería, sus investigaciones se han enfocado al desarrollo de algoritmos metaheurísticos. Es Profesor de la Escuela de Ingeniería y Ciencias en el departamento de Ingeniería Industrial del Tecnológico de Monterrey con un enfoque en materias de estadística, diseño de experimentos y simulación. Asimismo, es autor de publicaciones en investigación relacionados a algoritmos de optimización. Ha sido mentor en el proyecto insignia del campus Cuernavaca de la NASA Human Exploration Rover Challenge y en el proyecto insignia del campus Cuernavaca de la Formula SAE Michigan. Cuenta con experiencia profesional en gestión e implementación de proyectos de software."
                }
            ],
            nivel: "Intermedio",
            cupo: "15 personas",
            modalidad: "Presencial",
            area: "Mecatrónica",
            descripcion: "Este taller proporciona una guía práctica sobre la fabricación y calibración de termopares, sensores esenciales en la medición de temperatura en entornos industriales y científicos. Se abordarán los principios de funcionamiento, los tipos de termopares, los materiales utilizados y los procedimientos para su ensamblaje. Además, se enseñarán técnicas de calibración para garantizar mediciones precisas, utilizando equipos y métodos estandarizados. Los participantes adquirirán conocimientos clave para la aplicación de termopares en sistemas de monitoreo y control de temperatura.",
            tabla_img: "/img/tallerista/c101.png"
        },
        {
            id: 102,
            titulo: "Medición Óptica Automatizada con Huskylens Cam: Demostraciones Didácticas para Laboratorios de Posgrado",
            instructores: [
                {
                    nombre: "Dr. Uriel Rivera-Ortega",
                    institucion: "Universidad Tecnológica de Puebla",
                    imagen: "/img/tallerista/t102.png",
                    semblanza: "Uriel Rivera-Ortega es graduado en Ingeniería Mecatrónica en la Universidad Popular Autónoma del Estado de Puebla (UPAEP), México, en 2005. Durante este periodo, cursó un semestre en la Universidad de Oklahoma (OU), EE. UU., en 2004. Obtuvo su Maestría en Ciencias (MSc) y su Doctorado en Filosofía (PhD) en 2009 y 2013, respectivamente, en la Benemérita Universidad Autónoma de Puebla (BUAP), México; trabajando en pruebas ópticas e interferometría óptica. Realizó una investigación postdoctoral en la Universidad de Amberes, Bélgica, donde desarrolló nuevas técnicas de desplazamiento de fase para interferometría, además de una estancia de investigación en el Tecnológico de Monterrey campus Monterrey.. Actualmente es miembro de Sistema Nacional de Investigadoras e Investigadores (SNII) nivel 1."
                }
            ],
            nivel: "Intermedio",
            cupo: "20 personas",
            modalidad: "Presencial",
            area: "Mecatrónica, Electrónica y Computación",
            descripcion: "Este taller, presenta un enfoque experimental y automatizado para la medición de parámetros ópticos como el índice de refracción y la longitud de onda, utilizando los algoritmos incluidos en una cámara de inteligencia artificial (Huskylens Cam). A través de demostraciones en vivo realizadas por el expositor, se explorarán configuraciones ópticas reproducibles que permiten estimaciones sin necesidad de software de paga ni equipos complejos. Se analizarán fenómenos como el disco de Airy y la medición de índices de refracción en líquidos basados en propiedades geométricas, con énfasis en la interpretación visual y la aplicabilidad en laboratorios de docencia e investigación a nivel posgrado",
            tabla_img: "/img/tallerista/c102.png"
        },
        {
            id: 103,
            titulo: "Introducción a Programación Cuántica",
            instructores: [
                {
                    nombre: "Ing. Oscar Alejandro López ",
                    institucion: "TecNM/CENIDETl",
                    imagen: "/img/tallerista/th103.png",
                    semblanza: "estudia la Maestría en Ciencias de la Computación en el CENIDET, enfocado en Ingeniería de Software Cuántico. Certificado Bronze por QWorld, busca la acreditación Silver y como Qiskit Advocate. Investiga metodologías y herramientas para entornos híbridos clásico-cuánticos."
                },
                {
                    nombre: "Dra. Blanca Dina Valenzuela Robles",
                    institucion: "TecNM/CENIDET",
                    imagen: "/img/tallerista/tm103.png",
                    semblanza: "es investigadora del TecNM/CENIDET, con maestría y doctorado en Ciencias de la Computación, además de una maestría en Educación. Miembro del SNI Nivel I y del cuerpo académico Ingeniería de Software, cuenta con perfil PRODEP. Sus líneas de investigación incluyen e-learning, servicios web, reuso de software, patrones de diseño, gestión y arquitectura de proyectos, DevOps e ingeniería de software cuántica."
                }
            ],
            nivel: "Intermedio",
            cupo: "15 personas",
            modalidad: "Presencial",
            area: "Computación.",
            descripcion: "Este taller ofrece una introducción práctica y visual al mundo de la programación cuántica utilizando Qiskit, el framework de código abierto desarrollado por IBM Quantum. Está dirigido a estudiantes y docentes interesados en comprender los principios básicos de los circuitos cuánticos, la manipulación de qubits, y la aplicación de compuertas cuánticas para construir y simular operaciones fundamentales. A lo largo del taller, los participantes aprenderán a crear y visualizar circuitos cuánticos desde cero, comprendiendo cómo se representan los estados cuánticos y cómo las compuertas afectan su evolución.",
            tabla_img: "/img/tallerista/c103.png"
        }, 
        {
            id: 104,
            titulo: "Teoría y Práctica de la Energía Solar Fotovoltaica",
            instructores: [
                {
                    nombre: "Dr. Miguel Ángel Chagolla Gaona",
                    institucion: "TecNM/Zacatepec",
                    imagen:  "/img/tallerista/t104.png",
                    semblanza: "Profesor-investigador del Instituto Tecnológico de Zacatepec (TecNM), Doctor en Ciencias en Ingeniería Mecánica por el CENIDET, especializado en eficiencia energética, calidad térmica de viviendas y energía en edificaciones. Ha desarrollado trabajos destacados sobre demanda energética y confort térmico en viviendas de Morelos, además de producir materiales didácticos en áreas como refrigeración y aire acondicionado. Cuenta con publicaciones en revistas, congresos y capítulos de libro, así como con la Medalla de Honor en Ciencias del Congreso del Estado de Morelos (2013), y ha impartido cursos y conferencias en foros nacionales como el Congreso Internacional de Investigación del TecNM."
                }
            ],
            nivel: "Intermedio",
            cupo: "10 personas",
            modalidad: "Presencial",
            area: "Desarrollo de Software",
            descripcion: "Este taller ofrece una introducción teórico–práctica a los principios esenciales de la energía solar fotovoltaica, desde el funcionamiento de las celdas solares hasta la instalación básica de sistemas. Se analizarán la radiación solar, el efecto fotovoltaico, la eficiencia de los módulos, sus condiciones de operación y los criterios de dimensionamiento. También se revisarán paneles, inversores, controladores de carga, baterías y aspectos térmicos que influyen en el desempeño. Las actividades prácticas incluirán mediciones, armado de sistemas básicos y evaluación de su rendimiento energético.",
            tabla_img: "/img/tallerista/c104.png"
        },
        {
            id: 105,
            titulo: "Algoritmos MPPT por Rechazo Activo de Perturbaciones Basados en Ultra-Modelo del Sistema PV-Convertidor Cuk usando Co-simulación entre Matlab/Simulink-PSIM",
            instructores: [
                {
                    nombre: "Dr. Jesús Linares Flores",
                    institucion: "UTM",
                    imagen: "/img/tallerista/t105.png",
                    semblanza: "Ingeniero en Electrónica por la BUAP, Maestro en Electrónica de Potencia por la UDLAP y Doctor en Ingeniería Eléctrica (Mecatrónica) por el CINVESTAV-IPN. Realizó estancias académicas en Alemania (DAAD, 2005) y en la BUAP (CONACYT-SEP, 2016–2018). Es Profesor Investigador Titular C en el Instituto de Electrónica y Mecatrónica de la UTM, donde fue director (2010–2016). Miembro del SNI Nivel II y Senior Member IEEE, lidera el cuerpo académico PROMEP-UTMIX-CA-24 en proyectos de electromovilidad. Ha publicado 37 artículos JCR, 4 capítulos de libro, y cuenta con una patente registrada. Ha dirigido más de 30 tesis de licenciatura, maestría y doctorado."
                }
            ],
            nivel: "Intermedio",
            cupo: "15 personas",
            modalidad: "Presencial",
            area: "Seguridad Informática",
            descripcion: "Este curso taller trata sobre la reducción del modelo promedio del sistema que combina al PV con el convertidor CD/CD tipo Cuk. La reducción del modelo promedio se realiza a través de una linealización parcial, también conocida como una parametrización diferencial parcial, para posteriormente redefinir la nueva salida que linealiza de forma exacta al sistema de orden reducido. Enseguida se diseña el algoritmo MPPT por rechazo activo de perturbaciones del ultra-modelo para una carga resistiva. Y finalmente, se realiza una co-simulación del sistema empleando los programas de Matlab/Simulink y PSIM.",
            tabla_img: "/img/tallerista/c105.png"
        },
        {
            id: 106,
            titulo: "Aplicaciones de Herramientas de IA para la Innovación Estratégica y Académica: del Prompt al Proyecto Funcional",
            instructores: [
                {
                    nombre: "Dr. David Arnoldo Valtiérrez Ángel",
                    institucion: "TecNM/Delicias",
                    imagen: "/img/tallerista/t106.png",
                    semblanza: "Doctor en Ciencias con acentuación en Tecnología Educativa y Maestro por la Universidad Autónoma de Chihuahua. Es catedrático del Instituto Tecnológico de Delicias, donde ha desarrollado una sólida trayectoria en Ciencias Computacionales, Metodología Científica e innovación educativa. Ha participado como conferencista, revisor y ponente en diversos congresos nacionales e internacionales, incluyendo importantes foros de Academia Journals en México y Estados Unidos. En 2017 obtuvo el Tercer Lugar Nacional en el Concurso de Prototipos del TecNM en Desarrollo de Software, lo que lo llevó a representar a México en el evento internacional MOSTRATEC – SIET en Brasil. Su experiencia profesional se complementa con estancias formativas y visitas académicas en múltiples países, consolidando una visión amplia y actualizada de los modelos educativos y tecnológicos."
                }
            ],
            nivel: "Intermedio",
            cupo: "20 personas",
            modalidad: "Presencial",
            area: "Electrónica y computación. ",
            descripcion: "Este taller está diseñado para introducir a los participantes en el uso ético y estratégico de herramientas de inteligencia artificial generativa como ChatGPT, Gemini, Deep Seek, Gamma, Copilot, D-ID, Napkin, entre otras más. A través de una metodología práctica y contextualizada, los participantes explorarán el potencial de la IA para transformar procesos académicos, institucionales y de innovación, desde la formulación de prompts efectivos hasta la creación de aplicaciones funcionales con transparencia y responsabilidad tecnológica para fortalecer las competencias, habilidades y destrezas de los usuarios en el ámbito de la Inteligencia Artificial.",
            tabla_img: "/img/tallerista/c106.png"
        },
        {
            id: 107,
            titulo: "Cálculo Fraccionario Aplicado a la Ingeniería Eléctrica. Filtros Fraccionarios",
            instructores: [
                {
                    nombre: "Dr. Diego Seuret Jiménez",
                    institucion: "UAEM/CIICAp",
                    imagen: "/img/tallerista/t107.png",
                    semblanza: "Tiene estudios de Licenciatura en Física, Maestría y Doctorado en la Facultad de Física de la Universidad de la Habana. Profesor de Física General, Métodos Matemáticos de la Física, Electrónica Analógica y Digital, Control Automático, Analógico y Digital. Investigaciones en Crecimiento de Materiales para Celdas Solares. Ha trabajado en la Universidad de la Habana, Universidad de Parma, CNR Italia, Universidad de Angola, Tecnológico de Monterrey, Universidad de Sonora y Universidad Autónoma del Estado de Morelos. Actualmente dirige un grupo de Matemáticas Aplicadas a la Ingeniería. Miembro del SNI Nivel I."
                }
            ],
            nivel: "Intermedio",
            cupo: "20 personas",
            modalidad: "Presencial",
            area: "Electrónica",
            descripcion: "Este taller ofrece una introducción práctica al cálculo fraccionario y su aplicación en la ingeniería eléctrica, especialmente en el diseño de filtros fraccionarios y en el análisis de capacitores basados en materiales fractales. Se revisarán las principales definiciones de derivadas fraccionarias (Riemann–Liouville, Caputo y Grünwald–Letnikov), destacando sus diferencias y usos. Los participantes conocerán cómo los órdenes no enteros permiten modelar sistemas con memoria, respuestas intermedias y comportamientos no lineales. El taller enfatiza la utilidad del cálculo fraccionario para describir y diseñar componentes eléctricos avanzados, mostrando cómo los modelos fraccionarios ofrecen mayor precisión en sistemas y materiales con propiedades fractales.",
            tabla_img:  "/img/tallerista/C107.png"
        },
        {
            id: 108,
            titulo: "Sistemas Embebidos: Aplicaciones",
            instructores: [
                {
                    nombre: "Dr. Iván Antonio Juarez Trujillo",
                    institucion: "TecNM/CENIDET",
                    imagen: "/img/tallerista/t108.png",
                    semblanza: "Licenciado en Ingeniería Eléctrica por la Universidad Autónoma del Estado de Morelos (UAEM), Cuernavaca, México, en 2014. Obtuvo su maestría en el Centro de Investigación en Ingeniería y Ciencias Aplicadas (CIICAp), Morelos, México, en 2017, y su doctorado en la misma institución en 2022. Actualmente es investigador postdoctoral en el Centro Nacional de Investigación y Desarrollo Tecnológico ubicado en Morelos, México. Su experiencia radica en el procesamiento de señales e imágenes, Field Programmable Gate Array (FPGA) y redes neuronales convolucionales (CNN)."
                }
            ],
            nivel: "Intermedio",
            cupo: "15 personas",
            modalidad: "Presencial",
            area: "Electrónica y computación",
            descripcion: "Este taller introduce a los participantes al diseño y uso de sistemas embebidos para resolver problemas reales en áreas como instrumentación, control, IoT y adquisición de datos. Se revisan los elementos básicos de hardware (microcontroladores, SoC-FPGA, sensores, actuadores, buses de comunicación) y su integración con herramientas de desarrollo (IDE, compiladores, depuradores). A través de ejercicios prácticos, los asistentes programan y prueban aplicaciones como lectura de sensores, control de motores, envío de datos a la nube y desarrollo de interfaces básicas de usuario. Al finalizar, el participante será capaz de seleccionar una plataforma embebida adecuada y desarrollar prototipos funcionales orientados a aplicaciones académicas e industriales.",
            tabla_img: "/img/tallerista/C108.png"
        },
    ]
};