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
| Administración de usuarios | Funcionando |
| Modelo de datos de la operación | Funcionando |
| Hoteles y piscinas (pantallas) | Funcionando |
| Diario del hotel con calendario | Funcionando |
| Registro de la jornada (colaborador) | Funcionando |
| Perfil propio y cambio de contraseña | Funcionando |
| Filtros del panel (hotel, empleado, fechas) | Funcionando |
| Ver como otro usuario (soporte) | Funcionando |
| Rangos de referencia de los parámetros | Pendiente |
| Reportes al hotel | Pendiente |

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

| Rol | Qué puede hacer |
|---|---|
| **master** | Todo. Es el único que puede eliminar administradores. **No lo puede eliminar nadie.** |
| **administrador** | Crear usuarios y asignar roles. **No puede eliminar a otro administrador.** |
| **colaborador** | Ingresar la información de mantenimiento. |
| **hotel** | Ver su información y la de sus piscinas. Solo lectura. |

### Reglas de eliminación

Están en `UsuarioController::destroy` y se aplican en este orden:

1. Al `master` no lo elimina nadie, nunca.
2. Nadie se elimina a sí mismo.
3. A un `administrador` solo lo elimina el `master`.
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
                       HotelController, PiscinaController,
                       RondaProgramadaController, DiarioController,
                       RegistroController, MedicionController,
                       SuplantacionController, PerfilController
  Http/Middleware/     VerificarRol      (alias 'rol', se usa 'rol:master,administrador')
  Http/Requests/       IniciarSesion, StoreUsuario, UpdateUsuario,
                       StoreHotel, UpdateHotel, StorePiscina, UpdatePiscina,
                       StoreRondaProgramada, UpdateRondaProgramada,
                       AbrirJornada, UpdateJornada, StoreMedicion,
                       UpdatePerfil, CambiarPassword
  Models/              Rol, Usuario, Hotel, Piscina, RondaProgramada, Producto,
                       Jornada, Ronda, Medicion, Dosis, Tarea, TareaRealizada
database/
  migrations/          sessions, cache, jobs, roles, usuarios,
                       hoteles, piscinas, rondas_programadas, productos,
                       tareas, jornadas, rondas, mediciones, dosis,
                       tareas_realizadas
  seeders/             RolSeeder, UsuarioMasterSeeder, ProductoSeeder,
                       TareaSeeder, HotelSeeder,
                       JornadaDemoSeeder (datos de ejemplo, no se llama solo)
public/
  css/                 general.css + una hoja por vista
  js/                  un archivo por vista
  img/                 logo, isotipo y derivados
resources/views/
  partials/            head, header, header-limpio, mensaje, footer
  login, panel, usuarios, hoteles, hotel, diario,
  registro, jornada, medicion, perfil
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

El manual completo está en `Libro de marca/manual-de-marca-aqualive.html` (se abre con doble clic).

### Errata conocida en el logo maestro

El archivo `LOGO FINAL VECTORIAL vertical.ai` dice **«POLL TECHNOLOGY»**, no «POOL».
En esa tipografía la `O` mide 25,66 con dos subtrazos y la `L` mide 15,66 con uno; los
caracteres 3 y 4 de la primera palabra son byte a byte idénticos entre sí.

Los PNG de `public/img/` se generaron **fieles al archivo maestro**, así que arrastran la errata.
La versión horizontal del logo sí dice POOL. **Hay que corregir el `.ai` antes de que esto
llegue a un cliente.**

---

## Responsive

Dos cortes, y solo dos, definidos en `general.css`:

- `768px` — tablet
- `480px` — móvil

En móvil las tablas se apilan en tarjetas usando el atributo `data-titulo` de cada
celda, en vez de desplazarse horizontalmente.

---

## Despliegue

En producción (Hostinger, Linux) el contenido de `public/` va copiado en `public_html/`, y el
resto del proyecto queda al lado, fuera del docroot.

Cuidados:

- **Nunca subas `bootstrap/cache/*.php`.** `config:cache` y `route:cache` incrustan rutas
  absolutas; si se generan en Windows, la aplicación se cae en el servidor. Genera las cachés
  por SSH después de cada despliegue.
- **Nunca subas el `.env`.** Está en `.gitignore` y ahí se queda.
- **Todos los nombres de archivo en `public/` van en minúscula.** Windows perdona las
  mayúsculas; Linux no.
- Las migraciones se corren en el servidor.

---

## A dónde entra cada rol

Al iniciar sesión nadie aterriza en una pantalla con sus propios datos: cada rol entra directo
a lo que va a hacer.

| Rol | Aterriza en |
|---|---|
| `colaborador` | `/registro` — elegir hotel y fecha, con sus jornadas recientes debajo |
| `hotel` | `/diario/{su hotel}` — el calendario de sus piscinas |
| `master` y `administrador` | `/panel` — las últimas 12 jornadas de todos los hoteles, con su avance |

`PanelController` redirige según el rol. Un usuario `hotel` sin hotel asignado sí ve el panel,
pero con un aviso de que pida su asignación.


### Filtros del panel

El panel de jornadas filtra por **hotel**, por **empleado** y por **rango de fechas**, y los
cuatro se combinan entre sí. El filtro va por GET, así que la URL se puede compartir o guardar:
`/panel?hotel=1&empleado=3&desde=2026-08-01`.

La lista de empleados solo muestra a quienes **de verdad han registrado alguna jornada**, no a
todos los usuarios.

Se muestran hasta 30 resultados. Si el filtro devuelve más, el conteo lo dice para que se acote
en vez de creer que eso es todo.

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

---

## Las hojas de estilo llevan versión

`@recurso('css/panel.css')` devuelve la ruta con la fecha de modificación del archivo detrás
(`?v=1787872758`). La directiva está en `AppServiceProvider`.

Sin esto el navegador sigue sirviendo la hoja vieja después de cada cambio, y uno cree que el
CSS no funciona cuando en realidad ni se descargó.

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

### Cuentas de prueba

Para revisar las vistas de cada rol durante el desarrollo hay cuentas con dominio `.test`:
`admin1`, `colab1` y `hotelaruba`, todas con la misma contraseña de pruebas.

**Bórralas antes de producción.** No son rutas trampa ni puertas traseras: son usuarios
normales, sujetos a las mismas reglas que cualquier otro.

---

## Registro de la jornada

Es la pantalla que reemplaza el papel. La usan `colaborador`, `administrador` y `master`.

Tres pasos, pensados para un teléfono a las seis de la mañana:

1. **`/registro`** — elegir hotel y fecha. Abre la jornada de ese día o retoma la que ya
   estaba. Debajo quedan las jornadas recientes para entrar de un clic.
2. **`/jornada/{id}`** — el centro del día: la lectura del metro de agua, el listado de
   trabajo con sus casillas, y cada ronda con sus piscinas y un contador de avance
   (`3 de 5`). Las piscinas ya registradas se marcan en verde con un visto.
3. **`/jornada/{id}/medicion/{ronda}/{piscina}`** — una pantalla por piscina: las 7 lecturas,
   los 9 químicos con su unidad, el retrolavado y la observación. El botón principal es
   **«Guardar y seguir con {la siguiente piscina}»**, para no volver al menú entre piscina y
   piscina.

Dejar un campo en blanco significa **no medido**, que no es lo mismo que cero. Un químico en
blanco significa que no se aplicó.

La ronda se crea sola la primera vez que se guarda una piscina de esa ronda.

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
- Cada ronda se muestra con una tarjeta por piscina: las 7 lecturas, los químicos aplicados
  con su unidad, el retrolavado y las observaciones.

**Quién lo ve:** el personal de AQUALIVE puede abrir el de cualquier hotel. Un usuario con rol
`hotel` **solo puede abrir el suyo**; cualquier otro devuelve 403, tanto la vista como el JSON.

### Datos de ejemplo

Mientras el formulario del colaborador no exista, no hay forma de capturar jornadas. Para ver
el diario funcionando:

```bash
php artisan db:seed --class=JornadaDemoSeeder
```

Crea unos 15 días de registros inventados, saltando algunos para que el calendario muestre
días con y sin datos. **No se llama desde `DatabaseSeeder`**: se corre a mano y se puede
borrar cuando ya no haga falta.

### Los parámetros no se juzgan todavía

El diario muestra los valores tal cual, sin marcar si están dentro o fuera de rango. Los
rangos aceptables los define AQUALIVE y todavía no están cargados; poner umbrales inventados
frente a un hotel sería peor que no mostrarlos.

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
| `piscinas` | Las piscinas de cada hotel (POOL VIP, BIG POOL, SPA HOT…), editables |
| `productos` | Los 9 químicos con su unidad: gallon, und, cup, pack, lb |
| `jornadas` | La hoja del día: fecha, lectura del metro de agua, quién firma |
| `rondas` | La ronda que se hizo ese día, con la hora real y la observación |
| `mediciones` | Las 7 lecturas por piscina y ronda, más el retrolavado |
| `dosis` | Cuánto se aplicó de cada producto |
| `tareas` | El listado de trabajo diario, estándar para toda la operación |
| `tareas_realizadas` | Qué se marcó ese día |

Las **7 lecturas** son las del formato: `cl_libre`, `cl_total`, `cl_combinado`, `ph`,
`alcalinidad`, `dureza_calcio` y `acido_cianurico`.

`back Wash` del papel **no es un producto**: es una acción, y va como el booleano `retrolavado`
en `mediciones`.

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

### Bloqueado, esperando una decisión

- **Rangos de referencia de los parámetros.** Hacen falta el mínimo y el máximo de cloro libre,
  pH, alcalinidad, dureza de calcio y ácido cianúrico, y saber si el spa lleva rangos distintos
  de la piscina. Sin eso el diario muestra números sin decir si están bien o mal. Es lo más
  barato de implementar y lo que más valor le agrega a lo ya construido.

### Funcionalidad que falta

- **Reportes al hotel**: el equivalente impreso o en PDF del formato que hoy se entrega en papel.
- **Editar usuarios desde la pantalla.** `UsuarioController::update` existe y está probado, pero
  no hay botón: hoy solo se pueden crear y eliminar.
- **Editar y reordenar piscinas.** Las rondas sí se editan en línea; las piscinas solo se crean,
  activan y eliminan. Es una inconsistencia.
- **Recuperar contraseña olvidada.** Se quitó al inicio porque no se pidió. Hoy la única salida
  es que un administrador la cambie, y para eso hace falta el punto anterior.
- **Paginación del panel.** Muestra hasta 30 jornadas y avisa si hay más. Con meses de operación
  va a hacer falta paginar.
- **Autor por medición.** Hoy la jornada guarda un solo `usuario_id`, el de quien la abrió. Si dos
  colaboradores se reparten el día, todo queda atribuido a uno solo.

### Antes de producción

- **Borrar las cuentas de prueba** `admin1`, `colab1` y `hotelaruba`, y los datos de
  `JornadaDemoSeeder`.
- **Corregir la errata del logo**: el `.ai` dice «POLL TECHNOLOGY» y los PNG la arrastran.
- Confirmar `APP_TIMEZONE=America/Aruba` en el `.env` del servidor.
- Generar las cachés por SSH en el servidor, nunca subir `bootstrap/cache/`.
