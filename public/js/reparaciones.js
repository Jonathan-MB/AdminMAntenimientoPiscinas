function cabeceras() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    };
}


// ============ POP UP CREAR ============

const fondoPopupCrear = document.getElementById('fondoPopupCrear');
const botonAbrirCrear = document.getElementById('botonAbrirCrear');
const botonCerrarCrear = document.getElementById('botonCerrarCrear');


botonAbrirCrear.addEventListener('click', function () {
    fondoPopupCrear.classList.add('popup-visible');
    document.getElementById('hotelId').focus();
});


botonCerrarCrear.addEventListener('click', function () {
    fondoPopupCrear.classList.remove('popup-visible');
});


fondoPopupCrear.addEventListener('click', function (evento) {
    if (evento.target === fondoPopupCrear) {
        fondoPopupCrear.classList.remove('popup-visible');
    }
});


// ============ MOVER DE ESTADO ============

document.querySelectorAll('.campo-estado').forEach(function (selector) {
    selector.addEventListener('change', async function () {
        selector.disabled = true;

        try {
            const res = await fetch(rutaReparaciones + '/' + selector.dataset.ticket, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify({ estado: selector.value })
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo mover el ticket');
                selector.disabled = false;
                return;
            }

            //  El ticket cambia de columna, asi que se recarga el tablero
            location.reload();
        } catch (error) {
            alert('Error de conexión al mover el ticket');
            selector.disabled = false;
        }
    });
});


// ============ ELIMINAR ============

document.querySelectorAll('.boton-eliminar-ticket').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        if (! confirm('¿Eliminar el ticket "' + boton.dataset.titulo + '"?\n\nSe pierde también su historial de movimientos.')) {
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaReparaciones + '/' + boton.dataset.id, {
                method: 'DELETE',
                headers: cabeceras()
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo eliminar');
                boton.disabled = false;
                return;
            }

            const tarjeta = document.querySelector('article[data-ticket="' + boton.dataset.id + '"]');

            if (tarjeta) {
                tarjeta.remove();
            }
        } catch (error) {
            alert('Error de conexión al eliminar');
            boton.disabled = false;
        }
    });
});


//  Si el formulario traía errores, reabrir el pop up
if (document.querySelector('.mensaje-error') && document.getElementById('titulo').value !== '') {
    fondoPopupCrear.classList.add('popup-visible');
}
