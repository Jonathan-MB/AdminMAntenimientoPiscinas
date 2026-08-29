// ============ CAMBIAR DE DIA SIN RECARGAR ============

const contenidoDia = document.getElementById('contenidoDia');
const tituloDia = document.getElementById('tituloDia');
const cargando = document.getElementById('cargando');


//  Escapa el texto que viene de la base antes de meterlo en el HTML
function limpiar(texto) {
    const caja = document.createElement('div');
    caja.textContent = texto === null || texto === undefined ? '' : String(texto);

    return caja.innerHTML;
}


function valor(dato) {
    return dato === null || dato === undefined || dato === '' ? '—' : limpiar(dato);
}


function textoMetros(metros) {
    if (! metros || ! metros.length) {
        return '—';
    }

    return metros.map(function (metro) {
        return limpiar(metro.nombre) + ': ' + valor(metro.lectura);
    }).join(' · ');
}


function dibujarDia(datos) {
    tituloDia.textContent = datos.titulo.charAt(0).toUpperCase() + datos.titulo.slice(1);

    apuntarImpresion(datos.fecha, datos.vacio);

    if (datos.vacio) {
        contenidoDia.innerHTML = '<p class="sin-registro">No hay registro de este día.</p>';
        return;
    }

    let html = '<div class="resumen-jornada">'
        + '<div class="dato-resumen"><span class="titulo-elemento">Metros de agua</span><strong>' + textoMetros(datos.metros) + '</strong></div>'
        + '<div class="dato-resumen"><span class="titulo-elemento">Registró</span><strong>' + limpiar(datos.colaborador) + '</strong></div>'
        + '<div class="dato-resumen"><span class="titulo-elemento">Rondas</span><strong>' + datos.rondas.length + '</strong></div>'
        + '</div>';

    datos.rondas.forEach(function (ronda) {
        html += '<div class="bloque-ronda">'
            + '<div class="cabecera-ronda">'
            + '<h3 class="nombre-ronda">' + limpiar(ronda.nombre) + '</h3>'
            + '<span class="hora-ronda">' + limpiar(ronda.hora) + '</span>'
            + '</div>';

        ronda.mediciones.forEach(function (medicion) {
            html += '<div class="tarjeta-piscina">'
                + '<div class="cabecera-piscina">'
                + '<span class="nombre-piscina">' + limpiar(medicion.piscina) + '</span>'
                + (medicion.retrolavado ? '<span class="marca-retrolavado">Retrolavado</span>' : '')
                + '</div>'
                + '<span class="etiqueta-bloque">Pruebas del agua</span>'
                + '<div class="rejilla-lecturas">'
                + '<div class="lectura"><span>Cl libre</span><strong>' + valor(medicion.clLibre) + '</strong></div>'
                + '<div class="lectura"><span>Cl total</span><strong>' + valor(medicion.clTotal) + '</strong></div>'
                + '<div class="lectura"><span>Combinado</span><strong>' + valor(medicion.clCombinado) + '</strong></div>'
                + '<div class="lectura"><span>pH</span><strong>' + valor(medicion.ph) + '</strong></div>'
                + '<div class="lectura"><span>Alcalinidad</span><strong>' + valor(medicion.alcalinidad) + '</strong></div>'
                + '<div class="lectura"><span>Dureza</span><strong>' + valor(medicion.durezaCalcio) + '</strong></div>'
                + '<div class="lectura"><span>Cianúrico</span><strong>' + valor(medicion.acidoCianurico) + '</strong></div>'
                + '</div>';

            if (medicion.dosis.length) {
                html += '<span class="etiqueta-bloque">Químicos aplicados</span>'
                    + '<div class="linea-dosis">';

                medicion.dosis.forEach(function (dosis) {
                    html += '<span class="pastilla-dosis">' + limpiar(dosis.producto) + ' · ' + limpiar(dosis.cantidad) + ' ' + limpiar(dosis.unidad) + '</span>';
                });

                html += '</div>';
            }

            if (medicion.observacion) {
                html += '<span class="etiqueta-bloque">Observación del técnico</span>'
                    + '<p class="observacion-piscina">' + limpiar(medicion.observacion) + '</p>';
            }

            html += '</div>';
        });

        if (ronda.observacion) {
            html += '<p class="observacion-ronda">'
                + '<span class="etiqueta-observacion">Observación de la ronda</span>'
                + limpiar(ronda.observacion) + '</p>';
        }

        html += '</div>';
    });

    contenidoDia.innerHTML = html;
}


//  Sin registro no hay nada que imprimir: el enlace queda apagado
function apuntarImpresion(fecha, vacio) {
    botonImprimirDia.href = rutaDia + '/' + fecha + '/imprimir';
    botonImprimirDia.classList.toggle('boton-apagado', vacio);
}


async function abrirDia(fecha, celda) {
    document.querySelectorAll('.celda-elegida').forEach(function (otra) {
        otra.classList.remove('celda-elegida');
    });

    celda.classList.add('celda-elegida');
    cargando.classList.add('cargando-visible');

    try {
        const res = await fetch(rutaDia + '/' + fecha, {
            headers: { 'Accept': 'application/json' }
        });

        if (! res.ok) {
            alert('No se pudo cargar ese día');
            return;
        }

        dibujarDia(await res.json());

        //  Dejar la fecha en la barra de direcciones, sin recargar
        const url = new URL(window.location);
        url.searchParams.set('fecha', fecha);
        history.replaceState(null, '', url);
    } catch (error) {
        alert('Error de conexión al cargar el día');
    } finally {
        cargando.classList.remove('cargando-visible');
    }
}


document.querySelectorAll('.celda-dia').forEach(function (celda) {
    if (celda.disabled) {
        return;
    }

    celda.addEventListener('click', function () {
        abrirDia(celda.dataset.fecha, celda);
    });
});
