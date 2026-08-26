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
| Hoteles y piscinas | Pendiente |
| Registro de mantenimientos | Pendiente |
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
git clone https://github.com/Jonathan-MB/AdminMAntenimientoPiscinas.git
cd AdminMAntenimientoPiscinas
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

El seeder crea los cuatro roles y el usuario **master**.

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

---

## Estructura

```
app/
  Http/Controllers/    AccesoController, PanelController, UsuarioController
  Http/Middleware/     VerificarRol      (alias 'rol', se usa 'rol:master,administrador')
  Http/Requests/       IniciarSesion, StoreUsuario, UpdateUsuario
  Models/              Rol, Usuario
database/
  migrations/          sessions, cache, jobs, roles, usuarios
  seeders/             RolSeeder, UsuarioMasterSeeder
public/
  css/                 general.css + una hoja por vista
  js/                  un archivo por vista
  img/                 logo, isotipo y derivados
resources/views/
  partials/            head, header, header-limpio, mensaje, footer
  login, panel, usuarios
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

En móvil la tabla de usuarios se apila en tarjetas usando el atributo `data-titulo` de cada
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

## Comandos útiles

```bash
php artisan migrate:fresh --seed   # rehace la base desde cero
php artisan db:seed                # vuelve a sembrar (no duplica)
php artisan optimize:clear         # limpia todas las cachés
php artisan route:list             # rutas declaradas
```

---

## Pendientes

- Definir cuánto dura la ventana en la que un `colaborador` puede corregir lo que acaba de
  guardar. Está decidido que solo puede corregir «en el momento», falta el plazo.
- Vincular el usuario con rol `hotel` a su registro de hotel y sus piscinas.
- Módulo de hoteles y piscinas.
- Módulo de registro de mantenimientos.
