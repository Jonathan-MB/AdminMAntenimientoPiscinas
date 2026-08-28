function cabeceras() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    };
}


// ============ LA TARJETA DE LA JORNADA ============

const botonGuardarJornada = document.getElementById('botonGuardarJornada');

if (botonGuardarJornada) {
    botonGuardarJornada.addEventListener('click', async function () {
        //  Una lectura por metro, indexada por el id del metro
        const lecturas = {};

        document.querySelectorAll('[data-metro]').forEach(function (campo) {
            lecturas[campo.dataset.metro] = campo.value;
        });

        botonGuardarJornada.disabled = true;

        try {
            const res = await fetch(rutaJornada, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify({
                    materialesSacados: document.getElementById('materialesSacados').value,
                    lecturas: lecturas
                })
            });

            const datos = await res.json();
            alert(datos.message);
        } catch (error) {
            alert('Error de conexión al guardar');
        }

        botonGuardarJornada.disabled = false;
    });
}


// ============ LISTADO DE TRABAJO ============

document.querySelectorAll('.casilla-tarea').forEach(function (casilla) {
    if (casilla.disabled) {
        return;
    }

    casilla.addEventListener('change', async function () {
        const marcada = casilla.checked;
        casilla.disabled = true;

        try {
            const res = await fetch(rutaJornada + '/tarea/' + casilla.dataset.tarea, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify({ hecha: marcada })
            });

            if (! res.ok) {
                const datos = await res.json();
                alert(datos.message || 'No se pudo guardar la tarea');
                //  Dejar la casilla como estaba
                casilla.checked = ! marcada;
            }
        } catch (error) {
            alert('Error de conexión');
            casilla.checked = ! marcada;
        }

        casilla.disabled = false;
    });
});
