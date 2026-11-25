<?php

// Ejecutar dentro de app: php docker/check-assets.php http://nginx
// Comprueba tambien los chunks que Vue carga al abrir el dashboard y otras vistas.
$baseUrl = rtrim($argv[1] ?? 'http://nginx', '/');
$buildPath = dirname(__DIR__).'/public/build/';

try {
    $manifest = json_decode(file_get_contents($buildPath.'manifest.json'), true, flags: JSON_THROW_ON_ERROR);
    if (! is_array($manifest) || $manifest === []) {
        throw new RuntimeException('El manifest de Vite está vacío o no es válido.');
    }

    $files = [];
    foreach ($manifest as $entry) {
        $files[] = $entry['file'];
        array_push($files, ...($entry['css'] ?? []), ...($entry['assets'] ?? []));
    }

    $context = stream_context_create(['http' => ['timeout' => 10, 'follow_location' => 0]]);
    $files = array_unique($files);
    foreach ($files as $file) {
        $served = @file_get_contents($baseUrl.'/build/'.$file, false, $context);
        if ($served === false || ! is_file($buildPath.$file)
            || ! hash_equals(hash_file('sha256', $buildPath.$file), hash('sha256', $served))) {
            throw new RuntimeException('Asset ausente o desincronizado: /build/'.$file);
        }
    }

    echo count($files)." assets verificados entre PHP y Nginx.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage()."\n");
    exit(1);
}
