<?php
//
//  Arma el paquete del libro de marca que se le entrega al cliente:
//  el manual en PDF y los logos sueltos, en un ZIP.
//
//  Del manual del repositorio se quitan dos cosas que solo le sirven a
//  quien programa: la seccion "Codigo" y la tabla de archivos con sus
//  rutas de public/. Por eso esto es un script y no un ZIP armado a
//  mano: hay una sola fuente —el manual del repositorio— y el entregable
//  se deriva de ella cada vez.
//
//  El PDF lo genera Chrome o Edge en modo headless, que es lo que hay en
//  esta maquina. Se fuerza el tema claro: el manual se pone oscuro si el
//  sistema lo pide, y un PDF oscuro se imprime pesimo.
//
//  Uso:  php -d extension=zip scripts/armar-libro-de-marca.php [destino.zip]
//

$raiz    = dirname(__DIR__);
$origen  = $raiz . '/Libro de marca';
$destino = $argv[1] ?? $raiz . '/AQUALIVE - Libro de marca.zip';

$navegadores = [
    'C:\Program Files\Google\Chrome\Application\chrome.exe',
    'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
    'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
];

$navegador = null;

foreach ($navegadores as $ruta) {
    if (file_exists($ruta)) {
        $navegador = $ruta;
        break;
    }
}

if ($navegador === null) {
    fwrite(STDERR, "No encuentro Chrome ni Edge, y hacen falta para el PDF.\n");
    exit(1);
}

$manual = file_get_contents($origen . '/manual-de-marca-aqualive.html');


//  ---------- Quitar lo que es para programadores ----------

//  La seccion "Codigo": donde vive la paleta, los cortes responsivos...
$inicio = strpos($manual, '<p class="eyebrow">Código</p>');

if ($inicio !== false) {
    $inicio = strrpos(substr($manual, 0, $inicio), '<section');
    $fin    = strpos($manual, '</section>', $inicio) + strlen('</section>');
    $manual = substr($manual, 0, $inicio) . substr($manual, $fin);
}

//  La tabla de archivos con sus rutas de public/, dentro de "Reglas de uso"
$inicio = strpos($manual, '<h3>Archivos generados</h3>');

if ($inicio !== false) {
    $fin    = strpos($manual, '</p>', strpos($manual, 'distingue mayúsculas')) + strlen('</p>');
    $manual = substr($manual, 0, $inicio) . substr($manual, $fin);
}


//  ---------- Preparar para imprimir ----------

//  Tema claro a la fuerza, y que Chrome no se coma los fondos de color
$manual = preg_replace('/<html\b([^>]*)>/i', '<html$1 data-theme="light">', $manual, 1);

$paraImprimir = <<<CSS
<style>
    @page { size: A4; margin: 14mm 12mm; }

    html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    /*  Que una ficha de color o una tabla no se parta entre dos hojas  */
    section, table, .caja-tabla, .ficha-color { break-inside: avoid; }
    h2, h3 { break-after: avoid; }
</style>
CSS;

$manual = str_replace('</head>', $paraImprimir . "\n</head>", $manual);


//  ---------- El PDF ----------

$temporal = sys_get_temp_dir() . '/libro-marca-' . getmypid();
@mkdir($temporal);

$html = $temporal . '/manual.html';
$pdf  = $temporal . '/Libro de marca AQUALIVE.pdf';

file_put_contents($html, $manual);

$orden = sprintf(
    '"%s" --headless --disable-gpu --no-pdf-header-footer --print-to-pdf="%s" "%s" 2>&1',
    $navegador,
    $pdf,
    'file:///' . str_replace('\\', '/', $html)
);

exec($orden, $salida, $estado);

if (! file_exists($pdf) || filesize($pdf) < 10000) {
    fwrite(STDERR, "El PDF no se genero. Salida del navegador:\n" . implode("\n", $salida) . "\n");
    exit(1);
}

echo 'PDF generado: ' . round(filesize($pdf) / 1024) . " KB\n";


//  ---------- El ZIP ----------

if (file_exists($destino)) {
    unlink($destino);
}

$zip = new ZipArchive();

if ($zip->open($destino, ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "No pude crear el zip\n");
    exit(1);
}

$carpeta = 'AQUALIVE - Libro de marca';
$zip->addFile($pdf, $carpeta . '/Libro de marca AQUALIVE.pdf');

$archivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($origen, FilesystemIterator::SKIP_DOTS));

foreach ($archivos as $archivo) {
    $relativa = str_replace('\\', '/', substr($archivo->getPathname(), strlen($origen) + 1));

    //  El manual va en PDF, y los ocultos no van
    if ($relativa === 'manual-de-marca-aqualive.html' || str_starts_with(basename($relativa), '.')) {
        continue;
    }

    $zip->addFile($archivo->getPathname(), $carpeta . '/' . $relativa);
}

$total = $zip->numFiles;
$zip->close();

array_map('unlink', glob($temporal . '/*'));
rmdir($temporal);

echo "Listo: $destino\n";
echo '  ' . $total . ' archivos, ' . round(filesize($destino) / 1024) . " KB\n";
