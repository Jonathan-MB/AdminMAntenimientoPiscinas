//  Comportamientos que se repiten en varias pantallas. Por ahora, uno solo:
//  poder ver la contraseña mientras se escribe.

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('input[type="password"].campo-formulario').forEach((campo) => {
        const envoltorio = document.createElement('div');
        envoltorio.className = 'campo-password';
        campo.parentNode.insertBefore(envoltorio, campo);
        envoltorio.appendChild(campo);

        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'boton-ver-password';
        boton.textContent = 'Ver';
        boton.setAttribute('aria-label', 'Mostrar la contraseña');
        envoltorio.appendChild(boton);

        boton.addEventListener('click', () => {
            const oculta = campo.type === 'password';
            campo.type = oculta ? 'text' : 'password';
            boton.textContent = oculta ? 'Ocultar' : 'Ver';
            boton.setAttribute('aria-label', oculta ? 'Ocultar la contraseña' : 'Mostrar la contraseña');
        });
    });

});
