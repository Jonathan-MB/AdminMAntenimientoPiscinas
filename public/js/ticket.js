function cabeceras() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    };
}


// ============ VISOR DE LA FOTO ============

const fondoVisor = document.getElementById('fondoVisor');
const fotoGrande = document.getElementById('fotoGrande');


document.querySelectorAll('.miniatura-foto').forEach(function (miniatura) {
    miniatura.addEventListener('click', function () {
        fotoGrande.src = miniatura.dataset.grande;
        fotoGrande.alt = miniatura.alt;
        fondoVisor.classList.add('visor-visible');
    });
});


fondoVisor.addEventListener('click', function () {
    fondoVisor.classList.remove('visor-visible');
    fotoGrande.src = '';
});


document.addEventListener('keydown', function (evento) {
    if (evento.key === 'Escape' && fondoVisor.classList.contains('visor-visible')) {
        fondoVisor.classList.remove('visor-visible');
        fotoGrande.src = '';
    }
});


// ============ QUITAR UNA FOTO ============

document.querySelectorAll('.boton-quitar-foto').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        if (! confirm('¿Quitar esta foto del ticket?')) {
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaFotos + '/' + boton.dataset.id, {
                method: 'DELETE',
                headers: cabeceras()
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo quitar la foto');
                boton.disabled = false;
                return;
            }

            const marco = document.querySelector('figure[data-foto="' + boton.dataset.id + '"]');

            if (marco) {
                marco.remove();
            }
        } catch (error) {
            alert('Error de conexión al quitar la foto');
            boton.disabled = false;
        }
    });
});


// ============ CAMARA O GALERIA ============

const fotoCamara = document.getElementById('fotoCamara');
const fotoGaleria = document.getElementById('fotoGaleria');
const fotosElegidas = document.getElementById('fotosElegidas');
const botonSubirFotos = document.getElementById('botonSubirFotos');


//  Los dos campos suman: se puede tomar una y ademas elegir de la galeria
function contarElegidas() {
    const camara = fotoCamara.files.length;
    const galeria = fotoGaleria.files.length;
    const total = camara + galeria;

    botonSubirFotos.disabled = total === 0;

    if (total === 0) {
        fotosElegidas.textContent = '';
        return;
    }

    const partes = [];

    if (camara) {
        partes.push(camara === 1 ? '1 foto tomada' : camara + ' fotos tomadas');
    }

    if (galeria) {
        partes.push(galeria === 1 ? '1 de la galería' : galeria + ' de la galería');
    }

    fotosElegidas.textContent = partes.join(' · ') + '. Toca «Subir» para guardarlas.';
}


//  Si algun dia el formulario deja de estar, que no se caiga el resto
if (fotoCamara && fotoGaleria) {
    [fotoCamara, fotoGaleria].forEach(function (campo) {
        campo.addEventListener('change', contarElegidas);
    });

    //  Se apaga aqui y no en el HTML: sin JavaScript el boton debe funcionar
    contarElegidas();
}


// ============ COPIAR LA DIRECCION ============

const botonCopiar = document.getElementById('botonCopiar');


//  El portapapeles moderno pide sitio seguro (https o localhost). En un
//  telefono viejo o si el navegador lo niega, se cae al truco del textarea.
function copiarTexto(texto) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(texto);
    }

    return new Promise(function (resolver, rechazar) {
        const caja = document.createElement('textarea');
        caja.value = texto;
        caja.setAttribute('readonly', '');
        caja.style.position = 'fixed';
        caja.style.top = '-1000px';
        document.body.appendChild(caja);
        caja.select();

        try {
            document.execCommand('copy') ? resolver() : rechazar();
        } catch (error) {
            rechazar(error);
        } finally {
            document.body.removeChild(caja);
        }
    });
}


if (botonCopiar) {
    //  Se enciende desde aqui: si no hay JavaScript no debe verse un boton
    //  que no hace nada. La direccion se puede seleccionar a mano igual.
    botonCopiar.hidden = false;

    botonCopiar.addEventListener('click', function () {
        copiarTexto(botonCopiar.dataset.direccion).then(function () {
            botonCopiar.textContent = 'Copiada';
            botonCopiar.classList.add('boton-copiado');

            setTimeout(function () {
                botonCopiar.textContent = 'Copiar';
                botonCopiar.classList.remove('boton-copiado');
            }, 2000);
        }).catch(function () {
            botonCopiar.textContent = 'Selecciónala y cópiala';
        });
    });
}


// ============ EDITAR LA OBSERVACION ============

const botonEditarObservacion = document.getElementById('botonEditarObservacion');
const botonCancelarObservacion = document.getElementById('botonCancelarObservacion');
const botonGuardarObservacion = document.getElementById('botonGuardarObservacion');
const editorObservacion = document.getElementById('editorObservacion');
const textoObservacion = document.getElementById('textoObservacion');
const campoObservacion = document.getElementById('campoObservacion');


function verEditor(abierto) {
    editorObservacion.hidden = ! abierto;
    textoObservacion.hidden = abierto;
    botonEditarObservacion.hidden = abierto;

    if (abierto) {
        campoObservacion.focus();
    }
}


if (botonEditarObservacion) {
    //  Se enciende aqui: sin JavaScript no hay forma de guardar
    botonEditarObservacion.hidden = false;

    botonEditarObservacion.addEventListener('click', function () {
        verEditor(true);
    });


    botonCancelarObservacion.addEventListener('click', function () {
        //  Devolver el campo a lo que hay guardado, o "cancelar" dejaria el
        //  texto a medio escribir esperando en la proxima apertura
        campoObservacion.value = campoObservacion.defaultValue;
        verEditor(false);
    });


    botonGuardarObservacion.addEventListener('click', async function () {
        botonGuardarObservacion.disabled = true;

        try {
            const respuesta = await fetch(rutaObservacion, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify({ observacion: campoObservacion.value }),
            });

            const datos = await respuesta.json();

            if (! respuesta.ok) {
                alert(datos.message || 'No se pudo guardar la observación');
                botonGuardarObservacion.disabled = false;
                return;
            }

            //  Se recarga a proposito: la edicion entra en el historial de
            //  abajo, y actualizarlo a mano seria repetir aqui lo que ya
            //  arma Blade. Si no se recarga, la constancia se ve incompleta.
            location.reload();

        } catch (error) {
            alert('Error de conexión. Revisa la señal e intenta otra vez.');
            botonGuardarObservacion.disabled = false;
        }
    });
}
