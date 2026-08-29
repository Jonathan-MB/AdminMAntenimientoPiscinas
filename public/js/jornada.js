function cabeceras() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    };
}


// ============ GUARDADO AUTOMATICO DE LA TARJETA ============

const estado = document.getElementById('estadoGuardado');

let guardando = false;
let pendiente = false;


function mostrar(texto, clase) {
    if (! estado) {
        return;
    }

    estado.textContent = texto;
    estado.className = 'estado-guardado ' + (clase || '');
}


async function guardarJornada() {
    if (guardando) {
        pendiente = true;
        return;
    }

    guardando = true;
    mostrar('Guardando…', 'estado-trabajando');

    //  Una lectura por metro, indexada por el id del metro
    const lecturas = {};

    document.querySelectorAll('[data-metro]').forEach(function (campo) {
        lecturas[campo.dataset.metro] = campo.value;
    });

    const materiales = document.getElementById('materialesSacados');

    try {
        const res = await fetch(rutaJornada, {
            method: 'PATCH',
            headers: cabeceras(),
            body: JSON.stringify({
                materialesSacados: materiales ? materiales.value : '',
                lecturas: lecturas
            })
        });

        const datos = await res.json();

        if (res.ok) {
            mostrar('Guardado ' + datos.hora, 'estado-hecho');
        } else if (res.status === 422 && datos.errors) {
            const primero = Object.values(datos.errors)[0][0];
            mostrar(primero, 'estado-fallo');
        } else if (res.status === 422) {
            //  "No se detectaron cambios" no es un fallo: es que ya estaba guardado
            mostrar('Guardado', 'estado-hecho');
        } else {
            mostrar(datos.message || 'No se pudo guardar', 'estado-fallo');
        }
    } catch (error) {
        mostrar('Sin conexión. Lo escrito no se ha guardado.', 'estado-fallo');
    }

    guardando = false;

    if (pendiente) {
        pendiente = false;
        guardarJornada();
    }
}


if (estado) {
    document.querySelectorAll('[data-metro]').forEach(function (campo) {
        campo.addEventListener('change', guardarJornada);
    });

    const materiales = document.getElementById('materialesSacados');

    if (materiales) {
        materiales.addEventListener('change', guardarJornada);
    }
}


// ============ LISTADO DE TRABAJO ============

const marcadorTareas = document.getElementById('marcadorTareas');
const cuentaHechas = document.getElementById('cuentaHechas');
const cuentaFaltan = document.getElementById('cuentaFaltan');
const avanceTareas = document.getElementById('avanceTareas');


//  El contador se calcula de las casillas, no de un numero guardado aparte:
//  asi no puede quedar desfasado de lo que se ve.
function contarTareas() {
    const casillas = document.querySelectorAll('.casilla-tarea');
    const total = casillas.length;
    let hechas = 0;

    casillas.forEach(function (casilla) {
        const linea = casilla.closest('.linea-tarea');

        if (casilla.checked) {
            hechas++;
        }

        linea.classList.toggle('tarea-hecha', casilla.checked);
        linea.classList.toggle('tarea-falta', ! casilla.checked);
    });

    cuentaHechas.textContent = hechas;
    cuentaFaltan.textContent = total - hechas;
    avanceTareas.style.width = total ? (hechas / total * 100) + '%' : '0';
    marcadorTareas.classList.toggle('marcador-completo', total > 0 && hechas === total);
}


document.querySelectorAll('.casilla-tarea').forEach(function (casilla) {
    if (casilla.disabled) {
        return;
    }

    casilla.addEventListener('change', async function () {
        const marcada = casilla.checked;
        casilla.disabled = true;
        contarTareas();

        try {
            const res = await fetch(rutaJornada + '/tarea/' + casilla.dataset.tarea, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify({ hecha: marcada })
            });

            if (! res.ok) {
                const datos = await res.json();
                alert(datos.message || 'No se pudo guardar la tarea');
                //  Dejar la casilla como estaba
                casilla.checked = ! marcada;
                contarTareas();
            }
        } catch (error) {
            alert('Error de conexión');
            casilla.checked = ! marcada;
            contarTareas();
        }

        casilla.disabled = false;
    });
});


if (marcadorTareas) {
    contarTareas();
}
