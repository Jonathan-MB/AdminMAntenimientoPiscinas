# Desplegar en Hostinger

Procedimiento completo, en orden. Está escrito para hacerse una vez; al final hay una versión
corta para las actualizaciones siguientes.

> **Hecho el 2 de septiembre de 2026** en `aqualiveapp.com`. Lo que
> sigue ya está corregido con lo que el servidor resultó ser, que no era lo que esta guía suponía.
> Las diferencias van marcadas.

Lo que no puede pasar: que el `.env`, la carpeta `vendor/` o las cachés viajen desde Windows.
Todo eso se genera en el servidor.

---

## 1. Antes de tocar el servidor

**Corrige el `.ai` del logo.** Los PNG ya dicen «POOL TECHNOLOGY», pero el archivo maestro sigue
con la errata. El descriptor corregido en curvas está en
`Libro de marca/logos/descriptor-pool-technology.svg`. Si esto sale a un hotel antes de
corregirlo, la errata vuelve la próxima vez que alguien exporte del `.ai`.

**Ten a mano los datos del hotel**: dirección, teléfono y persona de contacto. Salen en el
membrete de la hoja impresa y hoy están vacíos.

---

## 2. La base de datos

En el panel de Hostinger, **Bases de datos MySQL**:

1. Crea una base y un usuario.
2. Anota nombre de base, usuario y contraseña: van al `.env`.
3. Anota también el **host**. En Hostinger casi siempre es `localhost`, no `127.0.0.1`.

No importes nada: las tablas las crean las migraciones.

---

## 3. Subir el proyecto

Hostinger sirve el docroot del dominio. **El proyecto no va ahí dentro**, porque entonces el
`.env` y el código quedarían accesibles desde el navegador.

**El docroot no es `~/public_html`.** En esta cuenta Hostinger usa la estructura por dominios, y
dentro de la carpeta del dominio hay un archivo `DO_NOT_UPLOAD_HERE` avisándolo:

```
/home/u604113341/
├── aqualive/                    <- el proyecto, FUERA del docroot
│   ├── app/  bootstrap/  config/  database/  resources/  routes/  storage/
│   ├── artisan
│   ├── composer.json
│   └── .env                     <- se crea aqui, en el servidor
└── domains/
    └── aqualiveapp.com/
        └── public_html/         <- el contenido de public/, no la carpeta
            ├── index.php
            ├── .htaccess
            ├── favicon.ico
            ├── css/  js/  img/
```

Comprueba primero cuál es tu docroot: `ls ~/domains/*/`.

Sube por **SSH con git** (recomendado) o por el gestor de archivos:

```bash
cd ~
git clone https://github.com/Jonathan-MB/AdminMAntenimientoPiscinas.git aqualive
```

Después copia el contenido de `public/` dentro de `public_html/`:

```bash
DOC=~/domains/aqualiveapp.com/public_html
mv "$DOC/default.php" ~/default.php.hostinger   # la portada de bienvenida de Hostinger
cp -r ~/aqualive/public/. "$DOC/"
```

La carpeta `public/` del proyecto **se deja donde está y no se borra**: al actualizar con
`git pull` se vuelve a copiar de ahí. El docroot es una copia y no un enlace porque Hostinger
tiene `symlink` deshabilitado, así que cada despliegue hay que repetir la copia.

**No subas**: `vendor/`, `node_modules/`, `.env`, `bootstrap/cache/*.php`, ni la carpeta
`Libro de marca/` (es documentación, no la necesita el servidor).

---

## 4. Apuntar el `index.php` a la carpeta del proyecto

Como `public_html/` ya no está dentro del proyecto, hay que decirle dónde está. Edita
`public_html/index.php` y cambia las **tres** rutas `__DIR__.'/../'`:

```php
// Antes
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {

// Después
require __DIR__.'/../aqualive/vendor/autoload.php';
$app = require_once __DIR__.'/../aqualive/bootstrap/app.php';
if (file_exists($maintenance = __DIR__.'/../aqualive/storage/framework/maintenance.php')) {
```

Es el único archivo del proyecto que se edita a mano en el servidor. Anótalo: si algún día
vuelves a copiar `public/`, hay que rehacer este cambio.

---

## 5. El `.env` del servidor

Se crea en `~/aqualive/.env`, copiando `.env.example` y cambiando lo que importa:

```bash
cd ~/aqualive
cp .env.example .env
nano .env
```

Lo que **tiene** que quedar distinto de como viene:

| Variable | Valor | Por qué |
|---|---|---|
| `APP_ENV` | `production` | Bloquea los seeders de desarrollo |
| `APP_DEBUG` | `false` | Con `true`, un error le enseña el código y la base a quien lo vea |
| `APP_URL` | `https://tudominio.com` | De ahí salen los enlaces y las rutas del JavaScript |
| `APP_TIMEZONE` | `America/Aruba` | Ya viene puesto. **Confírmalo**: una ronda de las 19:00 puede quedar en el día equivocado |
| `DB_HOST` | `localhost` | En Hostinger, no `127.0.0.1` |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Los del paso 2 | |
| `MASTER_USUARIO` | El que vayas a usar | |
| `MASTER_CORREO` | Un correo real | Por defecto trae uno `.test` |
| `MASTER_PASSWORD` | Vacío | Déjalo vacío: el seeder genera una y la imprime **una sola vez**. Anótala en ese momento |
| `LOG_LEVEL` | `error` | Con `debug` el log crece sin control |

---

## 6. Instalar y preparar

```bash
cd ~/aqualive

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
```

`--force` hace falta porque en producción Laravel pide confirmación y por SSH no siempre hay
terminal interactiva.

### Composer va a fallar al final, y no pasa nada

Hostinger tiene **`proc_open` deshabilitado**, así que Composer no puede lanzar los scripts de
después de instalar y termina con *«The Process class relies on proc_open»*. **Los paquetes se
instalan igual.** Lo único que queda sin correr es el descubrimiento de paquetes de Laravel, que
se lanza aparte:

```bash
php artisan package:discover
```

La lista completa de funciones bloqueadas incluye también `exec`, `shell_exec`, `symlink` y
`popen`. Por `symlink` es por lo que **`php artisan storage:link` no funcionaría aquí** — y por lo
que fue buena idea servir las fotos por una ruta en vez de por un enlace en `public/`.

`db:seed` corre **solo** lo de producción: roles, usuario master, productos, tareas y el hotel
con sus piscinas y rondas, transcritos del formato en papel. `DemoSeeder`, que es el que llena la
base de datos de prueba, **se niega a correr** con `APP_ENV=production` y tampoco se llama desde
`db:seed`.

**Apunta la contraseña del master** que imprime el seeder. No vuelve a mostrarse.

---

## 7. Permisos y cachés

```bash
chmod -R 775 storage bootstrap/cache
```

`storage/` tiene que ser escribible: ahí van las **fotos de los tickets** y los logs.
No hace falta `php artisan storage:link`: las fotos se sirven por una ruta, no desde `public/`.

Las cachés se generan **aquí, nunca en Windows** — llevan rutas absolutas dentro:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### No corras `route:cache` en el XAMPP local

Comprobado el 29 de agosto de 2026: con `route:cache` puesta y la aplicación colgando de
`/controlMantenimientoPiscinas/public/`, la **raíz** devuelve **405 Method Not Allowed**. La
tabla cacheada dice `GET|HEAD /`, pero el servidor responde `allow: HEAD`. El resto de rutas
funciona; solo se cae la raíz.

**No es un fallo de la aplicación, y en producción no pasa.** Es la combinación del emparejador
compilado de rutas con el montaje en subcarpeta. Servida desde la raíz —que es como va a estar en
Hostinger— la misma caché da **200** y el login carga entero. Se verificó con `php artisan serve`.

Si alguna vez la corres en local por costumbre y la pantalla se cae, la salida es:

```bash
php artisan optimize:clear
```

---

## 8. Revisar la configuración de PHP

En el panel de Hostinger, **PHP configuration**:

En esta cuenta **no hubo que tocar nada**: venía con PHP 8.3.30, y `upload_max_filesize` y
`post_max_size` en **1536M**, muy por encima de lo necesario. Compruébalo igual:

- **PHP 8.2 o superior.**
- `upload_max_filesize` y `post_max_size` en **40M** o más. Seis fotos de 5 MB son 30 MB en una
  sola petición; si `post_max_size` es menor, PHP descarta el formulario **antes** de que Laravel
  lo vea y el empleado recibe un error de sesión caducada, no uno de tamaño.
- Extensiones: `fileinfo` y `pdo_mysql`. Aquí además **hay `gd`**, que en el XAMPP local no
  estaba: deja la puerta abierta a generar miniaturas de las fotos más adelante.

---

## 9. Comprobar que quedó bien

Con el navegador, en este orden:

1. **`https://tudominio.com`** carga el login con el logo diciendo «POOL TECHNOLOGY».
2. Entra con el master. **No** te va a pedir cambiarla: el seeder lo crea directo, sin la marca de
   contraseña provisional. Si la generó él, cámbiala tú desde **tu nombre → Perfil**, porque quedó
   escrita en la salida del comando.
3. **Hoteles** → abre el hotel y llena dirección, teléfono y contacto.
4. **Usuarios** → crea un colaborador de verdad. Anota la contraseña provisional: al entrar, la
   aplicación le va a pedir que elija la suya.
5. **Registro** → abre una jornada, mide una piscina, sube una **foto** en un ticket de
   reparación. Si la foto falla, es el paso 8.
6. **Imprimir el día** desde el diario: revisa que el membrete salga completo.
7. Comprueba la hora: la que muestra la jornada debe ser la de Aruba, no la del servidor.

Y una comprobación de seguridad que vale la pena hacer una vez:

```
https://tudominio.com/.env          -> debe dar 404
https://tudominio.com/storage/logs  -> debe dar 404
```

Si alguna de las dos muestra algo, el proyecto quedó dentro de `public_html/` y hay que rehacer
el paso 3.

---

## 10. Actualizaciones siguientes

**Cuando ya hay gente usando el sitio, respalda la base antes de migrar.** Es un minuto y evita el
único paso irreversible de la lista:

```bash
mkdir -p ~/respaldos
PASS=$(grep "^DB_PASSWORD=" ~/aqualive/.env | sed 's/^DB_PASSWORD=//; s/^"//; s/"$//')
mysqldump -u u604113341_aqualiveapp -p"$PASS" u604113341_aqualiveapp \
  > ~/respaldos/antes-$(date +%Y%m%d-%H%M).sql
```

`migrate` **no borra datos**; `migrate:fresh` sí, y borra todos. En un sitio con gente trabajando
va siempre `migrate` a secas. Si un cambio necesita transformar una tabla que ya existe, eso se
resuelve con una migración nueva que convierta los datos, **nunca editando una migración que ya
corrió**: esa no se vuelve a ejecutar y el servidor se quedaría con la tabla vieja.

```bash
cd ~/aqualive
php artisan down

git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan up
```

Si el cambio tocó archivos de `public/` (CSS, JS, imágenes), **cópialos otra vez**. `git pull`
actualiza `~/aqualive/public/`, pero el docroot es una **copia**, no un enlace: sin este paso el
navegador sigue sirviendo el CSS y el JS viejos, y un archivo nuevo directamente no existe.

```bash
DOC=~/domains/aqualiveapp.com/public_html
cp -r ~/aqualive/public/css/. "$DOC/css/"
cp -r ~/aqualive/public/js/.  "$DOC/js/"
cp -r ~/aqualive/public/img/. "$DOC/img/"
```

Se copian las carpetas una por una **a propósito**: `cp -r ~/aqualive/public/. "$DOC/"` pisaría el
`index.php` del docroot, que es el único archivo editado a mano en el servidor (paso 4). Y la
carpeta `public/` del proyecto **no se borra**: es de donde sale la copia la próxima vez.

Comprueba que llegó, que es más rápido que descubrirlo por el navegador:

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://aqualiveapp.com/js/general.js
```

Y si `public/index.php` cambió, **rehaz el paso 4**.

---

## 11. El respaldo automático

`scripts/respaldar.sh` guarda la base **y las fotos de los tickets**, que no están en la base: sin
ellas se restaurarían los tickets sin sus fotos. Deja los últimos **14 días** y borra el resto.
Escribe una línea por ejecución en `~/respaldos/respaldo.log` y no imprime nada si va bien, para
que el cron no mande un correo cada día.

La contraseña la lee del `.env` en el momento y la pasa por un archivo temporal, **no por la línea
de comandos**: esto es un servidor compartido y `ps` la dejaría a la vista de los demás.

Se puede lanzar a mano cuando quieras:

```bash
~/aqualive/scripts/respaldar.sh && tail -1 ~/respaldos/respaldo.log
```

### Programarlo

**En esta cuenta no hay `crontab` por SSH**: Hostinger solo deja crear tareas desde hPanel, en
**Avanzado → Trabajos cron**. Los valores:

| Campo | Valor |
|---|---|
| Comando | `/bin/bash /home/u604113341/aqualive/scripts/respaldar.sh` |
| Frecuencia | Personalizada: `0 7 * * *` |

**El servidor va en UTC, no en hora de Aruba.** Las `07:00` UTC son las **03:00** de Aruba, que es
cuando no hay nadie capturando. Si lo pones a las 3 pensando en la hora local, se ejecutaría a las
11 de la noche.

### Restaurar

Un respaldo que nadie ha restaurado nunca es una suposición, no un respaldo. Así se hace:

```bash
cd ~/respaldos
PASS=$(grep "^DB_PASSWORD=" ~/aqualive/.env | sed 's/^DB_PASSWORD=//; s/^"//; s/"$//')

# La base
gunzip -c base-20260904-0553.sql.gz | mysql -u u604113341_aqualiveapp -p"$PASS" u604113341_aqualiveapp

# Las fotos
tar -xzf fotos-20260904-0553.tar.gz -C ~/aqualive
```

El volcado trae `DROP TABLE ... CREATE TABLE`, así que **reemplaza** lo que haya: deja la base tal
como estaba el día del respaldo y se pierde lo capturado desde entonces. Por eso, si el problema
es solo un dato borrado por error, sale más barato mirar el volcado y reponer esa fila que
restaurar entero.

Después de restaurar, limpia las cachés: `php artisan optimize:clear`.

> **Si lo restauras en el XAMPP local**, la primera línea te va a dar
> `ERROR at line 1: Unknown command '\-'`. El servidor tiene MariaDB 11.8 y escribe una línea de
> *sandbox mode* que el cliente más viejo de XAMPP no entiende. **El respaldo está bien**; basta
> saltarse esa línea:
>
> ```bash
> gunzip -c base-20260904-0553.sql.gz | tail -n +2 | mysql -u root prueba_restauracion
> ```
>
> Probado así el 4 de septiembre de 2026: devolvió los 10 usuarios, las 41 jornadas, las 314
> mediciones y los 9 tickets, con los roles tal como estaban.

---

## Lo que queda pendiente después de desplegar

- **Recuperar la contraseña por correo** no existe: se descartó a propósito. Si alguien olvida la
  suya, un administrador se la cambia desde **Usuarios** y queda registrado quién lo hizo.
- **Copias de seguridad de la base.** Hostinger las hace, pero conviene bajar un volcado propio
  cada tanto: el historial de las jornadas no se puede reconstruir.

---

## Este despliegue es una demostración, no la producción de verdad

El sitio de `aqualiveapp.com` está con **`APP_ENV=staging`** y con los
datos del `DemoSeeder`: tres hoteles inventados, 38 jornadas y nueve tickets.

> **Actualizado el 4 de septiembre de 2026.** Desde el equipo ya se estaba probando con esos
> datos —había jornadas nuevas y a `admin1` le habían sumado el rol de reparación—, así que este
> despliegue fue el primero **conservando la base**: respaldo, `migrate` a secas y la migración
> `cambiar_hotel_por_cliente_en_tickets` copiando a cada ticket el nombre y la dirección de su
> hotel antes de soltar la llave foránea. Nada que se hubiera capturado se perdió.

Se puso `staging` y no `production` a propósito: el `DemoSeeder` **se niega a correr en
production**, y aquí hacían falta datos para poder mirar las pantallas y las impresiones. Con
`staging` el guardia sigue puesto para el día que importe, y `APP_DEBUG=false` mantiene los
errores escondidos igual que en producción.

**Cuando esto pase a ser el sitio de verdad** —con el dominio del cliente y datos reales—, hay que:

1. Poner `APP_ENV=production` en el `.env`.
2. Vaciar y volver a sembrar solo lo real:
   ```bash
   php artisan migrate:fresh --force
   php artisan db:seed --force
   ```
   Eso borra los tres hoteles de mentira y las nueve cuentas de prueba, y deja únicamente roles,
   productos, tareas, el hotel del formato en papel y el usuario master.
3. Cargar dirección, teléfono y contacto del hotel desde la pantalla, para el membrete.
4. Volver a cachear: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.

Mientras tanto, **la contraseña `pruebas2026` sirve para nueve cuentas en un sitio público**. No es
grave porque no hay datos reales, pero no dejes esto así el día que los haya.

---

## Volver a la base de trabajo

Durante las pruebas, el `.env` apunta a `control_piscinas_demo`. La base con la que se venía
trabajando sigue intacta en `control_mantenimiento_piscinas`. Para volver a ella:

```
DB_DATABASE=control_mantenimiento_piscinas
```

y después `php artisan config:clear`.

Para rehacer la base de pruebas desde cero en cualquier momento:

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=DemoSeeder
```
