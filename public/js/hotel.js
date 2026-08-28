//  Cabecera común de todas las peticiones
function cabeceras() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    };
}


//  Abre y cierra un pop up por sus tres elementos
function conectarPopup(idFondo, idAbrir, idCerrar, idFoco) {
    const fondo = document.getElementById(idFondo);
    const abrir = document.getElementById(idAbrir);
    const cerrar = document.getElementById(idCerrar);

    abrir.addEventListener('click', function () {
        fondo.classList.add('popup-visible');
        document.getElementById(idFoco).focus();
    });

    cerrar.addEventListener('click', function () {
        fondo.classList.remove('popup-visible');
    });

    fondo.addEventListener('click', function (evento) {
        if (evento.target === fondo) {
            fondo.classList.remove('popup-visible');
        }
    });

    return fondo;
}


const popupRonda = conectarPopup('fondoPopupRonda', 'botonAbrirRonda', 'botonCerrarRonda', 'nombreRonda');
const popupPiscina = conectarPopup('fondoPopupPiscina', 'botonAbrirPiscina', 'botonCerrarPiscina', 'nombrePiscina');
const popupMetro = conectarPopup('fondoPopupMetro', 'botonAbrirMetro', 'botonCerrarMetro', 'nombreMetro');


// ============ DATOS DEL HOTEL ============

const botonGuardarHotel = document.getElementById('botonGuardarHotel');

botonGuardarHotel.addEventListener('click', async function () {
    const cuerpo = {
        nombre:    document.getElementById('nombre').value.trim(),
        contacto:  document.getElementById('contacto').value.trim(),
        telefono:  document.getElementById('telefono').value.trim(),
        direccion: document.getElementById('direccion').value.trim(),
        activo:    document.getElementById('activo').checked,
    };

    if (cuerpo.nombre === '') {
        alert('El nombre del hotel no puede quedar vacío');
        return;
    }

    botonGuardarHotel.disabled = true;

    try {
        const res = await fetch(rutaHoteles + '/' + hotelId, {
            method: 'PATCH',
            headers: cabeceras(),
            body: JSON.stringify(cuerpo)
        });

        const datos = await res.json();
        alert(datos.message);

        if (res.ok) {
            location.reload();
            return;
        }
    } catch (error) {
        alert('Error de conexión al guardar');
    }

    botonGuardarHotel.disabled = false;
});


// ============ RONDAS ============

//  Guardar los tres campos editables de una fila
document.querySelectorAll('.boton-guardar-ronda').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const fila = document.querySelector('tr[data-ronda="' + boton.dataset.id + '"]');

        const cuerpo = {
            nombre: fila.querySelector('.campo-nombre').value.trim(),
            hora:   fila.querySelector('.campo-hora').value,
            orden:  Number(fila.querySelector('.campo-orden').value),
        };

        if (cuerpo.nombre === '') {
            alert('El nombre de la ronda no puede quedar vacío');
            return;
        }

        if (cuerpo.hora === '') {
            alert('La hora de la ronda es obligatoria');
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaRondas + '/' + boton.dataset.id, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify(cuerpo)
            });

            const datos = await res.json();
            alert(datos.message);

            if (res.ok) {
                location.reload();
                return;
            }
        } catch (error) {
            alert('Error de conexión al guardar');
        }

        boton.disabled = false;
    });
});


document.querySelectorAll('.boton-alternar-ronda').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const activa = boton.dataset.activa === '1';

        boton.disabled = true;

        try {
            const res = await fetch(rutaRondas + '/' + boton.dataset.id, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify({ activa: ! activa })
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo cambiar');
                boton.disabled = false;
                return;
            }

            location.reload();
        } catch (error) {
            alert('Error de conexión');
            boton.disabled = false;
        }
    });
});


document.querySelectorAll('.boton-eliminar-ronda').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        if (! confirm('¿Eliminar la ronda ' + boton.dataset.nombre + '?')) {
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaRondas + '/' + boton.dataset.id, {
                method: 'DELETE',
                headers: cabeceras()
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo eliminar');
                boton.disabled = false;
                return;
            }

            const fila = document.querySelector('tr[data-ronda="' + boton.dataset.id + '"]');

            if (fila) {
                fila.remove();
            }
        } catch (error) {
            alert('Error de conexión al eliminar');
            boton.disabled = false;
        }
    });
});


// ============ METROS DE AGUA ============

document.querySelectorAll('.boton-guardar-metro').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const fila = document.querySelector('tr[data-metro="' + boton.dataset.id + '"]');

        const cuerpo = {
            nombre: fila.querySelector('.campo-nombre').value.trim(),
            orden:  Number(fila.querySelector('.campo-orden').value),
        };

        if (cuerpo.nombre === '') {
            alert('El nombre del metro no puede quedar vacío');
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaMetros + '/' + boton.dataset.id, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify(cuerpo)
            });

            const datos = await res.json();
            alert(datos.message);

            if (res.ok) {
                location.reload();
                return;
            }
        } catch (error) {
            alert('Error de conexión al guardar');
        }

        boton.disabled = false;
    });
});


document.querySelectorAll('.boton-alternar-metro').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const activo = boton.dataset.activo === '1';

        boton.disabled = true;

        try {
            const res = await fetch(rutaMetros + '/' + boton.dataset.id, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify({ activo: ! activo })
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo cambiar');
                boton.disabled = false;
                return;
            }

            location.reload();
        } catch (error) {
            alert('Error de conexión');
            boton.disabled = false;
        }
    });
});


document.querySelectorAll('.boton-eliminar-metro').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        if (! confirm('¿Eliminar el metro ' + boton.dataset.nombre + '?')) {
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaMetros + '/' + boton.dataset.id, {
                method: 'DELETE',
                headers: cabeceras()
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo eliminar');
                boton.disabled = false;
                return;
            }

            const fila = document.querySelector('tr[data-metro="' + boton.dataset.id + '"]');

            if (fila) {
                fila.remove();
            }
        } catch (error) {
            alert('Error de conexión al eliminar');
            boton.disabled = false;
        }
    });
});


// ============ PISCINAS ============

document.querySelectorAll('.boton-alternar-piscina').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const activa = boton.dataset.activa === '1';

        boton.disabled = true;

        try {
            const res = await fetch(rutaPiscinas + '/' + boton.dataset.id, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify({ activa: ! activa })
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo cambiar');
                boton.disabled = false;
                return;
            }

            location.reload();
        } catch (error) {
            alert('Error de conexión');
            boton.disabled = false;
        }
    });
});


document.querySelectorAll('.boton-eliminar-piscina').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        if (! confirm('¿Eliminar la piscina ' + boton.dataset.nombre + '?')) {
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaPiscinas + '/' + boton.dataset.id, {
                method: 'DELETE',
                headers: cabeceras()
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo eliminar');
                boton.disabled = false;
                return;
            }

            const fila = document.querySelector('tr[data-piscina="' + boton.dataset.id + '"]');

            if (fila) {
                fila.remove();
            }
        } catch (error) {
            alert('Error de conexión al eliminar');
            boton.disabled = false;
        }
    });
});


//  Si un formulario traía errores, reabrir el pop up que corresponde
if (document.querySelector('.mensaje-error')) {
    if (document.getElementById('nombreRonda').value !== '') {
        popupRonda.classList.add('popup-visible');
    }

    if (document.getElementById('nombrePiscina').value !== '') {
        popupPiscina.classList.add('popup-visible');
    }

    if (document.getElementById('nombreMetro').value !== '') {
        popupMetro.classList.add('popup-visible');
    }
}
