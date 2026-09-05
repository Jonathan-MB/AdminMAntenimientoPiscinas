# Control de Mantenimiento de Piscinas — AQUALIVE

Sistema interno de **AQUALIVE · Pool Technology** para el control del mantenimiento de piscinas
en cadenas hoteleras.

El aplicativo lo usan tanto el personal técnico como los hoteles clientes, así que la interfaz
está pensada desde el primer día para **PC y móvil**, y con un tono **sobrio e institucional**:
los hoteles ven esta pantalla.

---

## Estado actual

| Módulo | Estado |
|---|---|
| Acceso (login / logout) | Funcionando |
| Roles y permisos | Funcionando |
| Administración de usuarios (crear, editar, contraseñas) | Funcionando |
| Modelo de datos de la operación | Funcionando |
| Hoteles y piscinas (pantallas) | Funcionando |
| Diario del hotel con calendario | Funcionando |
| Registro de la jornada (colaborador) | Funcionando |
| Perfil propio y cambio de contraseña | Funcionando |
| Filtros del panel (hotel, empleado, fechas) | Funcionando |
| Ver como otro usuario (soporte) | Funcionando |
| Roles de jefe y reparación | Funcionando |
| Reparaciones: tickets, estados e historial | Funcionando |
| Reparaciones: fotos del ticket | Funcionando |
| Reparaciones: contadores y aviso en vivo | Funcionando |
| Impresión de la revisión de un día | Funcionando |
| Paginación del panel | Funcionando |
| Respaldo diario de la base y las fotos | Funcionando |
| Nivel de sal y sal aplicada | Funcionando |
| Observación del ticket editable, con su rastro | Funcionando |
| Cierre por visita y por garantía, además del cobro | Funcionando |

**En pruebas con el equipo** en [aqualiveapp.com](https://aqualiveapp.com) desde el 2 de septiembre
de 2026, con `APP_ENV=staging` y los datos del `DemoSeeder`. Ya hay trabajo capturado a mano ahí,
así que esa base **se conserva**: ver «Antes de producción» al final.

---

## Documentos

- **[docs/DESPLIEGUE.md](docs/DESPLIEGUE.md)** — el procedimiento para subirlo a Hostinger, paso a
  paso, y la versión corta para actualizar.
- **[docs/PRUEBAS.md](docs/PRUEBAS.md)** — qué se probó, con qué resultado, y los errores de
  método en los que es fácil caer al repetirlo.
- **[docs/CONVENCIONES.md](docs/CONVENCIONES.md)** — cómo está escrito el código y por qué.

---

## Requisitos

- **PHP 8.2** o superior
- **Composer 2**
- **MySQL / MariaDB**
- **No requiere Node ni npm.** El proyecto no usa Vite, Tailwind ni ningún empaquetador:
  el CSS y el JS son planos y se sirven directo desde `public/`.

---

## Instalación

```bash
git clone https://github.com/Jonathan-MB/AdminMantenimientoPiscinas.git
cd AdminMantenimientoPiscinas
composer install
cp .env.example .env
php artisan key:generate
```

Crea la base de datos con el juego de caracteres correcto (importante para las tildes):

```sql
CREATE DATABASE control_mantenimiento_piscinas
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ajusta las credenciales en `.env` y siembra:

```bash
php artisan migrate --seed
```

Los seeders crean los cuatro roles, el usuario **master**, los 9 productos químicos, el
listado de trabajo diario y el hotel de ejemplo con sus piscinas.

### La contraseña del master

Nunca está escrita en el código. Sale de `MASTER_PASSWORD` en el `.env`.
Si la dejas vacía, el seeder genera una al azar y **la imprime una sola vez** en la consola.
Anótala en ese momento.

Los seeders usan `firstOrCreate`, así que se pueden correr dos veces sin duplicar nada.

---

## Roles y permisos

El login es con **nombre de usuario y contraseña** (no con correo).
El correo se pide al crear el usuario, pero no sirve para entrar.

**Un usuario puede tener varios roles**, guardados en la tabla `rol_usuario`. La excepción es
`master`, que es exclusivo: quien lo tiene no lleva ningún otro, y no se asigna desde la
pantalla.

| Rol | Qué puede hacer |
|---|---|
| **master** | Todo. Es el único que puede eliminar administradores. **No lo puede eliminar nadie.** Es un rol **exclusivo**. |
| **administrador** | Crear usuarios y asignar roles. **No puede eliminar a otro administrador.** |
| **colaborador** | Ingresar la información de mantenimiento. |
| **hotel** | Ver su información y la de sus piscinas. Solo lectura. |
| **jefe** | Ve las reparaciones, crea tickets y es el único que puede borrarlos. |
| **reparacion** | Ve las reparaciones y crea tickets. |

El `master` ve todo, incluida la sección de reparaciones. Como es un rol **exclusivo**, no puede
ser además `jefe`: si se le excluyera de ese módulo, quedaría fuera de su propio sistema.

**Los nombres de rol son identificadores, no textos de pantalla**, así que van sin tildes:
`reparacion`, no «reparación». Los títulos y botones que sí lee una persona las llevan.

### Los permisos se suman, las protecciones no

Dos reglas que se leen distinto cuando alguien tiene varios roles:

- **Los permisos se suman.** Quien es `colaborador` y `administrador` puede hacer lo de ambos:
  entra al registro y también a usuarios y hoteles.
- **Las protecciones no se suman: gana la más fuerte.** A ese mismo usuario **solo lo puede
  eliminar el master**, porque entre sus roles está `administrador`. Lo contrario dejaría un
  hueco: bastaría añadirle un rol menor a un administrador para poder borrarlo.

Por lo mismo, **al entrar manda el rol más amplio**: quien es colaborador y además administra
aterriza en el panel, no en la pantalla de registro.

### Editar un usuario y cambiarle la contraseña

Desde **Usuarios**, el botón **Editar** de cada fila abre los datos de esa persona: nombre,
correo, roles, hotel, si está activa, y un campo de **contraseña nueva**.

El campo de contraseña **se deja en blanco para no tocarla**. Escribir una la reemplaza **sin
pedir la anterior**: es la salida para cuando alguien la olvida. Se muestra en claro mientras se
escribe, a propósito, para que quien la fija pueda dictarla por teléfono.

Esa contraseña es **provisional**. Ver «La contraseña que pone un administrador dura un ingreso».

El botón solo aparece donde la acción es posible, con las mismas reglas que aplica el servidor:
al `master` solo lo edita el `master`; a un `administrador`, solo el `master` o él mismo.

### La contraseña que pone un administrador dura un ingreso

Si un administrador le fija la contraseña a **otra persona**, esa clave queda marcada como
provisional (`usuarios.debe_cambiar_password`). Sirve para volver a entrar y para nada más: en
cuanto entra, la aplicación la lleva a **`/elegir-contrasena`** y no la deja ir a ninguna otra
pantalla hasta que elija una suya.

El motivo es simple: **una contraseña que otra persona conoce no es una contraseña**. Quien la
dictó por teléfono la sabe, y probablemente quedó escrita en algún lado.

La marca se pone en dos casos y se quita en uno:

| Qué pasa | Marca |
|---|---|
| Se crea un usuario (la clave inicial la elige quien lo crea) | Se pone |
| Un administrador le cambia la clave a otra persona | Se pone |
| Alguien se cambia **su propia** contraseña, desde el perfil o desde la pantalla de usuarios | Se quita |

`debe_cambiar_password` **no está en `$fillable`** a propósito: lo pone el código, nunca una
petición.

La pantalla **no pide la contraseña actual**: acaba de escribirla para entrar, y justamente el
problema es que se la dio otra persona. Sí comprueba que la nueva **no sea la misma** que le
dieron, comparando contra el hash guardado. Al guardar se **renueva la sesión**, porque la
anterior la conocía alguien más.

El middleware `ExigirCambioPassword` deja pasar cuatro rutas, o el usuario se queda encerrado: la
propia pantalla, su envío, cerrar sesión y volver de una suplantación. Y **no se aplica mientras
el master está viendo la aplicación como otro usuario**: la marca es de la persona suplantada, no
de quien mira. A una petición JSON le responde **403** en vez de redirigirla, para que un fetch no
reciba una pantalla de HTML donde esperaba datos.

### Queda registrado quién le cambió la contraseña a quién

`cambios_password` guarda una línea cada vez que una contraseña cambia: **a quién**, **quién lo
hizo** y **cuándo**. La contraseña no, claro; solo el hecho de que cambió. Se anota en los cuatro
caminos que existen: al crear un usuario, al cambiársela a otro desde la pantalla de usuarios, al
cambiar la propia desde el perfil, y al elegirla en la pantalla obligatoria.

La pantalla de usuarios lo muestra en la columna **Contraseña**:

| Lo que se ve | Qué significa |
|---|---|
| *La eligió colab1* · fecha | Se la puso él mismo. En gris: no hay nada que mirar |
| *Se la puso admin1* · fecha | Otra persona conoce esa clave. En ámbar |
| Pastilla **Provisional** | Todavía no ha elegido una suya |
| *Sin registro* | Cambió antes de que existiera este registro, o nunca |

Las dos claves foráneas van a propósito contra la costumbre del proyecto, que es
`restrictOnDelete()`:

- `usuario_id` en **cascada**: si se borra la cuenta, su historial se va con ella. Sin cuenta no
  significa nada.
- `autor_id` **a nulo**: si se borra quien hizo el cambio, la línea se queda y la pantalla dice
  «un usuario eliminado». El hecho importa aunque el autor ya no esté. Con `restrictOnDelete()`
  además **no se podría borrar** a ningún administrador que hubiera reseteado una contraseña.

### La escalada de privilegios que había que cerrar antes

`UsuarioController::update` protegía al master **solo en sus roles y en su estado**:

```php
if ($usuario->esMaster() && ($roles !== null || isset($data['activo']))) { ... 403 }
```

Un `administrador` que mandara `PATCH /usuarios/1 {"password":"..."}` **pasaba de largo** y se
quedaba con la cuenta del master. Estaba así desde que se escribió el método; nadie lo había
tocado porque no había botón, y este trabajo consistía justo en poner ese botón.

Comprobado antes de arreglarlo, con un valor idéntico al que ya tenía —para no cambiar nada—:
la respuesta fue `422 No se detectaron cambios`, o sea que la petición **había llegado hasta el
final** de las protecciones.

Ahora la regla es entera: **al master no lo modifica nadie más que el master**, en ningún campo.
Se añadieron además dos que faltaban:

- Nadie **se desactiva a sí mismo**.
- Nadie **se quita a sí mismo el rol de administrador**. En la pantalla esa casilla aparece
  bloqueada, así que la petición ni se intenta.

### El hotel se mantiene coherente al editar

`update` no aceptaba `hotel_id`, así que al cambiar los roles el hotel quedaba como estuviera.
Ahora sigue la misma regla que al crear: si entre los roles está `hotel` hace falta un hotel
asignado —si no, **422**—, y si se le quita ese rol, el hotel **se pone en nulo**.

### Reglas de eliminación

Están en `UsuarioController::destroy` y se aplican en este orden:

1. Al `master` no lo elimina nadie, nunca.
2. Nadie se elimina a sí mismo.
3. Si entre sus roles está `administrador`, solo lo elimina el `master`.
4. Al resto lo puede eliminar `master` o `administrador`.

### El rol master no se asigna desde la pantalla

Ni siquiera el propio master puede asignarlo. Solo existe por seeder.
Es lo que garantiza que no aparezcan cuentas master nuevas por accidente: si un administrador
pudiera asignarlo, se saltaría toda la jerarquía.

### La autorización compara por nombre, no por id

`App\Models\Rol` expone constantes (`Rol::MASTER`, `Rol::ADMINISTRADOR`, …) y todas las
comprobaciones usan el **nombre** del rol. Los ids dependen del orden de inserción y no son
estables entre entornos.


### Nada se elimina si tiene historial

Las llaves foráneas son `restrictOnDelete`, así que los controladores comprueban antes y
devuelven un **409** con un mensaje claro en vez de dejar que reviente la base:

- Un **hotel** con piscinas, jornadas o usuarios no se elimina. Se desactiva.
- Una **piscina** con mediciones no se elimina. Se desactiva.

### El rol hotel va atado a su hotel

Un usuario con rol `hotel` **exige** un hotel asignado. A cualquier otro rol se le ignora ese
campo aunque venga en la petición.

---

## Estructura

```
app/
  Http/Controllers/    AccesoController, PanelController, UsuarioController,
                       HotelController, PiscinaController, MetroAguaController,
                       RondaProgramadaController, DiarioController,
                       RegistroController, MedicionController, CambioController,
                       SuplantacionController, PerfilController,
                       TicketController, FotoTicketController,
                       PasswordTemporalController
  Http/Middleware/     VerificarRol           (alias 'rol')
                       ExigirCambioPassword   (alias 'password.temporal')
  Http/Requests/       IniciarSesion, StoreUsuario, UpdateUsuario,
                       StoreHotel, UpdateHotel, StorePiscina, UpdatePiscina,
                       StoreRondaProgramada, UpdateRondaProgramada,
                       StoreMetroAgua, UpdateMetroAgua,
                       AbrirJornada, UpdateJornada, StoreMedicion,
                       UpdatePerfil, CambiarPassword,
                       StoreTicket, MoverTicket, StoreFotoTicket,
                       CambiarPasswordTemporal
  Models/              Rol, Usuario, Hotel, Piscina, RondaProgramada,
                       MetroAgua, LecturaMetro, Producto, Jornada, Ronda,
                       Medicion, Dosis, Tarea, TareaRealizada, Cambio,
                       Ticket, MovimientoTicket, FotoTicket, CambioPassword
database/
  migrations/          sessions, cache, jobs, roles, usuarios, rol_usuario,
                       hoteles, piscinas, rondas_programadas, metros_agua,
                       productos, tareas, jornadas, lecturas_metro, rondas,
                       mediciones, dosis, tareas_realizadas, cambios,
                       tickets, movimientos_ticket, fotos_ticket,
                       cambios_password,
                       + debe_cambiar_password en usuarios
  seeders/             RolSeeder, UsuarioMasterSeeder, ProductoSeeder,
                       TareaSeeder, HotelSeeder,
                       DemoSeeder (solo desarrollo, no se llama solo)
public/
  css/                 general.css + una hoja por vista
  js/                  un archivo por vista
  img/                 logo, isotipo y derivados
resources/views/
  partials/            head, head-impresion, header, header-limpio, mensaje,
                       footer, aviso-reparaciones, paginacion
  login, panel, usuarios, hoteles, hotel, diario,
  registro, jornada, medicion, cambios, perfil,
  reparaciones, ticket, historial-reparaciones, impresion-dia,
  password-temporal
Libro de marca/        Manual de marca, logos y paleta (copia para uso externo)
docs/                  Convenciones de código
```

Las vistas **no usan `@extends` ni `@section`**: se arman incluyendo fragmentos en orden.
Ver [docs/CONVENCIONES.md](docs/CONVENCIONES.md).

---

## Base de datos

Tablas y columnas en **español**, en `snake_case`.

Dos columnas quedan en inglés porque **las impone Laravel** y renombrarlas rompe el framework:

- `usuarios.password` — `Auth::attempt()` trata esa clave como especial y la compara con el hash.
- `sessions.user_id` — la escribe `DatabaseSessionHandler` con ese nombre exacto.

---

## Marca

La paleta sale del logo oficial, decodificada del archivo vectorial y convertida con el perfil
ICC que el propio archivo trae dentro (Coated FOGRA27). No son valores estimados a ojo.

| Color | Hex | CMYK |
|---|---|---|
| Azul corporativo | `#00519e` | C100 M70 Y0 K0 |
| Negro marca | `#000000` | K100 |
| Gris corporativo | `#5f6062` | K77 |
| Azul agua | `#91d4f2` | C46 |
| Azul agua claro | `#c9e9f8` | C25 |
| Índigo profundo | `#332b67` | C92 M97 Y25 K10 |

`#91d4f2` y `#c9e9f8` **nunca llevan texto encima**: dan 1.6:1 y 1.2:1 de contraste sobre blanco.

Proporción esperada por pantalla: ~70 % neutros, ~20 % azul corporativo, el resto gris y un
acento mínimo de azul agua.

### El fondo de la aplicación

`#b1d1fc`, elegido por AQUALIVE el 29 de agosto de 2026. Es más saturado que el gris azulado con
el que arrancó el proyecto, y hace que las tarjetas blancas destaquen más.

Tiene una consecuencia medida: el gris de marca `#5F6062` sobre ese azul da **4,0 : 1**, por
debajo del mínimo legible de 4,5. Dentro de las tarjetas blancas no pasa nada, pero el texto
pequeño que va **directamente sobre el fondo** —el conteo de resultados, las notas sueltas, los
enlaces de volver, la hora de la ronda— usa `#4A4B4D`, que da **5,6 : 1**. Es el mismo gris un
tono más oscuro, no un color nuevo.

Si algún día se cambia el fondo, hay que volver a medir eso: el criterio es que **ningún texto
sobre el fondo baje de 4,5 : 1**. Se comprobó pantalla por pantalla con las reglas aplicadas, no
a ojo.

El manual completo está en `Libro de marca/manual-de-marca-aqualive.html` (se abre con doble clic).

### El paquete que se le entrega al cliente

`scripts/armar-libro-de-marca.php` arma un ZIP con el manual **en PDF** y los logos sueltos:

```bash
php -d extension=zip scripts/armar-libro-de-marca.php
```

Del manual quita dos cosas que solo le sirven a quien programa —la sección «Código» y la tabla de
archivos con sus rutas de `public/`— y fuerza el tema claro, porque el manual se pone oscuro si el
sistema lo pide y un PDF oscuro se imprime pésimo. El PDF lo genera Chrome o Edge en headless.

Es un script y no un ZIP guardado a mano para que haya **una sola fuente**: el manual del
repositorio. El ZIP que sale está en `.gitignore`.


### La errata del descriptor: corregida en los PNG, pendiente en el `.ai`

El archivo `LOGO FINAL VECTORIAL vertical.ai` dice **«POLL TECHNOLOGY»**, no «POOL».
En esa tipografía la `O` mide 25,66 unidades con dos subtrazos y la `L` mide 15,66 con uno;
los caracteres 3 y 4 de la primera palabra son byte a byte idénticos entre sí. La versión
horizontal del logo sí dice POOL, así que la equivocada es esta.

**Los PNG ya están corregidos.** No se retocaron a mano ni se volvió a componer el texto: se
sacaron los trazos vectoriales del propio archivo maestro, se sustituyó la `L` sobrante por una
**copia exacta de la `O`** que ya estaba en la palabra, y se rasterizó de nuevo esa franja.

La separación entre las dos `O` no se inventó, se dedujo del propio logo. Si `h(A→B)` es el
hueco de tinta entre dos letras:

```
h(O→O) = h(O→L) - h(L→L) + h(L→O) = 10,0832 - 9,4156 + 6,1681 = 6,8357
```

Las tres separaciones de la derecha existen en «POLL TECHNOLOGY», así que el resultado usa el
espaciado del propio tipógrafo. «TECHNOLOGY» conserva su espaciado exacto: solo se desplaza.
El descriptor queda 7,42 unidades más ancho —la `O` es más ancha que la `L`— y se recentra
sobre el mismo eje, de modo que el resto del logo no se mueve ni un píxel.

Comprobado así: se rasterizó el descriptor **equivocado** con el mismo código y se comparó con
el PNG original. La diferencia media fue de **1,08 sobre 255** (0,4 %), o sea que el rasterizador
reproduce el archivo maestro; solo entonces se rasterizó el corregido. Fuera de las filas del
descriptor, los PNG nuevos son **idénticos** a los viejos.

`logo-800`, `logo-400` y `logo-blanco-800` se regeneran reduciendo el de 1600 px, que es como
se habían hecho los originales (comprobado: coincidían con una reducción por cajas dentro de
0,4 sobre 255).

**El `.ai` sigue con la errata, y no es nuestro.** Es el archivo del diseñador que hizo el logo;
no está en el repositorio. Quien lo abra verá POLL, así que si algún día hay que exportar de ahí
—una valla, una camioneta, camisetas—, primero hay que corregirlo con
`Libro de marca/logos/descriptor-pool-technology.svg`, que es el descriptor en curvas listo para
reemplazar esa línea.

**Nada del proyecto depende de él.** Los PNG del libro de marca y los de la aplicación están
corregidos y son los que se usan.

Se intentó reconstruir un vectorial completo desde el `.ai` el 5 de septiembre de 2026 y **se
descartó**: la «A» de dentro no es vectorial, es un mapa de bits de 2673 × 2717 px, y la
herramienta disponible lo rasteriza mal —le pierde el contorno azul oscuro y deja una rejilla de
costuras—. El resultado habría sido peor que los PNG que ya existen. El nombre del archivo dice
«VECTORIAL», pero solo el logotipo y el descriptor lo son.

---

## Responsive

Dos cortes, y solo dos, definidos en `general.css`:

- `768px` — tablet
- `480px` — móvil

En móvil las tablas se apilan en tarjetas usando el atributo `data-titulo` de cada
celda, en vez de desplazarse horizontalmente.

---

## Despliegue

**El procedimiento completo y en orden está en [docs/DESPLIEGUE.md](docs/DESPLIEGUE.md).** Lo de
aquí abajo son los cuidados, no los pasos.

En producción (Hostinger, Linux) el contenido de `public/` va copiado en `public_html/`, y el
resto del proyecto queda al lado, fuera del docroot.

Cuidados:

- **Nunca subas `bootstrap/cache/*.php`.** `config:cache` y `route:cache` incrustan rutas
  absolutas; si se generan en Windows, la aplicación se cae en el servidor. Genera las cachés
  por SSH después de cada despliegue.
- **Nunca subas el `.env`.** Está en `.gitignore` y ahí se queda.
- **Todos los nombres de archivo en `public/` van en minúscula.** Windows perdona las
  mayúsculas; Linux no.
- **`storage/` tiene que ser escribible.** Ahí van las fotos de los tickets. No hace falta
  `php artisan storage:link`: las fotos se sirven por una ruta, no desde `public/`.
- **Revisa `upload_max_filesize` y `post_max_size` en el servidor.** Seis fotos de 5 MB son
  30 MB en una sola petición. Si `post_max_size` es menor, PHP descarta el formulario entero
  antes de que Laravel lo vea y el usuario recibe un error de sesión caducada, no uno de
  tamaño. En desarrollo ambos están en 40 MB.
- Las migraciones se corren en el servidor.

---

## A dónde entra cada rol

Al iniciar sesión nadie aterriza en una pantalla con sus propios datos: cada rol entra directo
a lo que va a hacer.

| Rol | Aterriza en |
|---|---|
| `colaborador` | `/registro` — elegir hotel y fecha, con sus jornadas recientes debajo |
| `hotel` | `/diario/{su hotel}` — el calendario de sus piscinas |
| `jefe` y `reparacion` | `/reparaciones` — el tablero de tickets abiertos |
| `master` y `administrador` | `/panel` — las últimas 12 jornadas de todos los hoteles, con su avance |

`PanelController` redirige según el rol. Un usuario `hotel` sin hotel asignado sí ve el panel,
pero con un aviso de que pida su asignación.

Con varios roles manda el más amplio. El orden es a propósito: `colaborador` va **antes** que
`jefe` y `reparacion`, porque quien además captura jornadas entra a capturarlas —es lo que hace
todos los días y a una hora concreta—, y llega a las reparaciones con un clic en la barra.


### Filtros del panel

El panel de jornadas filtra por **hotel**, por **empleado** y por **rango de fechas**, y los
cuatro se combinan entre sí. El filtro va por GET, así que la URL se puede compartir o guardar:
`/panel?hotel=1&empleado=3&desde=2026-08-01`.

La lista de empleados solo muestra a quienes **de verdad han registrado alguna jornada**, no a
todos los usuarios.

### Paginación

El panel pagina de **30 en 30**. Los enlaces de página llevan `withQueryString()`, así que
**se van con los filtros puestos**: sin eso, pasar a la página 2 con un empleado filtrado
devolvería las jornadas de todos, que es la forma clásica de que una paginación mienta.

`partials/paginacion` dibuja los enlaces a mano en vez de usar `->links()` de Laravel, porque las
vistas que trae el framework están escritas para Tailwind o Bootstrap y aquí el CSS es plano.
Muestra la primera página, la última, y una ventana de dos a cada lado de la actual.

Pedir una página que ya no existe —se llega volviendo atrás después de acotar el filtro— no deja
la pantalla muda: dice **«Esa página ya no tiene resultados»** con un enlace a la primera que
conserva el filtro.

En el teléfono los números pasan a su propia fila arriba y «Anterior» y «Siguiente» ocupan media
pantalla cada uno, con 44 px de alto.

El conteo dice cuántas hay en total y cuáles se están viendo: «70 jornadas. Viendo de la 31 a la
60, página 2 de 3».

**Ojo con lo que significa «empleado»:** filtra por `jornadas.usuario_id`, que es **quien abrió
la jornada**, no quien tomó cada medición. Si dos colaboradores se reparten el mismo día, todo
queda atribuido al que la abrió. Si eso llega a importar, habría que guardar el autor en cada
medición.

---

## Mi perfil

El nombre de usuario en la barra superior es un enlace a `/perfil`. Ahí cualquier usuario ve
sus datos y puede:

- **Cambiar su correo** — validado como único entre todos los usuarios.
- **Cambiar su contraseña** — pide la actual, la nueva dos veces, mínimo 8 caracteres y que sea
  distinta de la actual. Al cambiarla se **regenera la sesión**, por si alguien más la conocía.

El **nombre de usuario y el rol no se cambian desde ahí**: son identidad, y los administra un
administrador.

### Ver la contraseña mientras se escribe

Todo campo de contraseña de la aplicación lleva al lado un botón **Ver / Ocultar**. Escribir a
ciegas una clave de 8 caracteres en el teclado de un teléfono, con el sol encima y las manos
mojadas, es la forma más rápida de quedarse fuera por una letra.

No está puesto pantalla por pantalla: `public/js/general.js` se carga en el pie de todas y le
pone el botón a cada `input[type="password"]`. Se aplica solo a las cuatro que hoy piden una
—ingreso, perfil, alta de usuario y contraseña provisional— y a cualquiera que se agregue después.

---

## Cuando algo falla, la aplicación vuelve al panel

No hay pantallas de error. Un **419** (la sesión o el token vencieron), un **404**, un **405** o
un **500** redirigen al panel con un aviso, en vez de dejar al usuario frente a una página en
blanco de la que solo se sale escribiendo la dirección a mano.

Si además ya no hay sesión, el panel lo manda al login por su cuenta: no hace falta distinguir
los dos casos, la cadena se resuelve sola.

El caso que lo destapó fue **cerrar sesión con el token vencido**: el botón «Salir» devolvía un
419 y una pantalla en blanco, justo al usuario que estaba intentando irse bien.

Está en `bootstrap/app.php`, en `withExceptions`. **Los 401 y 403 se dejan pasar tal cual**: esos
no son fallos, son la aplicación negando el acceso a propósito, y convertirlos en redirección
escondería un permiso mal puesto. Las peticiones que esperan JSON tampoco se tocan, o el
JavaScript recibiría una redirección donde espera un código.

---

## Los archivos de `public/` llevan versión

`@recurso('css/panel.css')` devuelve la ruta con la fecha de modificación del archivo detrás
(`?v=1787872758`). La directiva está en `AppServiceProvider`.

Sin esto el navegador sigue sirviendo la hoja vieja después de cada cambio, y uno cree que el
CSS no funciona cuando en realidad ni se descargó.

**Las imágenes también.** Al corregir el descriptor del logo, el servidor ya entregaba el PNG
nuevo y la pantalla seguía mostrando el viejo: el navegador lo tenía guardado. El logo, el
isotipo de la barra y el icono de iOS pasan por `@recurso` por el mismo motivo. Es más
importante en las imágenes que en el CSS, porque un logo se cambia una vez cada mucho tiempo y
para entonces todo el mundo lo tiene cacheado.

---

## Ver como otro usuario

El `master` puede entrar a la aplicación como cualquier otro usuario, para ver exactamente lo
que ese usuario ve. Sirve para soporte: cuando un hotel reporta un problema, se mira su
pantalla sin pedirle la contraseña.

En **Usuarios**, cada fila trae el botón **Ver como**. Mientras dura, arriba queda una franja
naranja permanente con el nombre suplantado y el botón **Volver a mi cuenta**.

### Las reglas

- **Solo el `master`** puede iniciarla. Un `administrador` no ve el botón, y un POST directo a
  la ruta le responde `403`.
- **No se puede encadenar**: estando dentro de una suplantación no se puede empezar otra, o se
  perdería el rastro del master original.
- **No se puede suplantar a un usuario inactivo**, ni a uno mismo.
- El id del master real se guarda en la **sesión**. Volver no exige ser master: exige tener esa
  marca. Un usuario cualquiera que llame a `/volver-a-mi-cuenta` sin la marca simplemente
  regresa a su panel, **no escala privilegios**.
- Si la cuenta original quedó inactiva o dejó de ser master mientras tanto, se cierra la sesión
  por completo en vez de devolver a una cuenta que ya no debería tener esos permisos.
- **Mientras dura la suplantación no se muestra el botón «Salir»** de la barra. Estaba al lado de
  «Volver a mi cuenta» y los dos parecían lo mismo: uno devuelve al master, el otro cierra la
  sesión de verdad. Se dejó solo el que corresponde.

### La base de pruebas

Una sola orden deja la base con datos en todos lados, para probar las pantallas y sobre todo la
impresión:

```bash
php artisan db:seed --class=DemoSeeder
```

Crea **tres hoteles con formas distintas a propósito**, porque un solo hotel esconde los errores:

| Hotel | Piscinas | Rondas | Metros |
|---|---|---|---|
| Aruba Hotel Enterprises N.V. | 5 | 2 | 1 |
| Palm Beach Resort & Spa | 3 | 3 | 2 |
| Eagle Bay Suites | 2 | 1 | 1 |

Los tres llevan **dirección, teléfono y contacto**, que es lo que sale en el membrete impreso.

Y además: unos 40 días de jornadas repartidas entre los tres, con mediciones, químicos, lecturas
de metro, listado de trabajo y materiales; **días repartidos entre dos colaboradores**, para ver
el «Registraron: colab1, colab2» y el nombre en cada piscina; **correcciones** cada pocos días,
para la fila amarilla del panel; y **nueve tickets en los cuatro estados**, con su historial de
movimientos y fotos de verdad. **Dos de esos nueve son de clientes que no son hoteles** —una
residencia y un restaurante—, para que se vea que el ticket ya no está atado a la lista.

Las fotos son PNG generados por el propio seeder, escritos byte a byte porque este servidor no
tiene GD. Si fueran archivos falsos, la ruta que las sirve devolvería 404 y no se podría probar la
galería.

Todas las cuentas usan `PRUEBAS_PASSWORD`, o `pruebas2026` si no está en el `.env`:

`admin1`, `colab1`, `colab2`, `colab3`, `hotelaruba`, `hotelpalm`, `hoteleagle`, `jefe1`, `repa1`.

El `master` conserva la contraseña del `.env`: `DemoSeeder` no lo toca.

**El seeder se niega a correr con `APP_ENV=production`** y `php artisan db:seed` no lo llama, así
que estos datos no pueden llegar al servidor por descuido.

---

## Registro de la jornada

Es la pantalla que reemplaza el papel. La usan `colaborador`, `administrador` y `master`.

Tres pasos, pensados para un teléfono a las seis de la mañana:

1. **`/registro`** — elegir hotel y fecha. Abre la jornada de ese día o retoma la que ya
   estaba. Debajo quedan las jornadas recientes para entrar de un clic.
2. **`/jornada/{id}`** — el centro del día, en **dos tarjetas**:
   - **La jornada**: las lecturas de cada metro de agua, el listado de trabajo con sus
     casillas, y los materiales y químicos sacados de almacén.
   - **Piscinas**: cada ronda con sus piscinas y un contador de avance (`3 de 5 piscinas`).
     Las que ya tienen registro se marcan en verde con un visto.
3. **`/jornada/{id}/medicion/{ronda}/{piscina}`** — una pantalla por piscina: las 8 lecturas,
   los 10 químicos con su unidad, el **nivel del agua** (alto, normal o bajo), el retrolavado y
   la observación.

Dejar un campo en blanco significa **no medido**, que no es lo mismo que cero. Un químico en
blanco significa que no se aplicó.

### La sal se mide y se echa, y son dos cosas distintas

Añadida el 4 de septiembre de 2026. Una piscina de sal ronda las 3000 ppm, y para saber cuánta
hay que agregar primero hay que saber con cuánta viene. Por eso la sal aparece **dos veces**, y
no es duplicación:

- **«Nivel de sal»**, entre las lecturas — con cuánta empieza la piscina. Va en `mediciones.sal`,
  en ppm, como la alcalinidad o la dureza.
- **«Sal · kilos»**, entre los químicos — cuánta se le echa. Va como cualquier otro producto, en
  `dosis`.

Separadas, el diario y la hoja impresa muestran las dos: se ve que la piscina estaba en 3120 ppm
y que se le echaron 25 kilos. Si fuera un solo campo habría que elegir cuál de las dos preguntas
responder, y las dos hacen falta para decidir la siguiente dosis.

La ronda se crea sola la primera vez que se guarda una piscina de esa ronda.



### Se guarda solo

No hay botón de guardar. **Cada campo se guarda al salir de él**, tanto en la tarjeta de la
jornada como en la pantalla de cada piscina. Arriba a la derecha un indicador dice qué está
pasando: «Guardando…», «Guardado 06:42» o el error concreto si algo no pasó la validación.

El botón de abajo, **«Listo, volver a las piscinas»**, solo navega: lo escrito ya está en la
base. Antes había dos botones de guardar y era fácil salir creyendo haber guardado.

Detalles que importan:

- Si un guardado se dispara mientras otro está en curso, **se encola uno solo al final** en vez
  de mandar una petición por tecla.
- Un `422` de «No se detectaron cambios» **no es un error**: es que ese valor ya estaba
  guardado, y el indicador lo muestra como guardado.
- Si se cae la conexión, el indicador lo dice en rojo: **«Sin conexión. Lo escrito no se ha
  guardado.»** El técnico se entera en el momento, no al día siguiente.
- Sin JavaScript el formulario sigue funcionando: hay un botón de guardar dentro de
  `<noscript>` y el controlador responde con redirección en vez de JSON cuando la petición no
  pide JSON.


### Control de cambios

Corregir un valor **ya guardado** deja rastro. Corregirlo es legítimo; que nadie se entere, no.

**Qué cuenta como corrección:** solo si el campo **ya tenía un valor** y se modificó. Llenar un
campo vacío por primera vez es captura, no corrección. Sin esa regla el aviso saltaría en cada
tecla y la marca amarilla saldría en todas las jornadas, con lo que no significaría nada.

Las cuatro piezas, que solo sirven juntas:

1. **El aviso.** Al cambiar un campo que llegó con valor, el navegador pregunta: «Estaba en 7.20
   y quedaría en 7.85. El cambio queda registrado y el administrador lo verá.» Si se cancela, el
   campo vuelve a su valor y no se guarda nada.
2. **El registro.** El servidor compara lo que había contra lo que quedó y anota en `cambios` el
   campo, ambos valores, la hora y quién. El aviso es del navegador y se puede saltar; **el
   registro es del servidor y no**.
3. **La marca.** En el panel, la jornada corregida sale con borde ámbar y una etiqueta que dice
   cuántas correcciones tiene.
4. **La comparación.** Esa etiqueta lleva a `/jornada/{id}/cambios`, que lista cada corrección
   con el valor anterior tachado en rojo, el nuevo en verde, la hora y el autor.

Quitar un químico que estaba puesto también se anota, como «→ sin valor».

Solo `master` y `administrador` ven las correcciones; un `colaborador` recibe `403`.

### Los metros de agua

Cada hotel define cuántos metros tiene y cómo se llama cada uno, desde su pantalla de edición.
Al registrar la jornada aparece un campo por metro activo, con su nombre.

El servidor **ignora** cualquier lectura que llegue de un metro que no sea de ese hotel o que
esté inactivo, aunque venga en la petición. Un metro que ya tiene lecturas no se elimina: se
desactiva.

Cuando los metros pasaron de uno a varios, la columna `jornadas.lectura_metro_agua` desapareció
pero el diario y su JSON la seguían pidiendo. Eloquent devuelve `null` para una columna que no
existe **sin avisar**, así que el hotel llevaba desde entonces viendo «—» en el metro de agua y
nada fallaba. Apareció al construir la impresión, que muestra los mismos datos. Ya lee las
lecturas de verdad, en las dos pantallas.

### Quién registró cada medición

`mediciones.usuario_id` guarda **quién la registró**. Antes la jornada guardaba un solo usuario, el
que la abrió, así que si dos colaboradores se repartían el día todo quedaba a nombre de uno.

El autor es **quien la registró la primera vez**. Una corrección posterior **no se lleva la
autoría**: eso ya queda en `cambios`, con su propio autor y con el valor de antes y el de después.
Son dos preguntas distintas —quién la midió y quién la corrigió— y cada una tiene su respuesta.

En pantalla se muestra **solo cuando hace falta**: si el día lo hizo una sola persona, la cabecera
dice «Registró: colab1» y no se repite el nombre en cada piscina. Si lo hicieron varias, la
cabecera dice «Registraron: colab1, colab2» y **cada piscina lleva el nombre de quien la midió**.
El diario, su JSON y la hoja impresa siguen la misma regla.

Las 150 mediciones que ya existían se atribuyeron a quien abrió su jornada, que es lo único que se
sabía de esas filas.

### Cada colaborador ve solo lo suyo

Un colaborador ya no ve el trabajo de los demás. En `/registro` su lista de jornadas recientes
trae **solo aquellas en las que participó**, y abrir por URL una jornada ajena responde **403**,
igual que abrir una de sus piscinas.

**Participar** es haberla abierto **o** haber registrado alguna medición en ella. Por eso hizo
falta primero que cada medición guardara su autor: sin eso, el segundo colaborador del día
desaparecía del registro.

**La jornada de hoy es la excepción y tiene que serlo.** Cualquier colaborador puede entrar en la
de hoy de cualquier hotel, porque a alguien lo pueden mandar a ayudar a media mañana y necesita
entrar a la que abrió su compañero. Al día siguiente esa jornada ya solo la ven los dos que
trabajaron en ella.

`Jornada::puedeVerla()` tiene esa regla en un solo sitio, y la usan las tres pantallas: la jornada,
la piscina y el guardado.

**El diario dejó de ser accesible para el colaborador**, confirmado el 29 de agosto de 2026. Es la
vista del hotel y de la oficina, y ahí vería el trabajo de todos los días y de todos sus
compañeros; lo suyo lo tiene en `/registro`. Filtrar `/registro` y dejar el diario abierto habría
sido cosmético. El botón «Ver el diario» de la pantalla de jornada solo sale para quien puede
entrar.

| | `/registro` | `/jornada` ajena y pasada | `/diario` |
|---|---|---|---|
| `master`, `administrador` | Todo | 200 | 200 |
| `colaborador` | Solo lo suyo | **403** | **403** |
| `hotel` | 403 | 403 | 200, solo el suyo |

### El colaborador no elige el día

En `/registro` el colaborador **no ve un selector de fecha**: ve la de hoy escrita y ya. Y no es
solo la pantalla: `AbrirJornadaRequest` **fuerza la fecha de hoy** para quien no sea `master` ni
`administrador`, así que mandar otra a mano no sirve de nada.

El motivo es que abrir una jornada de otro día le crearía una que **después no puede editar**: la
ventana de corrección es del mismo día. Elegía el día, escribía, y se encontraba con una pantalla
cerrada.

El `master` y el `administrador` sí conservan el campo, porque su trabajo es corregir días
pasados.

### La ventana de corrección

El `colaborador` solo puede editar la jornada **del día en curso**. Pasada la medianoche en
hora de Aruba, la pantalla queda en solo lectura con un aviso, y la corrección la hace un
`administrador` o el `master`, que no tienen ese límite.

El bloqueo no es solo visual: los campos se deshabilitan **y** el servidor responde `403`.

---

## Diario del hotel

`/diario/{hotel}` muestra la operación de un día y un calendario para revisar días anteriores.

- El calendario marca con un punto los días que tienen registro, y no deja abrir días futuros.
- Al elegir un día, el detalle se carga **sin recargar la página**, por
  `/diario/{hotel}/dia/{fecha}`, y la fecha queda en la barra de direcciones.
- Cada ronda se muestra con una tarjeta por piscina: las 8 lecturas, los químicos aplicados
  con su unidad, el retrolavado y las observaciones.

**Quién lo ve:** el personal de AQUALIVE puede abrir el de cualquier hotel. Un usuario con rol
`hotel` **solo puede abrir el suyo**; cualquier otro devuelve 403, tanto la vista como el JSON.

### La lista de trabajos se muestra entera, no solo lo que alguien tocó

`tareas_realizadas` **solo guarda fila de las tareas que alguien marcó o desmarcó**. Una que nadie
miró no tiene registro, así que si se listara la tabla tal cual, el hotel vería 14 tareas de 20 y
no podría distinguir «no la hizo» de «no estaba en la lista».

`DiarioController::tareasDelDia` arma la lista sumando las **tareas activas de hoy** y las que
**tengan registro en esa jornada** —por si alguna se desactivó después—, y marca cada una según su
fila, o como no hecha si no la tiene. El diario y la hoja impresa usan la misma lista, así que
dicen lo mismo.

Junto a ella va el conteo **«11 de 20»**, y lo hecho se distingue de lo pendiente por el color del
texto y la casilla, no solo por la marca.

### Datos de ejemplo

Para ver el diario con historial: `php artisan db:seed --class=DemoSeeder`. Está descrito en
«La base de pruebas».

### La aplicación no juzga los parámetros, y es a propósito

El diario y la hoja impresa muestran los valores **tal cual**, sin marcar si están bien o mal.
No falta cargar unos umbrales: **se decidió que no los va a haber** (28 de agosto de 2026).

Quien lea esto dentro de un tiempo va a pensar que es un olvido y va a proponer añadirlos. No lo
es. La aplicación registra lo que se midió; quien interpreta esa medición es el técnico de
AQUALIVE, que además conoce la piscina. Una pastilla roja de «fuera de rango» en un papel que ve
un hotel cliente es una afirmación técnica con consecuencias, y no la puede firmar un umbral
puesto por el programador.

Si algún día AQUALIVE define rangos propios y quiere que la aplicación los aplique, la
conversación empieza por quién los firma, no por dónde va el color.

---

## Reparaciones

Es la sección que reemplaza los avisos sueltos por WhatsApp: lo que se dañó, de quién es, y
en qué punto va el cobro. Entran `master`, `jefe` y `reparacion`; para cualquier otro rol la
sección devuelve **403**, y el enlace del menú ni siquiera se dibuja.

Va **primero y resaltado** en el menú superior porque es lo que más se consulta durante el día.

### El cliente es texto libre, no un hotel de la lista

Al principio un ticket colgaba de `hotel_id`, como todo lo demás. Estaba mal: **una reparación se
le puede hacer a quien no usa el sistema** —una residencia, un restaurante— y esa gente no tiene
por qué existir como hotel, con sus piscinas y sus rondas, solo para poder cobrarle un trabajo.

Desde el 4 de septiembre de 2026 el ticket guarda `cliente` (obligatorio) y `direccion` (opcional),
los dos escritos a mano. Se dejó de crear una ficha de hotel para algo que es un dato de una sola
reparación.

La migración que lo cambió **copió a cada ticket el nombre y la dirección del hotel que tenía**
antes de soltar la llave foránea, así que los que ya existían no perdieron de quién eran. Por eso
los tickets viejos llevan nombre de hotel como cliente: no es un error, es de dónde vienen.

El historial dejó de filtrar por un desplegable de hoteles y pasa a filtrar **escribiendo el
nombre del cliente**, que es lo único que tiene sentido cuando el nombre es texto.

### La dirección se ve y se puede llevar al mapa

El tablero muestra el cliente y debajo su dirección: quien va a reparar necesita saber a dónde,
y abrir ticket por ticket para averiguarlo no sirve cuando se está armando la ruta del día.

En el ticket la dirección va en su propia línea, con **Copiar** y **Ver en el mapa**. Son dos
porque el portapapeles no está garantizado: `navigator.clipboard` exige sitio seguro *y* permiso
del navegador, y si alguno falta el botón no copia nada. El enlace a Google Maps llega al mismo
sitio sin depender de eso, y en el teléfono de un solo toque. El botón de copiar **nace oculto y
lo enciende el JavaScript**: sin JavaScript no debe verse un botón que no hace nada, y la
dirección se puede seleccionar con el dedo igual.

### Los seis estados: tres que siguen abiertos y tres que cierran

| Estado | En pantalla | Color | |
|---|---|---|---|
| `por_hacer` | Por hacer | Rojo | Abierto |
| `por_facturar` | Por facturar | Ámbar | Abierto |
| `por_cobrar` | Reparado y por cobrar | Azul | Abierto |
| `cobrado` | Cobrado | Verde | Cierra |
| `visita_realizada` | Visita realizada | Pizarra | Cierra |
| `garantia_realizada` | Garantía realizada | Naranja | Cierra |

El tablero muestra **solo los tres abiertos**, una columna por estado. Los otros tres dan por
terminada la reparación: el ticket sale del tablero y pasa al historial. El tablero es la lista
de pendientes, no el archivo.

**Visita y garantía se agregaron el 5 de septiembre de 2026.** Cierran igual que `cobrado`, pero
**sin cobro**: una visita que resultó no ser falla y una reparación que volvió por garantía no se
facturan, y meterlas como «cobrado» ensuciaría el historial de cobros. Como los tres conviven
ahí, cada uno lleva su color y su etiqueta.

El pizarra de la visita no es decorativo: el primer color que se probó fue un verde azulado, y
medido en Lab quedaba a un ΔE de 33 del verde de `cobrado` —distinguible, pero justo—. El pizarra
está a 43 y además dice lo que es: cerrado, sin dinero de por medio.

Los tres que cierran están **a ΔE 43 o más** entre sí, que es la única separación que importa:
son los únicos que conviven, y conviven solo en el historial. Se llegó a descartar un naranja por
parecerse al ámbar de «por facturar», hasta caer en que **el ámbar nunca aparece ahí**: es un
estado abierto. El único sitio donde coinciden es la lista de «quién lo movió» dentro de un
ticket, y solo si pasó por facturar antes de cerrarse por garantía, que es un camino raro.

### El ámbar de aviso, que no se leía

`#c07a00` daba **3,49 de contraste** con texto blanco, por debajo del 4,5 que necesita un texto
normal. No era solo la pastilla de «por facturar»: es el ámbar de aviso de toda la aplicación
—mensajes de alerta, la jornada, el panel, usuarios, la contraseña provisional— y fallaba en sus
**dos papeles**, como fondo con texto blanco y como texto sobre blanco.

Pasó a `#9a6200`, que da **5,10**. Sigue leyéndose como ámbar, solo que más profundo. Con eso las
seis pastillas de estado pasan el mínimo: 5,1 · 5,2 · 5,3 · 6,5 · 7,2 · 7,9.

Una visita o una garantía **no pasan por «por facturar»**: se atienden y se cierran. El
desplegable «Mover a» permite ir a cualquier estado, igual que antes, porque la operación real no
es lineal.

Las columnas van sobre un gris azulado y las tarjetas en blanco. Al principio las dos eran
blancas y el tablero se leía como un bloque: no se veía dónde acababa un ticket y empezaba el
siguiente. Además de la separación, cada tarjeta lleva a la izquierda **una banda del color de su
estado**, así que dice de qué columna es aunque se mire de reojo o se llegue desde el buscador
del teléfono, donde las columnas van una debajo de otra.

### La observación se edita, y lo anterior no se pierde

Una reparación cambia mientras avanza: se diagnostica una cosa y resulta ser otra, se pide un
repuesto, llega el martes. La observación se edita desde el propio ticket, con el botón
**Editar** al lado del bloque.

**Cada edición guarda lo que decía antes, quién la cambió y cuándo**, y eso se ve debajo, igual
que el historial de estados. Sin ese rastro, editar sería borrar: alguien podría quitar una nota
incómoda —«se avisó que la bomba estaba al límite»— y no quedaría forma de saber que existió. Es
el mismo criterio de las correcciones de mediciones y de los cambios de contraseña.

Guardar el mismo texto devuelve **422** y no ensucia el historial con una edición que no cambió
nada. Vaciar la observación sí se registra: pasar de un texto a nada es un cambio, y el
historial muestra lo que decía.

Entran los mismos tres roles de la sección. Un `colaborador` recibe **403**.

Al guardar, la pantalla se recarga a propósito: la edición entra en el historial de abajo, y
armarlo a mano en JavaScript sería repetir lo que ya hace Blade. Si no se recargara, la
constancia se vería incompleta justo después de dejarla.

### El rastro de quién movió qué

Cada movimiento queda en `movimientos_ticket`, con el estado del que venía, al que fue, quién
lo movió y cuándo. **La creación también es un movimiento**, con `estado_anterior` en `NULL`:
así el historial arranca desde el origen y no desde el primer cambio.

El reparador puede mover un ticket a cualquier estado. No se le limita el paso porque la
operación real no es lineal —un cobro se cae, una factura se rehace— y porque el rastro ya
dice quién lo hizo. Mover un ticket al estado en el que ya estaba devuelve **422** y no
ensucia el historial con una línea que no cambió nada.

El historial se borra junto con el ticket (`cascadeOnDelete`): sin ticket no significa nada.

### Quién puede borrar

Solo `jefe` y `master`. Se comprueba **en el controlador**, no solo escondiendo el botón:
`TicketController::destroy` devuelve 403 a cualquier otro, así que el reparador tampoco lo
consigue mandando la petición a mano.

### Las fotos

Hasta **6 por ticket**, de **5 MB** cada una, en JPG, PNG o WEBP. Se suben desde la pantalla del
ticket y se ven en grande al tocarlas.

Hay **dos botones y no uno**: «Tomar foto» y «Elegir de la galería». Son dos campos separados
porque el atributo `capture` no es un filtro sino una orden: puesto en el campo, el teléfono abre
**solo** la cámara y se pierde el acceso a la galería. Con dos campos, ambos llamados `fotos[]`, se
puede tomar una foto **y además** elegir otras del carrete, y llegan juntas en el mismo envío.

El `accept` es `image/*` y no la lista de tipos concreta: con la lista, Android a veces no ofrece
la cámara en el selector. Lo que no sirva lo rechaza el servidor con un mensaje que explica qué
hacer.

El botón «Subir» nace habilitado en el HTML y es el JavaScript el que lo apaga hasta que haya algo
elegido. Al revés, sin JavaScript el botón quedaría muerto.

**No viven en `public/`.** Se guardan en `storage/app/private/tickets/{id}/` con un nombre UUID,
y se sirven por la ruta `reparaciones/foto/{foto}`, que está dentro del mismo grupo con
`rol:master,jefe,reparacion`. Dos motivos:

- No hace falta `php artisan storage:link` al desplegar. Es el paso que más se olvida y el que
  rompe las imágenes en silencio.
- Son fotos de instalaciones de hoteles clientes. En `public/` cualquiera con la dirección las
  vería sin iniciar sesión; así, quien no tenga el rol recibe **403** como en el resto del módulo.

El costo es que las imágenes pasan por PHP en vez de servirlas Apache directo. Con seis fotos por
ticket no se nota.

**No hay miniaturas ni se redimensiona**: este servidor no tiene la extensión **GD**. Por eso el
límite de peso es firme y el navegador recorta la miniatura con `object-fit`. Tampoco se acepta
**HEIC**, el formato por defecto de los iPhone: se guardaría bien pero ningún navegador lo
dibuja, así que es mejor rechazarlo con un mensaje que explique cómo cambiarlo.

Borra la foto quien la subió, el `jefe` o el `master`. Al eliminar un ticket, la base borra las
filas en cascada pero **los archivos se borran a mano** en `TicketController::destroy`: si no,
quedarían ocupando el servidor para siempre.

### El contador y el aviso en vivo

En la barra superior, pegado al enlace de **Reparaciones**, va el número de tickets **sin
cobrar**. Sale en todas las pantallas de `master`, `jefe` y `reparacion`, no solo en el tablero:
el número se calcula en un *view composer* de `AppServiceProvider`, para que la vista no consulte
la base.

Cada **15 segundos** la página le pregunta a `reparaciones/resumen` cómo va la cosa. Si algo
cambió, aparece un aviso abajo diciendo qué pasó —«Entró un ticket nuevo», «Un ticket pasó a
"Reparado y por cobrar"»— y el contador late para que se note.

El resumen devuelve un **sello**: el `md5` de la lista de tickets abiertos con su estado. Cambia
si entra uno, si alguno se mueve de columna o si se borra, y **no** cambia por cosas que no
importan para el aviso, como agregarle una foto. La respuesta va con `Cache-Control: no-store`;
si no, el navegador serviría la misma copia una y otra vez y el aviso nunca saldría.

**No es tiempo real y no pretende serlo.** No hay WebSockets porque el hosting es compartido y no
admite un proceso escuchando todo el día; y una conexión abierta por empleado le ocuparía a
Apache un trabajador entero. Preguntar cada 15 segundos cuesta cuatro consultas por minuto y por
persona conectada, que para este tamaño de equipo no se siente.

Con la pestaña de fondo el sondeo se detiene, y al volver a ella se pregunta enseguida. Si la
sesión caduca, deja de preguntar en vez de insistir contra el login.

**El aviso no recarga la pantalla solo.** Trae un botón «Ver el tablero». Recargar por su cuenta
le borraría a alguien lo que estuviera escribiendo en el formulario de un ticket.

### El historial de cobrados

Pantalla aparte, con filtro por **nombre de cliente** y por rango de fechas. El del cliente es una
búsqueda por partes: escribiendo `palm` salen todos los de Palm Beach. Muestra hasta 50 y dice
cuántos hay en total. Cada fila lleva la pastilla de **cómo terminó**, que es lo que distingue un
cobro de una visita o una garantía.

**La fecha sale del movimiento que cerró el ticket, no de `updated_at`.** Al principio era
`updated_at`, y funcionaba hasta que la observación se volvió editable: editar la nota de un
ticket cerrado hace un mes le cambiaba la fecha del historial y lo subía al primer puesto, como si
se hubiera cobrado hoy. La consulta hace `leftJoin` con el último movimiento hacia un estado que
cierra; si por lo que sea no hubiera movimiento, cae a `updated_at` y la fila no desaparece.

---

## Imprimir la revisión de un día

Desde el diario, el botón **«Imprimir el día»** abre `diario/{hotel}/dia/{fecha}/imprimir` en una
pestaña nueva: la hoja del día con membrete, lista para papel o PDF. Si el día no tiene registro
el botón queda apagado, y la ruta responde **404**: no se imprime una hoja en blanco con membrete.

Entran `hotel` (solo el suyo), `colaborador`, `administrador` y `master`. El `jefe` y el
`reparacion` **no**: la química del agua de un cliente no es lo suyo. Esa restricción se aplicó a
todo el diario, no solo a la impresión — antes cualquier usuario con sesión veía el diario de
cualquier hotel salvo el rol `hotel`.

### Es una vista aparte, no la pantalla con `@media print`

La hoja **no carga `general.css`**. Tiene su propio `partials/head-impresion` y su propia
`impresion.css`. La alternativa era envolver todo el diseño de pantalla en `@media screen` y
escribir encima las reglas de impresión, y sale peor: cada retoque de una pantalla obliga a
comprobar que no rompió el papel, y basta olvidar un `@media` para que un fondo de color se cuele
en la impresión. Aquí lo que se imprime está en un archivo que solo sirve para eso.

### Que se vea igual en color y en blanco y negro

Ningún dato depende del color para entenderse. El retrolavado dice **«Sí»** o **«No»**, no es un
punto verde; el nivel del agua dice «Alto», «Normal» o «Bajo». El color solo acompaña: la raya del
membrete y el título en azul de marca, que en blanco y negro salen grises y no estorban.

Tampoco hay fondos de color rellenando filas. Además de gastar tóner, obligan a `print-color-adjust:
exact` para que el navegador no los descarte, y aun así cada impresora los interpreta distinto.
Las separaciones se hacen con líneas.

### El corte de página

Cada ronda es un bloque con `page-break-inside: avoid`, así no se parte por la mitad. Si una ronda
no cabe entera, la cabecera de su tabla se repite en la página siguiente (`display: table-header-group`),
para que las columnas no queden sin rótulo. Las filas de medición tampoco se parten de su línea de
químicos.

---

## Tamaños para el teléfono

La jornada se llena **de pie, junto a la piscina, con las manos mojadas**. Eso fija los tamaños,
no el gusto: en pantallas de hasta 480 px nada que haya que tocar baja de **44 px de alto**, y
las etiquetas en versalitas suben de 12 a 13 px con menos espaciado, porque a 12 px con el sol
de frente no se leen.

Eso incluye **la barra superior**: los enlaces del menú, el nombre de usuario y el botón de salir
estaban en 31 px. Se midieron emulando un Pixel 9 (412 px), no a ojo.

### El menú se parte, no se sale

Con las cinco entradas del `master` la fila del menú no cabe en un teléfono y **se salía de la
pantalla**: la última quedaba cortada contra el borde. Ahora baja de línea.

Junto a eso apareció el que de verdad rompía la pantalla: en `/hoteles`, la tabla apilada en móvil
no dejaba **bajar de línea el nombre largo de un hotel**, así que estiraba el documento entero a
418 px en una pantalla de 360. Eso es lo que dejaba la barra azul del encabezado corta contra el
borde derecho — parecía un problema del encabezado y era de la tabla, tres secciones más abajo.

Se barrieron todas las pantallas a 280, 320, 360, 390, 412 y 430 px comprobando
`scrollWidth > clientWidth`. **280 px** entró en la lista porque es lo que mide una Galaxy Fold
cerrada, y ahí la rejilla de la medición baja sola a una columna.

### La etiqueta viaja pegada a su campo

En la pantalla de medición los campos van en dos columnas y las etiquetas encima. Cuando una
etiqueta se partía en dos líneas, su vecina de al lado quedaba con alto de sobra y **el campo se
hundía al fondo de la celda**: la etiqueta se quedaba arriba, pegada a la fila anterior, y se leía
como si fuera de ella.

La causa era un `margin-top: auto` puesto en el **campo**. Ahora está en la **etiqueta**: el
sobrante de alto se va por encima y la pareja etiqueta-campo viaja junta. Medido en todos los
anchos, la distancia de una etiqueta a su campo es de 6 px y a la fila siguiente de 20 a 24. La
más corta es siempre la que agrupa, que es de lo que se trata.

### La lista de trabajo

Son 20 tareas y se marcan a lo largo del turno, así que el riesgo real es **saltarse una**.

- La casilla mide **30 px** en el teléfono y 24 en el escritorio; la fila entera es tocable y
  mide 56 px de alto.
- Lo que **falta** va en ámbar con una banda a la izquierda: salta a la vista al bajar.
- Lo **hecho** se apaga en gris, con la casilla en gris oscuro y el texto tachado.
- Debajo hay un contador de **hechas / por marcar** con una barra de avance. En el teléfono va
  **fijo abajo**, siempre visible aunque se baje hasta las piscinas, y el contenedor lleva un
  hueco al final para que no tape la última línea. Cuando no falta ninguna, el número y la barra
  pasan a verde.

El contador **se calcula de las casillas**, no de un número guardado aparte, y se recalcula
también cuando el guardado falla y la casilla vuelve atrás. Así no puede quedar diciendo algo
distinto de lo que se ve.

### La pantalla de la piscina

Los nombres largos de producto («Bicarbonato de sodio libras») parten en dos líneas y dejaban el
campo de al lado a otra altura. Cada celda de la rejilla estira y el sobrante se va **por encima
de la etiqueta**, así que los campos quedan alineados aunque una etiqueta ocupe el doble, y la
etiqueta viaja pegada al suyo. (Ver «La etiqueta viaja pegada a su campo»: al principio el
sobrante se ponía debajo de la etiqueta y el resultado se leía al revés.)

La unidad estaba en azul agua sobre blanco, que casi no se ve al sol. Pasa a gris y se distingue
de la etiqueta por el grosor, no por el color. La casilla de retrolavado sube de 18 a 26 px.

### Las pantallas de administración

Mismo criterio, y ahí estaba el control más pequeño de toda la aplicación: la casilla de
**«Hotel activo» medía 13 × 13 px**. Ahora mide 26 en el teléfono, y su fila 48 de alto.

| Control | Antes | Ahora |
|---|---|---|
| Casilla «Hotel activo» | 13 × 13 | 26 × 26 (móvil), 22 (PC) |
| Casillas de rol al crear usuario | 16 × 16 | 26 × 26 (móvil), 22 (PC) |
| Campos en línea de rondas y metros | 33 px alto, letra 14 | 44 px, letra 16 |
| Enlace del hotel en la lista | 21 px alto | 44 px |

Los campos en línea suben a **16 px de letra en el móvil** por un motivo concreto: por debajo de
16, **el iPhone hace zoom a la página entera al enfocar el campo**, y hay que volver a alejarla a
mano. No es estética, es que la pantalla se descoloca al escribir.

### Los roles se muestran con tilde

El identificador del rol es `reparacion`, sin tilde, porque se compara en código. Pero la pantalla
mostraba ese mismo identificador con un `text-transform: capitalize`, así que se leía
**«Reparacion»**. Ahora `Rol::etiquetas()` da el texto que lee una persona —«Reparación»— y el
identificador se queda como está. Se usa en la lista de usuarios, en el perfil, en las casillas de
rol y en la franja de «ver como».

---

## Zona horaria

La operación es en **Aruba**. Toda la aplicación corre en `America/Aruba` (**AST, UTC−4, sin
horario de verano**), configurado en `config/app.php` y en `APP_TIMEZONE`.

En la máquina de desarrollo conviven tres relojes distintos: el de PHP, el de MySQL y el de
Laravel. **Usa siempre `now()` de Laravel/Carbon.** Nunca `NOW()` ni `CURRENT_TIMESTAMP` de
MySQL, ni `date()` de PHP a secas: esos devuelven la hora del servidor, no la de Aruba, y una
ronda de las 19:00 puede terminar registrada en el día equivocado.

Al desplegar, confirma que `APP_TIMEZONE` esté en el `.env` del servidor.

---

## Modelo de datos de la operación

Sale directo de los dos formatos en papel que llenan los empleados.

| Tabla | Qué guarda |
|---|---|
| `hoteles` | El cliente |
| `rondas_programadas` | La lista de rondas de cada hotel: nombre, hora y orden |
| `metros_agua` | Los metros de agua de cada hotel, con nombre y orden |
| `lecturas_metro` | La lectura de cada metro en cada jornada |
| `piscinas` | Las piscinas de cada hotel (POOL VIP, BIG POOL, SPA HOT…), editables |
| `productos` | Los 10 químicos con su unidad: galones, onzas, libras, kilos o tabletas |
| `jornadas` | La hoja del día: fecha, materiales sacados, quién firma |
| `rondas` | La ronda que se hizo ese día, con la hora real y la observación |
| `mediciones` | Las 8 lecturas por piscina y ronda, el nivel del agua y el retrolavado |
| `dosis` | Cuánto se aplicó de cada producto |
| `tareas` | El listado de trabajo diario: una lista corrida, el `orden` marca la secuencia del turno |
| `tareas_realizadas` | Qué se marcó ese día |
| `cambios` | Qué se corrigió después de haber guardado, con el valor anterior y el nuevo |
| `ediciones_observacion_ticket` | Qué decía la observación de un ticket antes de cada edición, y quién la cambió |

Las **8 lecturas** son las siete del formato —`cl_libre`, `cl_total`, `cl_combinado`, `ph`,
`alcalinidad`, `dureza_calcio` y `acido_cianurico`— más `sal`, que pidió el equipo el 4 de
septiembre de 2026.

`back Wash` del papel **no es un producto**: es una acción, y va como el booleano `retrolavado`
en `mediciones`.


### Las unidades de los químicos

El formato en papel decía «und» en varios químicos, que no mide nada. Cada producto lleva ahora
la unidad con la que de verdad se dosifica:

| Unidad | Productos |
|---|---|
| **galones** | Ácido muriático |
| **onzas** | Alguicida, Super blue, Balance fosfato |
| **libras** | Cloro granulado, Tricloro, Bicarbonato de sodio |
| **kilos** | Ácido cianúrico, Sal |
| **tabletas** | Tabletas 3" |

El criterio: líquidos en galones u onzas según el tamaño de la dosis, sólidos en libras. Las
tabletas son la excepción y se cuentan: nadie las pesa, echa tres. «Cloro granulado» decía
«1.5 lb / cup», que era la medida del cacito y no una unidad.

El **ácido cianúrico pasó de libras a kilos** el 4 de septiembre de 2026, a petición del equipo.
Solo cambió la etiqueta: **las cantidades ya registradas se dejaron como estaban**, porque nadie
puede saber hoy si se anotaron pensando en una unidad o en la otra, y convertirlas a ciegas
inventaría datos. Si alguna hay que corregir, la corrige quien la aplicó.

Están en `ProductoSeeder`. Como usa `firstOrCreate`, cambiar una unidad ahí **no actualiza** los
productos ya sembrados: por eso el cambio de unidad viajó en una migración, que es lo único que
llega a un servidor donde los seeders no se corren.

### El nombre de una piscina se corrige, y se corrige hacia atrás

Las piscinas se editan en línea desde la tabla del hotel, igual que las rondas y los metros: se
cambia el nombre o el orden y se pulsa **Guardar**. El endpoint ya aceptaba los dos campos; lo que
faltaba era la pantalla, así que un nombre mal escrito **se quedaba para siempre**: una piscina
con mediciones no se puede eliminar, y no había otra forma de tocarla.

Al corregir el nombre, **los registros ya hechos pasan a mostrar el nombre nuevo**, en el diario y
en la hoja impresa. Eso es a propósito y es lo que se quiere aquí: las mediciones apuntan a la
piscina por su id, no guardan una copia del nombre, así que corregir una errata la corrige en todo
el historial. Es una piscina que siempre fue la misma y estaba mal escrita, no una piscina
distinta.

Si algún día hiciera falta que un informe viejo conserve el nombre que tenía ese día, habría que
guardar el nombre junto con la medición. Hoy no hace falta y complicaría el modelo.

### Las piscinas y las rondas son datos, no columnas

En el formato en papel, SPA HOT y SPA COLD están **escritas a mano** sobre las piscinas
impresas. Por eso las piscinas se administran por hotel y nunca se escriben en el código.

Lo mismo con las rondas: no son dos fijas de mañana y tarde. Cada hotel arma su propia lista
en `rondas_programadas` y puede agregar las que necesite. Eso resuelve además la discrepancia
entre los dos formatos en papel, donde uno dice 5:00 y el otro 6:00.

Una ronda que ya se usó en alguna jornada no se elimina: se desactiva.

---

## Comandos útiles

```bash
php artisan migrate:fresh --seed   # rehace la base desde cero
php artisan db:seed                # vuelve a sembrar (no duplica)
php artisan optimize:clear         # limpia todas las cachés
php artisan route:list             # rutas declaradas
```

---

## Pendientes

### Funcionalidad que falta

**Nada de lo pedido está pendiente.** Todo lo de la lista de cambios quedó hecho o decidido, y la
lista de decisiones está abajo para que no se vuelva a proponer lo descartado.

Lo que falte va a salir del uso real: cuando el equipo lleve unas semanas registrando jornadas
aparecerán cosas que hoy no se pueden adivinar. Anótalas ahí y se resuelven con datos delante, no
antes.

### Decidido que no se hace

- **Que el hotel vea sus propias reparaciones.** Se propuso y **se descartó** el 29 de agosto de
  2026. El tablero no es un parte de averías: sus estados son **«por facturar»**, **«reparado y
  por cobrar»** y **«cobrado»**. Eso es la cadena de cobro de AQUALIVE, no información del hotel.
  Enseñarle a un cliente que su reparación está «por cobrar» expone una conversación comercial que
  no le toca a él tener con una pantalla. Si algún día el hotel debe enterarse de una reparación,
  será con otros estados y otro texto, no abriéndole este tablero.

  Hoy ya está cerrado: `/reparaciones`, `/reparaciones/historial` y `/reparaciones/resumen`
  responden **403** al rol `hotel`, y el enlace no aparece en su barra.
- **`jornadas.entregada`.** Se quitó el 29 de agosto de 2026. Venía del formato en papel, que se
  «entrega» al final del turno, pero **nunca se escribió ni se leyó** desde ninguna pantalla:
  estaban la columna, el `$fillable`, el cast y la regla de validación, y nada más. Si algún día
  el turno se cierra de verdad, se vuelve a agregar junto con lo que signifique cerrarlo —una
  firma, una hora, un bloqueo—, no como una casilla suelta.
- **Recuperar la contraseña por correo.** Se empezó y **se descartó** el 29 de agosto de 2026.
  Con un equipo de seis o siete personas y un administrador localizable, el único caso que el
  correo cubre —recuperar un domingo por la noche— es raro, y a cambio mete piezas que fallan en
  silencio: que el hosting entregue el correo, que no caiga en no deseado, y que la dirección
  registrada sea real y la lea alguien. Si el correo de un técnico está mal escrito, la
  recuperación no funciona y **nadie se entera**, porque la pantalla tiene que responder lo mismo
  exista o no la cuenta. En su lugar se hizo el **registro de quién cambió la contraseña de
  quién**, que es lo que se quería: saber quién pidió el cambio.
- **Rangos de referencia de los parámetros del agua.** Se propuso y **se descartó** el 28 de
  agosto de 2026. El aplicativo registra las mediciones y no las califica. El porqué está en
  «La aplicación no juzga los parámetros, y es a propósito».

### Antes de producción

Los pasos del servidor están en **[docs/DESPLIEGUE.md](docs/DESPLIEGUE.md)**. Aquí solo lo que hay
que resolver **fuera** del despliegue:

- **Corregir el `.ai` maestro.** Los PNG ya dicen «POOL TECHNOLOGY», pero la parte editable del
  `.ai` sigue con la errata, así que vuelve en cuanto alguien exporte de él. El reemplazo, en
  curvas, está en `Libro de marca/logos/descriptor-pool-technology.svg`.
- **Los datos del hotel**: dirección, teléfono y persona de contacto. Salen en el membrete de la
  hoja impresa que recibe el cliente y hoy están vacíos. Se llenan desde la pantalla del hotel.

**El sitio de pruebas sí tiene los datos de demo.** `aqualiveapp.com` corre con `APP_ENV=staging`
justamente para eso: el equipo está probando ahí con las nueve cuentas y las jornadas del
`DemoSeeder`, y desde el 4 de septiembre de 2026 esos datos **ya no son desechables** —hay
jornadas capturadas a mano y roles cambiados que nadie quiere perder.

De ahí salen dos reglas para ese servidor:

- **`migrate` a secas, nunca `migrate:fresh`**, y ningún seeder. Un cambio que necesite
  transformar una tabla existente se hace con una migración nueva que convierta los datos.
- **Respaldo diario** de la base y de las fotos, con `scripts/respaldar.sh`. El procedimiento y la
  restauración están en [docs/DESPLIEGUE.md](docs/DESPLIEGUE.md).

Cuando llegue la producción de verdad, ahí sí `APP_ENV=production`: `DemoSeeder` se niega a correr
con ese valor y `php artisan db:seed` no lo llama, así que las cuentas de prueba no pueden colarse.
