# Qué se probó, y cómo repetirlo

Barrido completo del **29 de agosto de 2026**, contra la base `control_piscinas_demo` con los datos
del `DemoSeeder`: 3 hoteles, 38 jornadas, 312 mediciones, 9 tickets.

Todo lo de aquí se probó **por HTTP contra el servidor real**, con sesión iniciada y token CSRF,
no llamando a los controladores por dentro. Una prueba que no recorre el camino real no prueba
nada: en este proyecto ya hubo un fallo —el «error de conexión»— que existía en el navegador y no
aparecía en las pruebas porque estas usaban la URL completa y el navegador la corta.

---

## 1. Permisos: 22 rutas × 6 roles

La tabla completa sale correcta. Lo que hay que mirar si algo cambia:

| Ruta | master | admin | colaborador | hotel | jefe | reparación |
|---|---|---|---|---|---|---|
| `/panel` | 200 | 200 | → registro | → diario | → reparaciones | → reparaciones |
| `/registro`, `/jornada/*` | 200 | 200 | 200 | 403 | 403 | 403 |
| `/jornada/*/cambios` | 200 | 200 | **403** | 403 | 403 | 403 |
| `/diario/*`, imprimir | 200 | 200 | **403** | solo el suyo | 403 | 403 |
| `/hoteles`, `/usuarios` | 200 | 200 | 403 | 403 | 403 | 403 |
| `/reparaciones/*` | 200 | **403** | 403 | 403 | 200 | 200 |
| `/perfil` | 200 | 200 | 200 | 200 | 200 | 200 |

Ninguna ruta devolvió 500.

---

## 2. La jornada del colaborador

- Manda a mano `fecha=1999-01-01` → **se guarda la de hoy**. El campo no está en la pantalla y el
  servidor tampoco lo acepta.
- Guarda una medición, marca una tarea, guarda metro y materiales: **200** en los tres.
- La medición **queda a nombre de quien la registró**.

## 3. La corrección no roba la autoría

Cuando otro usuario corrige una medición ya existente:

- El autor de la medición **sigue siendo el primero**.
- La corrección queda en `cambios` **a nombre de quien la hizo**, con el valor de antes y el de
  después.
- La jornada sale marcada en el panel y la pantalla de correcciones muestra hora y autor.

## 4. Reparaciones, el ciclo entero

Crear → por facturar → por cobrar → cobrado, comprobando en cada paso:

- Nace en `por_hacer` y la creación queda en el historial con `estado_anterior` nulo.
- Mover al mismo estado: **422**, sin ensuciar el historial.
- Estado inventado: **422**.
- Al llegar a `cobrado` **sale del tablero** y aparece en el historial.
- El reparador **no puede borrar** (403); el jefe sí (200).
- El historial guardó los 4 pasos.

## 5. La contraseña provisional

- El admin le pone clave a otro → queda marcada como provisional y **se registra quién se la puso**.
- El usuario entra y **no puede ir a ninguna pantalla** hasta elegir la suya.
- Repetir la que le dieron: rechazado.
- Al elegir una propia se levanta la marca y navega normal.

## 6. La impresión

Los tres hoteles, cada uno con su forma (2, 3 y 1 rondas): logo, membrete con dirección y
contacto, tablas, listado de trabajo y pie. El hotel solo imprime **el suyo** (403 en los otros) y
un día sin registro devuelve **404** en vez de una hoja en blanco con membrete.

## 7. Aislamiento y paginación

- Un colaborador **no abre una jornada pasada ajena**: 403.
- Sin filtro: 30 en la página 1 y el resto en la 2.
- Con filtro por empleado: solo salen las suyas.

> El caso «filtro **y** más de una página» se probó el mismo día con 70 jornadas de relleno: la
> página 2 traía 13 filas, todas del empleado filtrado. Con los datos del `DemoSeeder` ningún
> filtro pasa de 30, así que ahí no se puede repetir tal cual.

## 8. El contador en vivo y el diario

- `reparaciones/resumen` cuenta bien los abiertos, responde `no-store` y da **403** al colaborador.
- El diario muestra materiales y trabajos del día, **y su JSON los lleva igual** — son dos caminos
  distintos y hay que probar los dos.

---

## Tres avisos para quien repita esto

Las tres veces que una prueba dio «mal» en este barrido, **el error estaba en la prueba**:

1. **Coger la jornada por el `id` más alto.** El seeder las crea del día de hoy hacia atrás, así
   que el `id` más alto es la **más vieja**. Se agarró una de otro colaborador y los 403 que
   siguieron eran el aislamiento funcionando. Busca por fecha, no por `id`.

2. **Esperar que al guardar cambie el autor.** Si la medición ya existía, guardar es *corregir*:
   el autor no cambia a propósito. Lo que hay que comprobar es que la corrección quedó en
   `cambios`.

3. **Contar nombres de hotel en el HTML.** Aparecen en el desplegable de filtros aunque no haya ni
   una fila. Cuenta dentro de la tabla, no en la página entera.

Y una más, de otro día: **`curl -L` sigue la redirección al login**, así que un acceso rechazado
devuelve 200 y parece que funcionó. Para comprobar permisos, sin `-L`.

---

## Segunda ronda — 4 de septiembre de 2026

Lo de esta ronda se probó contra **el servidor real** (`aqualiveapp.com`) además de en local,
porque ya había gente trabajando con esos datos.

### 9. El cliente del ticket, de hotel a texto libre

- La migración corrió sobre una base **con datos**, reproduciendo el estado del servidor: los
  tickets existentes conservaron nombre y dirección, copiados del hotel que tenían. Ninguno quedó
  sin cliente.
- Después, la **instalación desde cero** también da la tabla correcta: los dos caminos funcionan.
- Ciclo completo en producción: crear con un cliente ajeno a los hoteles → verlo → moverlo hasta
  cobrado → encontrarlo en el historial filtrando por su nombre. El ticket de prueba se borró.
- Usuarios, roles y contraseñas **intactos** tras migrar: 10 cuentas, 11 asignaciones de rol, cero
  cambios de contraseña registrados. Las cinco cuentas probadas siguen entrando con `pruebas2026`.

### 10. Errores que ya no dejan pantalla en blanco

Con sesión iniciada y token vencido, `POST /salir` devuelve **302 al panel** en vez del 419. Un 404
hace lo mismo. Sin sesión, la cadena termina sola en el login — comprobado siguiendo las
redirecciones hasta el formulario de ingreso.

### 11. Anchos de pantalla

Barrido de `scrollWidth > clientWidth` en panel, registro, jornada, medición, diario, hoteles,
hotel, usuarios, perfil, reparaciones, ticket, historial y correcciones, a **280, 320, 360, 390,
412 y 430 px**. Ninguno desborda.

Ahí apareció un fallo que nadie había reportado: **`/hoteles` se estiraba a 418 px en una pantalla
de 360**, por el nombre largo de un hotel en la tabla apilada. Era la causa real del encabezado
«que no encajaba».

En la medición se midió la distancia de cada etiqueta a su campo (6 px) contra la distancia a la
fila siguiente (20-24 px), en todos los anchos: la más corta es siempre la que agrupa.

### 12. El respaldo

- El volcado abre, trae las 21 tablas y cierra bien; el tar lleva las 7 fotos, las mismas que hay
  en disco.
- **Rotación**: con archivos falsos de 20 y de 3 días, borró el viejo y conservó el reciente.
- **Restauración probada de verdad**, en una base local aparte: devolvió los 10 usuarios, 41
  jornadas, 314 mediciones y 9 tickets, con los roles como estaban.

---

## Tercera ronda — 4 de septiembre de 2026, por la tarde

### 13. La sal, en sus dos formas

- **Lectura**: se guardó `3120.50` en una piscina y quedó en `mediciones.sal`.
- **Dosis**: `25` kilos del producto Sal, en `dosis`. Las dos conviven en la misma medición, que
  es justo lo que se pidió: con cuánta empieza y cuánta se echa.
- Aparece en el formulario, en el diario **por HTML y por JSON** (son dos caminos, hay que probar
  los dos), y en la hoja impresa de los **tres** hoteles.

### 14. La hoja impresa, con una columna más

Pasó de 10 a 11 columnas, y eso es lo que más fácil se rompe:

- Cabecera, filas y el `colspan` de la fila de detalle: **11 en los tres hoteles**. Un `colspan`
  desfasado no da error, solo descuadra la hoja en silencio.
- Mide **186 mm** y en impresión hay 190 disponibles (A4 menos los márgenes de 10 mm).
- Ninguna celda corta su contenido, comprobado con `scrollWidth > clientWidth` celda por celda.
  El `3120.50` entra completo.

### 15. La observación editable

- Se edita, se guarda, y el historial encadena las versiones: cada fila lleva quién, cuándo y qué
  decía antes.
- Guardar **lo mismo** devuelve 422 y no ensucia el historial. Pasarse de 2000 caracteres, 422.
  Un `colaborador`, **403**.
- Probado también desde la interfaz en el teléfono: abrir, escribir, guardar y ver la edición
  aparecer abajo.

### 16. Las migraciones, por los dos caminos

- **Sobre una base con datos** (el caso del servidor): tres mediciones existentes conservaron sus
  valores y quedaron con la sal en nulo. El ácido cianúrico pasó a kilos y la Sal entró **de
  última** en el catálogo.
- **Desde cero**: el catálogo queda en el orden correcto y el seeder llena la sal.
- Regresión de permisos por rol: sin cambios.

> Ahí saltó un fallo que solo aparece en el camino desde cero: en una base nueva las migraciones
> corren **antes** que los seeders, así que la tabla de productos está vacía y `max(orden) + 1`
> daba 1. La sal salía **de primera** en la pantalla, delante del ácido muriático. Se arregló
> haciendo que la migración solo inserte el producto si el catálogo ya existe; si no, lo crea el
> seeder en su sitio. **Probar un solo camino no habría enseñado esto.**

---

## Cuarta ronda — 5 de septiembre de 2026

### 17. Los dos estados que cierran sin cobrar

- El tablero sigue con **tres columnas**; el desplegable «Mover a» ofrece los **seis** estados.
- Mover a `visita_realizada` y a `garantia_realizada`: 200, el ticket **sale del tablero** (7 → 5)
  y **entra al historial** (4 → 6). Un estado inventado sigue dando 422.
- El contador de la barra sigue contando **solo los abiertos**: 5, repartidos 1/2/2.
- En el historial cada fila lleva su pastilla, y las tres se distinguen: medido en Lab, el pizarra
  de la visita está a ΔE 43 del verde y a 58 del morado. El contraste del texto blanco pasa AA en
  las tres (5.3, 7.2 y 7.4).
- Permisos sin cambios.

### 18. La fecha del historial, que estaba mal

Prueba decisiva: un ticket cobrado el **20/08**, editar su observación **hoy**.

| | Antes de editar | Después |
|---|---|---|
| `updated_at` en la base | 20/08 | **05/09** |
| Fecha en el historial | 20/08 | **20/08** |
| Posición | 6 de 6 | **6 de 6** |

Con el código anterior habría saltado al primer puesto con fecha de hoy. El filtro por fechas usa
la misma fecha de cierre: agosto devuelve 1 y septiembre 5, que suman las 6.

> El primer intento de esta prueba **no demostraba nada**: se editó un ticket que se acababa de
> cerrar, así que `updated_at` y la fecha de cierre caían en el mismo minuto y las dos consultas
> daban igual. Para que una prueba distinga dos cosas, las dos cosas tienen que ser distintas.

---

## Lo que no se pudo probar aquí

**El botón de copiar la dirección.** El portapapeles exige activación del usuario
(`navigator.userActivation.hasBeenActive`) y el navegador automatizado nunca la tiene: da
`permiso: denied` pase lo que pase. Se comprobó que el fallo es del entorno y no del código —la
API falla igual llamándola directamente—, pero **el camino feliz hay que tocarlo en un teléfono
real**. Si el botón cambia a «Copiada» en verde, funcionó.

Por eso mismo al lado va el enlace a Google Maps, que no depende de permisos. De ese sí se
verificó que la dirección queda bien codificada en la URL y que Google responde 200.

---

## Un aviso más para quien repita esto

**Mandar acentos por la consola de Windows corrompe el JSON.** Una prueba de la observación con
`curl -d '{"observacion":"Se revisó..."}'` guardó `null` y pareció un fallo del código. No lo
era: el shell mutiló la `ó`, el JSON llegó inválido y Laravel lo descartó entero. Escribiendo el
cuerpo en un archivo UTF-8 y mandándolo con `--data-binary @archivo`, los acentos y hasta la raya
`—` pasan intactos.

Es el mismo error que ya estaba anotado para MySQL, en otra puerta. En un proyecto en español,
**una prueba sin acentos no prueba nada**: los datos reales siempre los llevan.
