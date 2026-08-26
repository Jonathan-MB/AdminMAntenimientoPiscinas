// ============ POP UP CREAR ============

const fondoPopupCrear = document.getElementById('fondoPopupCrear');
const botonAbrirCrear = document.getElementById('botonAbrirCrear');
const botonCerrarCrear = document.getElementById('botonCerrarCrear');


botonAbrirCrear.addEventListener('click', function () {
    fondoPopupCrear.classList.add('popup-visible');
    document.getElementById('nombre').focus();
});


botonCerrarCrear.addEventListener('click', function () {
    fondoPopupCrear.classList.remove('popup-visible');
});


fondoPopupCrear.addEventListener('click', function (evento) {
    if (evento.target === fondoPopupCrear) {
        fondoPopupCrear.classList.remove('popup-visible');
    }
});


// ============ ELIMINAR HOTEL ============

const botonesEliminar = document.querySelectorAll('.boton-eliminar');

botonesEliminar.forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const id = boton.dataset.id;
        const nombre = boton.dataset.nombre;

        if (! confirm('¿Eliminar el hotel ' + nombre + '?')) {
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaHoteles + '/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            const datos = await res.json();

            if (! res.ok) {
                alert(datos.message || 'No se pudo eliminar');
                boton.disabled = false;
                return;
            }

            const fila = document.querySelector('tr[data-hotel="' + id + '"]');

            if (fila) {
                fila.remove();
            }
        } catch (error) {
            alert('Error de conexión al eliminar');
            boton.disabled = false;
        }
    });
});


//  Si el formulario traía errores, reabrir el pop up
if (document.querySelector('.mensaje-error') && document.getElementById('nombre').value !== '') {
    fondoPopupCrear.classList.add('popup-visible');
}
