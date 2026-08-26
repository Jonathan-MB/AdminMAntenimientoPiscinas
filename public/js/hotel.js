//  Cabecera común de todas las peticiones
function cabeceras() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    };
}


// ============ DATOS DEL HOTEL ============

const botonGuardarHotel = document.getElementById('botonGuardarHotel');

botonGuardarHotel.addEventListener('click', async function () {
    const cuerpo = {
        nombre:          document.getElementById('nombre').value.trim(),
        contacto:        document.getElementById('contacto').value.trim(),
        telefono:        document.getElementById('telefono').value.trim(),
        direccion:       document.getElementById('direccion').value.trim(),
        horaRondaManana: document.getElementById('horaRondaManana').value,
        horaRondaTarde:  document.getElementById('horaRondaTarde').value,
        activo:          document.getElementById('activo').checked,
    };

    if (cuerpo.nombre === '') {
        alert('El nombre del hotel no puede quedar vacío');
        return;
    }

    if (cuerpo.horaRondaManana === '' || cuerpo.horaRondaTarde === '') {
        alert('Las dos horas de ronda son obligatorias');
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


// ============ POP UP AGREGAR PISCINA ============

const fondoPopupCrear = document.getElementById('fondoPopupCrear');
const botonAbrirCrear = document.getElementById('botonAbrirCrear');
const botonCerrarCrear = document.getElementById('botonCerrarCrear');


botonAbrirCrear.addEventListener('click', function () {
    fondoPopupCrear.classList.add('popup-visible');
    document.getElementById('nombrePiscina').focus();
});


botonCerrarCrear.addEventListener('click', function () {
    fondoPopupCrear.classList.remove('popup-visible');
});


fondoPopupCrear.addEventListener('click', function (evento) {
    if (evento.target === fondoPopupCrear) {
        fondoPopupCrear.classList.remove('popup-visible');
    }
});


// ============ ACTIVAR Y DESACTIVAR PISCINA ============

document.querySelectorAll('.boton-alternar').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const id = boton.dataset.id;
        const activa = boton.dataset.activa === '1';

        boton.disabled = true;

        try {
            const res = await fetch(rutaPiscinas + '/' + id, {
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


// ============ ELIMINAR PISCINA ============

document.querySelectorAll('.boton-eliminar').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const id = boton.dataset.id;
        const nombre = boton.dataset.nombre;

        if (! confirm('¿Eliminar la piscina ' + nombre + '?')) {
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaPiscinas + '/' + id, {
                method: 'DELETE',
                headers: cabeceras()
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo eliminar');
                boton.disabled = false;
                return;
            }

            const fila = document.querySelector('tr[data-piscina="' + id + '"]');

            if (fila) {
                fila.remove();
            }
        } catch (error) {
            alert('Error de conexión al eliminar');
            boton.disabled = false;
        }
    });
});


//  Si el formulario de piscina traía errores, reabrir el pop up
if (document.querySelector('.mensaje-error') && document.getElementById('nombrePiscina').value !== '') {
    fondoPopupCrear.classList.add('popup-visible');
}
