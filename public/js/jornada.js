function cabeceras() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    };
}


// ============ LECTURA DEL METRO DE AGUA ============

const botonGuardarMetro = document.getElementById('botonGuardarMetro');

if (botonGuardarMetro) {
    botonGuardarMetro.addEventListener('click', async function () {
        botonGuardarMetro.disabled = true;

        try {
            const res = await fetch(rutaJornada, {
                method: 'PATCH',
                headers: cabeceras(),
                body: JSON.stringify({
                    lecturaMetroAgua: document.getElementById('lecturaMetroAgua').value
                })
            });

            const datos = await res.json();
            alert(datos.message);
        } catch (error) {
            alert('Error de conexión al guardar');
        }

        botonGuardarMetro.disabled = false;
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
