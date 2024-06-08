<?php
function obtenerUsoCPU() {
    $load = sys_getloadavg();
    return $load[0]; // Devuelve la carga de 1 minuto
}

function obtenerUsoMemoria() {
    $meminfo = [];
    foreach (file('/proc/meminfo') as $line) {
        list($key, $val) = explode(":", $line);
        $meminfo[$key] = trim($val);
    }
    $memTotal = filter_var($meminfo['MemTotal'], FILTER_SANITIZE_NUMBER_INT);
    $memDisponible = filter_var($meminfo['MemAvailable'], FILTER_SANITIZE_NUMBER_INT);
    $memUsada = $memTotal - $memDisponible;
    return [
        'total' => $memTotal,
        'usada' => $memUsada,
        'disponible' => $memDisponible,
    ];
}

function obtenerUsoDisco() {
    $disco = [];
    $output = [];
    exec('df -h', $output);
    foreach ($output as $line) {
        if (preg_match('/^\/dev\//', $line)) {
            $parts = preg_split('/\s+/', $line);
            $disco[] = [
                'filesystem' => $parts[0],
                'size' => $parts[1],
                'used' => $parts[2],
                'available' => $parts[3],
                'use%' => $parts[4],
                'mounted_on' => $parts[5]
            ];
        }
    }
    return $disco;
}

$cpu = obtenerUsoCPU();
$memoria = obtenerUsoMemoria();
$disco = obtenerUsoDisco();

echo json_encode([
    'cpu' => $cpu,
    'memoria' => $memoria,
    'disco' => $disco
]);

