# Convenciones de código

Estas no son preferencias teóricas: son los patrones que ya se usan en el proyecto.
Si vas a escribir código aquí, síguelos.

---

## La regla que manda sobre todas

Los cambios son **quirúrgicos**: tocar lo mínimo, no romper nada existente, no refactorizar
de paso, no renombrar lo que ya funciona. Preferir agregar antes que modificar.

Si algo del código actual parece mejorable, se comenta aparte. No se arregla dentro de otro
cambio.

---

## Idioma

Todo en **español**: variables, métodos, tablas, columnas, clases CSS, comentarios y mensajes.
En inglés solo lo que impone Laravel.

```php
public function guardar()      // sí
public function save()         // no

$nuevoNoFactura                // sí
$newInvoiceNumber              // no
```

Los comentarios llevan tildes: `sesión`, `próxima`, `cédula`.

### Lo que sí impone Laravel

- `usuarios.password` — `Auth::attempt()` la trata como clave especial.
- `sessions.user_id` — la escribe `DatabaseSessionHandler` con ese nombre.

---

## Formato

### Alineación de `=>`

En todo array de configuración las flechas van alineadas en columna. Si se agrega una clave
más larga, se realinea el bloque completo.

```php
return [
    'nombre'            => ['required', 'string', 'max:45'],
    'numero_documento'  => ['required', 'string', 'max:45'],
    'tipo_documento_id' => ['required', 'integer', 'exists:tipo_documentos,id'],
];
```

### Espaciado

- Indentación de 4 espacios.
- Llave en línea aparte para `class` y métodos; en la misma línea para `if` y `foreach`.
- **2 o 3 líneas en blanco entre métodos**, no una.
- Una línea en blanco después de la `{` del método.

### Comentarios

Cortos y encima de lo que explican. Sin docblocks salvo los que trae Laravel de fábrica.
Si algo necesita explicación larga, que diga **por qué**, no qué.

---

## Base de datos

Tablas y columnas en español y `snake_case`. Tablas en plural.

```php
Schema::create('usuarios', function (Blueprint $table) {
    $table->id();
    $table->string('nombre_usuario',45)->unique();
    $table->boolean('activo')->default(true);

    $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();

    $table->timestamps();

});
```

- `string('nombre',45)` — sin espacio tras la coma, casi siempre con largo explícito.
- Las `foreignId` van agrupadas al final, separadas por una línea en blanco.
- `restrictOnDelete()` por defecto; `nullOnDelete()` cuando la relación es opcional.

### Seeders

Siempre `firstOrCreate`, nunca `create`: deben poder correrse dos veces sin duplicar.

**Nunca credenciales reales en un seeder.** Usuarios de demostración con dominio `.test`, y las
contraseñas salen del `.env`.

---

## Modelos

Delgados: `$fillable`, `$casts` y relaciones. La lógica vive en el controlador.

- **Tipo de retorno siempre**: `: BelongsTo`, `: HasMany`.
- **Llave foránea explícita en `belongsTo`**, aunque Laravel la adivine.
- Al escribir una relación, verificar que la clase destino sea la correcta. Una relación que
  apunta a la clase equivocada no explota si nadie la llama.

---

## Controladores

Orden fijo: `index`, `store`, `show`, `edit`, `update`, `destroy`, y al final los métodos
propios.

### El patrón de update

Se repite igual en todos los controladores:

```php
public function update(UpdateEpsRequest $request, Eps $ep)
{
    $data = $request->validated();

    // PATCH sin data
    if (empty($data)) {
        return response()->json([
            'message' => 'Sin datos'
        ], 422);
    }

    // Cargar datos sin guardar
    $ep->fill($data);

    // No hubo cambios
    if (! $ep->isDirty()) {
        return response()->json([
            'message' => 'No se detectaron cambios'
        ], 422);
    }

    $ep->save();

    return response()->json([
        'message' => 'Actualizado Correctamente',
        'data'    => $ep->fresh()
    ], 200);
}
```

### Rutas

Solo se declaran las rutas que el controlador implementa. Nada de `Route::resource` completo:
deja rutas abiertas apuntando a métodos inexistentes.

---

## Requests

Toda escritura pasa por un FormRequest. Orden de métodos: `authorize`, `rules`,
`prepareForValidation`.

### La traducción camelCase ↔ snake_case

**El navegador manda camelCase, la base guarda snake_case**, y `prepareForValidation` es el
puente. Los dos mundos nunca se mezclan.

```php
protected  function prepareForValidation(): void
{
    $this->merge([
        'nombre_usuario' => $this->nombreUsuario,
        'rol_id'         => $this->rolId,
    ]);
}
```

En el `update` se distingue PUT de PATCH: `required` para PUT, `sometimes` para PATCH. Y en
PATCH solo se agrega al `merge` lo que realmente vino, para no meter nulos.

Los mensajes de error van en español, con `messages()`.

---

## Vistas (Blade)

**No se usa `@extends` ni `@section`.** Se incluyen fragmentos en orden:

```blade
@include('partials.head')      {{-- abre <head>, NO lo cierra --}}
<link rel="stylesheet" href="@recurso('css/usuarios.css')">
<title>Usuarios</title>

@include('partials.header')    {{-- cierra </head> y abre <body> --}}

<div class="contenedor-general">
    <h1 class="vista-titulo">Usuarios</h1>

    @include('partials.mensaje')
</div>

@include('partials.footer')    {{-- cierre --}}
```

`partials/header-limpio` es la variante sin barra superior, para el login.

### Datos hacia el JS

Un `<script>` con constantes globales, y después el archivo externo. Sin módulos, sin
empaquetador.

```blade
<script>
    const rutaUsuarios = '/usuarios';
</script>

<script src="{{ asset('js/usuarios.js') }}"></script>
```

### Mensajes flash

Cuatro claves, siempre las mismas, renderizadas por `partials/mensaje`:

| Clave | Color | Cuándo |
|---|---|---|
| `mensajeCreado` | verde | algo se creó |
| `mensajeActualizado` | verde | algo se actualizó |
| `mensajeAlerta` | amarillo | aviso |
| `error` | rojo | salió mal |

---

## JavaScript

JS plano, un archivo por vista, sin frameworks ni compilación. Se carga al final.

- Los datos llegan por constantes globales que puso el Blade.
- `fetch` con `X-CSRF-TOKEN` leído del `<meta>`.
- Los elementos se buscan por `id`, y los grupos por clase.
- Banners de sección: `// ============ ELIMINAR ============`
- Validación en el cliente; el servidor revalida igual.

### Las rutas del fetch las genera Blade

Las rutas del `fetch` son **absolutas**, nunca relativas. Pero **no se escriben a mano**: las
genera Blade con `url()`.

```blade
<script>
    const rutaUsuarios = '{{ url('/usuarios') }}';
</script>
```

El motivo es concreto. Escribir `'/usuarios'` a mano da una ruta desde la raíz del dominio, y
esta aplicación cuelga de un subdirectorio: la petición se iba a `http://localhost/usuarios`,
que no existe, y el `catch` del JavaScript mostraba «Error de conexión».

`url()` devuelve la ruta correcta esté donde esté montada la aplicación, en local y en
producción, sin tocar el código.

**Al probar una llamada de JavaScript, hay que armar la URL como la arma el navegador.** Este
error estuvo en las siete constantes de ruta del proyecto y no lo detectó ninguna prueba,
porque las pruebas usaban `curl` con la URL completa mientras el navegador usaba la corta. Una
prueba que no reproduce el camino real no prueba nada.

---

## CSS

Un archivo por vista, más `general.css` común. Sin preprocesador, sin variables CSS, sin
framework.

Nombres de clase en español, `kebab-case`, largos y descriptivos. Describen la posición en la
estructura, no el aspecto:

```css
.contenedor-general
.linea-acciones
.elemento-formulario
.titulo-elemento
```


### Componentes compartidos

Si un patrón aparece en dos vistas, vive en `general.css`, no duplicado en cada hoja.

| Clase | Para qué |
|---|---|
| `.linea-titulo` | Título de la vista y su botón principal, en la misma línea. Se apila en móvil. |
| `.fila-historial` | Un registro por línea en escritorio: `.fila-fecha`, `.fila-datos`, `.fila-marca`, `.fila-acciones`. Se usa en el panel y en el registro. |
| `.encabezado-historial` | Nombra las columnas de esas filas. Se oculta en móvil, donde las filas se apilan y cada dato se lee solo. |
| `.contenedor-estrecho` | 620px centrado, para formularios de una columna. |
| `.contenedor-medio` | 900px centrado, para vistas de dos columnas. |
| `.caja-vacia` | El recuadro punteado de «todavía no hay nada». |
| `.caja-tabla` | Tabla que en móvil se apila en tarjetas. **Cada `<td>` necesita su `data-titulo`**, que es lo que se muestra como etiqueta al apilarse. |

Las vistas de lista (hoteles, usuarios, panel, registro) se ven todas igual porque usan las
mismas clases. Antes cada una tenía las suyas y se habían ido separando.

### Los botones dicen a dónde llevan

Nada de «Ver» y «Abrir», que no dicen nada. La misma acción se llama igual en toda la
aplicación: **Diario** lleva a la consulta del hotel, **Editar** abre la pantalla donde se
modifica. Si el texto no alcanza para ser claro, se agrega un `title` que lo explique.

Un número que no se explica solo lleva su `title`: `10 de 10` no dice nada hasta que se sabe
que son 5 piscinas × 2 rondas.

### Todo bloque de datos lleva encabezado

Si en una pantalla hay un dato suelto, el usuario tiene que adivinar de dónde sale. Cada grupo
lleva su nombre: «Pruebas del agua», «Químicos aplicados», «Observación del técnico».

Las columnas de tabla que no son obvias llevan `title`: qué significa «Orden», qué implica que
un usuario esté «Inactivo», qué son las «Rondas» de un hotel.

Cuando una acción tiene una consecuencia que no se ve, se dice antes de que ocurra: «Una
piscina con mediciones registradas no se puede eliminar: se desactiva».

- Colores en hexadecimal, repetidos. La paleta está documentada en la cabecera de `general.css`.
- Efectos con `transform: scale()` y `transition: 0.4s` en los `:hover`.
- Separadores en comentario: `/* ----------Título-------------- */`
- Dos cortes responsive y solo dos: `768px` y `480px`.
- Las hojas se enlazan con `@recurso('css/x.css')`, no con `asset()`: la directiva le pega
  detrás la fecha del archivo para que el navegador no sirva una versión vieja.

---

## Errores ya cometidos

| Error | Qué hacer |
|---|---|
| Mayúsculas en archivos de `public/` | Todo en minúscula. Windows perdona, Linux no. |
| Consecutivos sin transacción | `DB::transaction` + `lockForUpdate()`. Dos usuarios a la vez sacan el mismo número. |
| Contraseñas reales en el seeder | Nunca. Salen del `.env`, y los usuarios de demo usan dominio `.test`. |
| `Route::resource` completo | `->only([...])` con lo que el controlador implementa. |
| IDs escritos a mano en el JS | Buscar por nombre, no por id. Los ids dependen del orden de inserción. |
| Acentos por consola de MySQL | phpMyAdmin, o `--default-character-set=utf8mb4`. La `é` debe ser `C3A9` en `HEX()`. |
| Acentos por la consola al probar con `curl` | El shell de Windows los corrompe y el JSON llega inválido: parece un fallo del código y es de la prueba. Cuerpo en un archivo UTF-8 y `--data-binary @archivo`. En un proyecto en español, una prueba sin acentos no prueba nada. |
| Insertar datos de catálogo en una migración | Las migraciones corren **antes** que los seeders. Con la tabla vacía, un `max(orden) + 1` da 1 y el registro sale de primero. Insertar solo si el catálogo ya existe, y dejar que el seeder lo cree en su sitio en las bases nuevas. |
| `bootstrap/cache/` versionado | Va en `.gitignore`. Las cachés se generan en el servidor. |
| Código muerto de una idea descartada | Si se abandona, se borra completa. |
| Desactivar en vez de borrar «por si acaso» | Mientras el proyecto no esté en producción, lo que sobra se borra. Desactivar es para proteger historia real, no datos de prueba: deja filas muertas que nadie va a mirar. |
| Rutas trampa para «probar» | Nunca. Una ruta que salta la autenticación se olvida y viaja a producción. Para ver otras vistas: usuarios de prueba con dominio `.test`, o la suplantación del `master`, que es una función real y auditada. |
| Editar una migración que ya corrió en un servidor | Ahí no se vuelve a ejecutar: la base se queda con la tabla vieja y la aplicación revienta. Se hace una migración **nueva** que transforme la tabla y **convierta los datos**. Editar la original solo vale mientras la base sea desechable. |
| Suponer que el navegador copia al portapapeles | `navigator.clipboard` pide sitio seguro **y** permiso; si falta alguno falla en silencio. Dejar siempre un camino que no dependa de eso. |

---

## Cómo trabajar

- Un cambio a la vez. No mezclar arreglos independientes en la misma edición.
- Antes de tocar: decir qué archivo y línea cambian, qué podría romperse y cómo se verifica.
- Probar al principio y al final. El resultado debe ser idéntico salvo por lo que se cambió.
- No reformatear lo que no es parte del arreglo.
- Verificar, no suponer.
- Nada de código de más: ni abstracciones para casos que no existen, ni dependencias nuevas
  sin preguntar.

> Cuando algo dependa de un **nombre** —de clase, de archivo, de registro en la base—,
> señálalo. Es donde más se rompe todo en silencio.
