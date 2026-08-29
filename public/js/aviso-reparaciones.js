//  Sondeo cada 15 segundos. No hay WebSockets porque el hosting es compartido
//  y no admite un proceso escuchando todo el dia.
const segundosSondeo = 15;

const marcaAbiertos = document.getElementById('marcaAbiertos');
const avisoReparaciones = document.getElementById('avisoReparaciones');
const textoAviso = document.getElementById('textoAviso');
const botonCerrarAviso = document.getElementById('botonCerrarAviso');

let selloPrevio = null;
let abiertosPrevios = parseInt(marcaAbiertos.textContent, 10) || 0;
let conteosPrevios = null;
let reloj = null;


function frase(datos) {
    const diferencia = datos.abiertos - abiertosPrevios;

    if (diferencia > 0) {
        return diferencia === 1
            ? 'Entró un ticket nuevo'
            : 'Entraron ' + diferencia + ' tickets nuevos';
    }

    if (diferencia < 0) {
        return diferencia === -1
            ? 'Una reparación se cobró o se eliminó'
            : Math.abs(diferencia) + ' reparaciones se cobraron o se eliminaron';
    }

    //  Mismo total: alguno se movió de columna. Se dice cuál si se puede.
    if (conteosPrevios) {
        for (const estado in datos.conteos) {
            if (datos.conteos[estado] > conteosPrevios[estado]) {
                const columna = document.querySelector('.columna-' + estado + ' .nombre-estado');

                if (columna) {
                    return 'Un ticket pasó a «' + columna.textContent.trim() + '»';
                }
            }
        }
    }

    return 'Un ticket cambió de estado';
}


function mostrarAviso(texto) {
    textoAviso.textContent = texto;
    avisoReparaciones.classList.add('aviso-visible');
}


function pintarMarca(abiertos) {
    marcaAbiertos.textContent = abiertos;
    marcaAbiertos.classList.toggle('marca-vacia', abiertos === 0);

    //  Se quita y se vuelve a poner para que la animación arranque de nuevo
    marcaAbiertos.classList.remove('marca-latiendo');
    void marcaAbiertos.offsetWidth;
    marcaAbiertos.classList.add('marca-latiendo');
}


async function mirarReparaciones() {
    try {
        const res = await fetch(rutaResumen, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        });

        //  Sesión caducada o permiso retirado: se deja de preguntar
        if (res.status === 401 || res.status === 403 || res.status === 419) {
            clearInterval(reloj);
            return;
        }

        if (! res.ok) {
            return;
        }

        const datos = await res.json();
        const cambio = selloPrevio === null
            ? datos.abiertos !== abiertosPrevios
            : datos.sello !== selloPrevio;

        if (cambio) {
            mostrarAviso(frase(datos));
            pintarMarca(datos.abiertos);
        }

        selloPrevio = datos.sello;
        abiertosPrevios = datos.abiertos;
        conteosPrevios = datos.conteos;
    } catch (error) {
        //  Un corte de red no debe llenar la consola: en 15 segundos se reintenta
    }
}


botonCerrarAviso.addEventListener('click', function () {
    avisoReparaciones.classList.remove('aviso-visible');
});


//  Con la pestaña de fondo no se pregunta; al volver se pregunta enseguida
document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
        clearInterval(reloj);
        return;
    }

    mirarReparaciones();
    reloj = setInterval(mirarReparaciones, segundosSondeo * 1000);
});


reloj = setInterval(mirarReparaciones, segundosSondeo * 1000);
