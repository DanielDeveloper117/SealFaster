// *******AQUI VA EL CODIGO DE NUEVA VALIDACION/LIMITANTES DE MEDIDAS MINIMAS Y MAXIMAS DEL MAQUINADO, SI UNA SE CUMPLE SE RECHAZA Y RETORNA FALSE ********

    // Lista de perfiles que aplican limitantes CNC
    const perfilesConLimitantes = [
        'R04-A', 'R05-A', 'R06-P', 'R06-R', 'R07-P', 'R07-R', 'R08-A', 'R15-P',
        'K01-P', 'K01-PE', 'K01-R', 'K01-RE', 'K05-P', 'K05-R', 'K06-P', 'K06-R', 'K16-A', 'K16-B', 'K35-P',
        'S01-P', 'S01-R', 'S05-P', 'S05-R', 'S06-P', 'S06-R', 'S08-P', 'S08-PE', 'S08-R', 'S16-A', 'S17-P', 'S17-R', 'S35-P'
    ];

    // Definicion de limitantes por dureza y herramienta
    const limitantesHerramientas = {
        blandos: {
            112: { DI_MIN: 5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 10 },
            212: { DI_MIN: 7.5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 17 },
            103: { DI_MIN: 11, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 13 },
            104: { DI_MIN: 11, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 13 },
            113: { DI_MIN: 16.5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 31.5 },
            114: { DI_MIN: 16.5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 31.5 },
            139: { DI_MIN: 14.5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 50.8 },
            102: { DI_MIN: 23, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 50.8 },
            201: { DI_MIN: 60, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 55 },
            202: { DI_MIN: 60, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 55 },
        },
        duros: {
            112: { DI_MIN: 5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 7 },
            212: { DI_MIN: 7.5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 14 },
            103: { DI_MIN: 11, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 10 },
            104: { DI_MIN: 11, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 10 },
            113: { DI_MIN: 16.5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 28.5 },
            114: { DI_MIN: 16.5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 28.5 },
            139: { DI_MIN: 14.5, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 47.8 },
            102: { DI_MIN: 23, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 47.8 },
            201: { DI_MIN: 60, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 52 },
            202: { DI_MIN: 60, DI_MAX: 845, DE_MIN: 9.5, DE_MAX: 850, SECCION_MIN: 2.25, SECCION_MAX: 45, H_MIN: 2.6, H_MAX: 52 },
        }
    };

function obtenerHerramientaSegunDimensiones(dureza, DI_R, DE_R, ALTURA_R, SECCION) {
    const herramientas = limitantesHerramientas[dureza];
    for (const numHerramienta in herramientas) {
        const lim = herramientas[numHerramienta];

        if (DI_R >= lim.DI_MIN && DI_R <= lim.DI_MAX) {
            if (DE_R >= lim.DE_MIN && DE_R <= lim.DE_MAX) {
                if (ALTURA_R >= lim.H_MIN && ALTURA_R <= lim.H_MAX) {
                    return { numHerramienta, limitante: lim };
                }
            }
        }
    }
    return null;
}

// dentro de validarCamposDimensiones
if (perfilesConLimitantes.includes(perfilSello) &&
   (tipoDurezaMateriales == "blandos" || tipoDurezaMateriales == "duros")) {

    const resultado = obtenerHerramientaSegunDimensiones(tipoDurezaMateriales, DI_R, DE_R, ALTURA_R, SECCION);

    if (!resultado) {
        // Mensaje simple (para usuario sin conocimientos)
        let mensajeSimple = "No se encontro herramienta disponible para estas dimensiones.";

        // Mensaje tecnico (para operador CNC)
        let mensajeTecnico = "No se encontro herramienta para estas dimensiones.<br>";
        mensajeTecnico += `Material: ${tipoDurezaMateriales}<br>`;
        mensajeTecnico += `Dimensiones dadas: DI=${DI_R}, DE=${DE_R}, H=${ALTURA_R}, Seccion=${SECCION}<br><br>`;

        mensajeTecnico += "Rangos de herramientas disponibles:<br>";

        const herramientas = limitantesHerramientas[tipoDurezaMateriales];
        for (const numHerramienta in herramientas) {
            const lim = herramientas[numHerramienta];
            mensajeTecnico += `Herramienta ${numHerramienta}: `;
            mensajeTecnico += `DI [${lim.DI_MIN}-${lim.DI_MAX}], `;
            mensajeTecnico += `DE [${lim.DE_MIN}-${lim.DE_MAX}], `;
            mensajeTecnico += `H [${lim.H_MIN}-${lim.H_MAX}], `;
            mensajeTecnico += `Seccion [${lim.SECCION_MIN}-${lim.SECCION_MAX}]<br>`;
        }

        // aqui decides cual mensaje mostrar
        // ejemplo: mostrar ambos en frontend en diferentes contenedores
        $("#containerErrorDimensiones_cliente span").html(mensajeTecnico);
        //$("#containerErrorDimensiones_cliente span").html(mensajeSimple);

        window.DIMENSIONES_VALIDAS = false;
        return false;
    }

    const { numHerramienta, limitante } = resultado;
    console.log("Herramienta seleccionada automaticamente:", numHerramienta);
    console.log("Limitante aplicada:", limitante);

    let violaciones = [];

    function agregarViolacion(tipo, valor, min, max) {
        violaciones.push(`Para ${tipoDurezaMateriales}, el ${tipo} debe estar entre ${min} y ${max} mm (valor: ${valor})`);
    }

    if (DI_R < limitante.DI_MIN || DI_R > limitante.DI_MAX) {
        agregarViolacion("Diametro interior", DI_R, limitante.DI_MIN, limitante.DI_MAX);
    }
    if (DE_R < limitante.DE_MIN || DE_R > limitante.DE_MAX) {
        agregarViolacion("Diametro exterior", DE_R, limitante.DE_MIN, limitante.DE_MAX);
    }
    if (SECCION < limitante.SECCION_MIN || SECCION > limitante.SECCION_MAX) {
        agregarViolacion("Seccion radial", SECCION, limitante.SECCION_MIN, limitante.SECCION_MAX);
    }
    if (ALTURA_R < limitante.H_MIN) {
        agregarViolacion("Altura", ALTURA_R, limitante.H_MIN, limitante.H_MAX);
    }
    if (ALTURA_R > limitante.H_MAX) {
        agregarViolacion("Altura", ALTURA_R, limitante.H_MIN, limitante.H_MAX);
    }

    if (violaciones.length > 0) {
        $("#containerErrorDimensiones_cliente span").html(violaciones.join("<br>"));
        esAdvertencia = true;
    }
}


// ********************************************************************************************************************************************************

