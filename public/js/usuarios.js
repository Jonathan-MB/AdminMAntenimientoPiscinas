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
if (document.querySelector('.contenedor-general > .mensaje-error')) {
    const hayViejo = document.getElementById('nombreUsuario').value !== '';

    if (hayViejo) {
        fondoPopupCrear.classList.add('popup-visible');
    }
}


// ============ POP UP EDITAR ============

const fondoPopupEditar = document.getElementById('fondoPopupEditar');
const botonCerrarEditar = document.getElementById('botonCerrarEditar');
const botonGuardarEditar = document.getElementById('botonGuardarEditar');
const errorEditar = document.getElementById('errorEditar');
const notaEditando = document.getElementById('notaEditando');
const grupoRolesEditar = document.getElementById('grupoRolesEditar');
const grupoHotelEditar = document.getElementById('grupoHotelEditar');
const grupoActivoEditar = document.getElementById('grupoActivoEditar');
const selectorHotelEditar = document.getElementById('editarHotel');
const casillasRolEditar = document.querySelectorAll('.rol-editar');

let editando = null;


function avisarEditar(texto) {
    errorEditar.textContent = texto;
    errorEditar.classList.toggle('error-visible', texto !== '');
}


function alternarHotelEditar() {
    let esHotel = false;

    casillasRolEditar.forEach(function (casilla) {
        if (casilla.checked && Number(casilla.value) === rolHotelId) {
            esHotel = true;
        }
    });

    grupoHotelEditar.style.display = esHotel ? 'flex' : 'none';

    if (! esHotel) {
        selectorHotelEditar.value = '';
    }
}


casillasRolEditar.forEach(function (casilla) {
    casilla.addEventListener('change', alternarHotelEditar);
});


function abrirEditar(boton) {
    editando = {
        id: boton.dataset.id,
        master: boton.dataset.master === '1',
        yo: boton.dataset.yo === '1',
    };

    avisarEditar('');
    notaEditando.textContent = 'Estás editando a ' + boton.dataset.nombre + '.';

    document.getElementById('editarNombre').value = boton.dataset.nombre;
    document.getElementById('editarCorreo').value = boton.dataset.correo;
    document.getElementById('editarPassword').value = '';
    document.getElementById('editarActivo').checked = boton.dataset.activo === '1';
    selectorHotelEditar.value = boton.dataset.hotel || '';

    const suyos = (boton.dataset.roles || '').split(',').filter(Boolean).map(Number);

    casillasRolEditar.forEach(function (casilla) {
        casilla.checked = suyos.indexOf(Number(casilla.value)) !== -1;
        casilla.disabled = false;
        casilla.title = '';

        //  Nadie se quita a si mismo el rol de administrador: se bloquea aqui
        //  para que no llegue a fallar contra el servidor
        if (editando.yo && casilla.dataset.rol === 'administrador') {
            casilla.disabled = true;
            casilla.title = 'No puedes quitarte a ti mismo el rol de administrador';
        }
    });

    //  Al master no se le cambian roles, hotel ni estado; nadie se desactiva solo
    grupoRolesEditar.style.display = editando.master ? 'none' : '';
    grupoActivoEditar.style.display = (editando.master || editando.yo) ? 'none' : '';

    alternarHotelEditar();

    if (editando.master) {
        grupoHotelEditar.style.display = 'none';
    }

    fondoPopupEditar.classList.add('popup-visible');
    document.getElementById('editarNombre').focus();
}


function cerrarEditar() {
    fondoPopupEditar.classList.remove('popup-visible');
    document.getElementById('editarPassword').value = '';
    editando = null;
}


document.querySelectorAll('.boton-editar-usuario').forEach(function (boton) {
    boton.addEventListener('click', function () {
        abrirEditar(boton);
    });
});


botonCerrarEditar.addEventListener('click', cerrarEditar);


fondoPopupEditar.addEventListener('click', function (evento) {
    if (evento.target === fondoPopupEditar) {
        cerrarEditar();
    }
});


botonGuardarEditar.addEventListener('click', async function () {
    if (! editando) {
        return;
    }

    const cuerpo = {
        nombreUsuario: document.getElementById('editarNombre').value.trim(),
        correo: document.getElementById('editarCorreo').value.trim(),
    };

    //  Al master solo se le tocan nombre, correo y contraseña
    if (! editando.master) {
        cuerpo.roles = [];

        //  Una casilla bloqueada sigue informando si esta marcada, asi que el
        //  rol de administrador propio viaja igual y no se pierde
        casillasRolEditar.forEach(function (casilla) {
            if (casilla.checked) {
                cuerpo.roles.push(Number(casilla.value));
            }
        });

        cuerpo.hotelId = selectorHotelEditar.value || null;

        if (! editando.yo) {
            cuerpo.activo = document.getElementById('editarActivo').checked;
        }
    }

    const clave = document.getElementById('editarPassword').value.trim();

    if (clave !== '') {
        cuerpo.password = clave;
    }

    botonGuardarEditar.disabled = true;
    avisarEditar('');

    try {
        const res = await fetch(rutaUsuarios + '/' + editando.id, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(cuerpo)
        });

        const datos = await res.json();

        if (! res.ok) {
            avisarEditar(datos.message || 'No se pudo guardar');
            botonGuardarEditar.disabled = false;
            return;
        }

        location.reload();
    } catch (error) {
        avisarEditar('Error de conexión al guardar');
        botonGuardarEditar.disabled = false;
    }
});
