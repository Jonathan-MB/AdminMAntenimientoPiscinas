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
