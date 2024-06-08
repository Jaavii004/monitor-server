<?php
function obtenerUsoCPU() {
    $cpu_count = 1;
    if (is_file('/proc/cpuinfo')) {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuinfo, $matches);
        $cpu_count = count($matches[0]);
    }
    $sys_load = sys_getloadavg();
    $load = $sys_load[0] / $cpu_count;
    return round($load * 100, 2); // Devuelve la carga de 1 minuto como porcentaje
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

function obtenerUsoRed() {
    $output = [];
    exec('cat /proc/net/dev', $output);
    $interfaces = [];
    foreach ($output as $line) {
        if (preg_match('/^\s*(\w+):\s*(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $line, $matches)) {
            $interfaces[$matches[1]] = [
                'rx_bytes' => $matches[2],
                'tx_bytes' => $matches[10]
            ];
        }
    }
    return $interfaces;
}

$cpu = obtenerUsoCPU();
$memoria = obtenerUsoMemoria();
$disco = obtenerUsoDisco();
$red = obtenerUsoRed();

echo json_encode([
    'cpu' => $cpu,
    'memoria' => $memoria,
    'disco' => $disco,
    'red' => $red
]);
?>
