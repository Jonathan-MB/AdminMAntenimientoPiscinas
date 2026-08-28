// ============ GUARDADO AUTOMATICO ============
//  Cada campo se guarda al salir de el. No hay boton de guardar: el de
//  abajo solo sale de la piscina, porque lo escrito ya esta en la base.

const formulario = document.getElementById('formularioMedicion');
const estado = document.getElementById('estadoGuardado');

//  Sin formulario o sin indicador, la jornada esta cerrada y no hay nada que guardar
if (formulario && estado) {

    let guardando = false;
    let pendiente = false;


    function mostrar(texto, clase) {
        estado.textContent = texto;
        estado.className = 'estado-guardado ' + (clase || '');
    }


    async function guardar() {
        //  Si ya hay un guardado en curso, se encola uno solo al final
        if (guardando) {
            pendiente = true;
            return;
        }

        guardando = true;
        mostrar('Guardando…', 'estado-trabajando');

        try {
            const res = await fetch(formulario.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: new FormData(formulario)
            });

            const datos = await res.json();

            if (res.ok) {
                mostrar('Guardado ' + datos.hora, 'estado-hecho');
            } else if (res.status === 422 && datos.errors) {
                //  Un valor invalido: se dice cual, sin sacar al tecnico de la pantalla
                const primero = Object.values(datos.errors)[0][0];
                mostrar(primero, 'estado-fallo');
            } else {
                mostrar(datos.message || 'No se pudo guardar', 'estado-fallo');
            }
        } catch (error) {
            mostrar('Sin conexión. Lo escrito no se ha guardado.', 'estado-fallo');
        }

        guardando = false;

        if (pendiente) {
            pendiente = false;
            guardar();
        }
    }


    //  Cambiar un valor que ya estaba guardado se pregunta. Llenar un campo
    //  que llego vacio, no: eso es primera captura y preguntar molestaria.
    function confirmarCambio(campo) {
        const original = campo.dataset.original;

        if (original === undefined || original === '') {
            return true;
        }

        if (String(campo.value) === String(original)) {
            return true;
        }

        const etiqueta = campo.dataset.etiqueta || 'este valor';
        const nuevo = campo.value === '' ? '(vacío)' : campo.value;

        return confirm(
            '¿Seguro que quieres cambiar ' + etiqueta + '?\n\n' +
            'Estaba en ' + original + ' y quedaría en ' + nuevo + '.' + '\n' +
            'El cambio queda registrado y el administrador lo verá.'
        );
    }


    //  change salta al salir del campo en los de texto y numero, y de
    //  inmediato en los select y las casillas
    formulario.querySelectorAll('input, select, textarea').forEach(function (campo) {
        if (campo.type === 'hidden' || campo.disabled) {
            return;
        }

        campo.addEventListener('change', function () {
            if (! confirmarCambio(campo)) {
                //  Se deja como estaba y no se guarda
                campo.value = campo.dataset.original;
                return;
            }

            //  A partir de aqui, este es el valor guardado
            if (campo.dataset.original !== undefined) {
                campo.dataset.original = campo.value;
            }

            guardar();
        });
    });


    //  Si alguien pulsa Enter, no se recarga la pagina: ya se esta guardando
    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();
        guardar();
    });
}
