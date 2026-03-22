<?php


require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/IPMI.php';

function getRealStatus($server) {
    $ipmi_password = IPMI::decryptPassword($server['ipmi_password']);
    $ipmi_port = !empty($server['ipmi_port']) ? (int)$server['ipmi_port'] : 623;

    $command = sprintf(
        'sudo /usr/bin/ipmitool -I lanplus -H %s -p %d -U %s -P %s chassis status 2>&1',
        escapeshellarg($server['ip_address']),
        $ipmi_port,
        escapeshellarg($server['ipmi_username']),
        escapeshellarg($ipmi_password)
    );

    exec($command, $output, $returnCode);
    $status = 'offline';
    $info = implode("\n", $output);

    if ($returnCode === 0 && stripos($info, 'System Power') !== false) {
        if (stripos($info, 'on') !== false) {
            $status = 'online';
        }
    }

    return [$status, $info];
}

$db = Database::getInstance();
$servers = $db->fetchAll("SELECT * FROM servers");

foreach ($servers as $server) {
    list($status, $details) = getRealStatus($server);
    $db->update('servers', [
        'status' => $status,
        'last_checked' => date('Y-m-d H:i:s'),
        'status_details' => $details
    ], 'id = ?', [$server['id']]);
    echo "Updated: {$server['name']} -> {$status}\n";
}
