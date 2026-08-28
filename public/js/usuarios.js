// ============ POP UP CREAR ============

const fondoPopupCrear = document.getElementById('fondoPopupCrear');
const botonAbrirCrear = document.getElementById('botonAbrirCrear');
const botonCerrarCrear = document.getElementById('botonCerrarCrear');


botonAbrirCrear.addEventListener('click', function () {
    fondoPopupCrear.classList.add('popup-visible');
    document.getElementById('nombreUsuario').focus();
});


botonCerrarCrear.addEventListener('click', function () {
    fondoPopupCrear.classList.remove('popup-visible');
});


//  Cerrar al hacer clic fuera de la tarjeta
fondoPopupCrear.addEventListener('click', function (evento) {
    if (evento.target === fondoPopupCrear) {
        fondoPopupCrear.classList.remove('popup-visible');
    }
});


// ============ ELIMINAR ============

const botonesEliminar = document.querySelectorAll('.boton-eliminar');

botonesEliminar.forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const id = boton.dataset.id;
        const nombre = boton.dataset.nombre;

        if (! confirm('¿Eliminar el usuario ' + nombre + '? Esta acción no se puede deshacer.')) {
            return;
        }

        boton.disabled = true;

        try {
            const res = await fetch(rutaUsuarios + '/' + id, {
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

            //  Quitar la fila sin recargar
            const fila = document.querySelector('tr[data-usuario="' + id + '"]');

            if (fila) {
                fila.remove();
            }
        } catch (error) {
            alert('Error de conexión al eliminar');
            boton.disabled = false;
        }
    });
});


// ============ EL HOTEL SOLO APLICA AL ROL HOTEL ============

const casillasRol = document.querySelectorAll('input[name="roles[]"]');
const grupoHotel = document.getElementById('grupoHotel');
const selectorHotel = document.getElementById('hotelId');


function alternarHotel() {
    //  El hotel solo hace falta si entre los roles marcados esta el de hotel
    let esHotel = false;

    casillasRol.forEach(function (casilla) {
        if (casilla.checked && Number(casilla.value) === rolHotelId) {
            esHotel = true;
        }
    });

    grupoHotel.style.display = esHotel ? 'flex' : 'none';
    selectorHotel.required = esHotel;

    if (! esHotel) {
        selectorHotel.value = '';
    }
}


casillasRol.forEach(function (casilla) {
    casilla.addEventListener('change', alternarHotel);
});

alternarHotel();


//  Si el formulario de creación traía errores, abrir el pop up de una vez
if (document.querySelector('.mensaje-error')) {
    const hayViejo = document.getElementById('nombreUsuario').value !== '';

    if (hayViejo) {
        fondoPopupCrear.classList.add('popup-visible');
    }
}
