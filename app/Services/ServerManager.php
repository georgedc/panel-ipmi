<?php

namespace App\Services;

use App\Database\Database;
use Exception;
use IPMI;

class ServerManager
{
    public function __construct(
        private ?Database $db = null,
        private ?IPMI $ipmi = null,
        private ?Logger $logger = null,
        private ?Auth $auth = null
    ) {
        $this->db ??= Database::getInstance();
        $this->ipmi ??= new IPMI();
        $this->logger ??= new Logger();
        $this->auth ??= new Auth();
    }

    public function add(array $input): string
    {
        $serverName = trim((string) ($input['server_name'] ?? ''));
        $ipAddress = trim((string) ($input['ip_address'] ?? ''));
        $ipmiUsername = trim((string) ($input['ipmi_username'] ?? ''));
        $ipmiPasswordRaw = (string) ($input['ipmi_password'] ?? '');
        $ipmiType = trim((string) ($input['ipmi_type'] ?? 'generic'));
        $kvmMode = $this->normalizeKvmMode((string) ($input['kvm_mode'] ?? 'html5'));
        $location = trim((string) ($input['location'] ?? ''));
        $tlsFingerprint = normalizeTlsFingerprint((string) ($input['tls_fingerprint'] ?? ''));
        $ipmiPort = !empty($input['ipmi_port']) ? (int) $input['ipmi_port'] : 623;

        $clientLabel = trim((string) ($input['client_label'] ?? ''));

        $serverData = [
            'name' => $serverName,
            'client_label' => ($clientLabel !== '' ? $clientLabel : null),
            'ip_address' => $ipAddress,
            'ipmi_username' => $ipmiUsername,
            'ipmi_password' => $this->ipmi->encryptPassword($ipmiPasswordRaw),
            'ipmi_port' => $ipmiPort,
            'location' => ($location !== '' ? $location : null),
            'ipmi_type' => $ipmiType,
            'kvm_mode' => $kvmMode,
            'status' => 'offline',
            'api_token' => bin2hex(random_bytes(32)),
            'tls_fingerprint' => ($tlsFingerprint !== '' ? $tlsFingerprint : null),
        ];

        $this->db->insert('servers', $serverData);
        return __('servers.added', ['name' => $serverName]);
    }

    public function update(array $input): string
    {
        $serverId = (int) ($input['server_id'] ?? 0);
        $serverName = trim((string) ($input['server_name'] ?? ''));
        $ipAddress = trim((string) ($input['ip_address'] ?? ''));
        $ipmiUsername = trim((string) ($input['ipmi_username'] ?? ''));
        $ipmiPasswordRaw = (string) ($input['ipmi_password'] ?? '');
        $ipmiType = trim((string) ($input['ipmi_type'] ?? 'generic'));
        $kvmMode = $this->normalizeKvmMode((string) ($input['kvm_mode'] ?? 'html5'));
        $location = trim((string) ($input['location'] ?? ''));
        $tlsFingerprint = normalizeTlsFingerprint((string) ($input['tls_fingerprint'] ?? ''));
        $ipmiPort = !empty($input['ipmi_port']) ? (int) $input['ipmi_port'] : 623;

        $clientLabel = trim((string) ($input['client_label'] ?? ''));
        $serialNumber = trim((string) ($input['serial_number'] ?? ''));
        $cpuInfo = trim((string) ($input['cpu_info'] ?? ''));
        $ramGbRaw = $input['ram_gb'] ?? '';
        $ramGb = ($ramGbRaw !== '' && $ramGbRaw !== null) ? (int) $ramGbRaw : null;
        $diskInfo = trim((string) ($input['disk_info'] ?? ''));
        $switchPort = trim((string) ($input['switch_port'] ?? ''));
        $notes = trim((string) ($input['notes'] ?? ''));

        $updateData = [
            'name' => $serverName,
            'client_label' => ($clientLabel !== '' ? $clientLabel : null),
            'ip_address' => $ipAddress,
            'ipmi_username' => $ipmiUsername,
            'ipmi_port' => $ipmiPort,
            'location' => ($location !== '' ? $location : null),
            'ipmi_type' => $ipmiType,
            'kvm_mode' => $kvmMode,
            'tls_fingerprint' => ($tlsFingerprint !== '' ? $tlsFingerprint : null),
            'serial_number' => ($serialNumber !== '' ? $serialNumber : null),
            'cpu_info' => ($cpuInfo !== '' ? $cpuInfo : null),
            'ram_gb' => $ramGb,
            'disk_info' => ($diskInfo !== '' ? $diskInfo : null),
            'switch_port' => ($switchPort !== '' ? $switchPort : null),
            'notes' => ($notes !== '' ? $notes : null),
        ];

        if ($ipmiPasswordRaw !== '') {
            $updateData['ipmi_password'] = $this->ipmi->encryptPassword($ipmiPasswordRaw);
        }

        $this->db->update('servers', $updateData, 'id = ?', [$serverId]);
        return __('servers.updated', ['name' => $serverName]);
    }

    public function rotateApiToken(int $serverId, int $actorUserId): string
    {
        $server = $this->db->fetch('SELECT id, name FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            throw new Exception(__('servers.not_found'));
        }

        $newToken = bin2hex(random_bytes(32));
        $this->db->update('servers', ['api_token' => $newToken], 'id = ?', [$serverId]);
        $this->logger->logActivity($actorUserId, $serverId, 'rotate_api_token', 'Server API token regenerated');

        return __('servers.token_rotated', ['name' => $server['name']]);
    }

    public function delete(int $serverId): string
    {
        $this->ipmi->deleteServer($serverId);
        return __('servers.deleted');
    }

    public function listForUser(?int $userId, bool $restricted = false): array
    {
        return $this->ipmi->getServers($userId, $restricted);
    }

    public function find(int $serverId): ?array
    {
        $server = $this->db->fetch('SELECT * FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            return null;
        }
        $server['kvm_mode'] = $this->normalizeKvmMode((string) ($server['kvm_mode'] ?? 'html5'));
        return $server;
    }

    private function ipmiInterface(array $server): string
    {
        return in_array($server['ipmi_type'] ?? '', ['generic', 'tyan', 'asrock'], true) ? 'lan' : 'lanplus';
    }

    private function buildIpmiCmd(array $server, string $subcommand): string
    {
        $password = IPMI::decryptPassword((string) ($server['ipmi_password'] ?? ''));
        return sprintf(
            'timeout 20 sudo /usr/bin/ipmitool -I %s -H %s -p %d -U %s -P %s %s 2>&1',
            $this->ipmiInterface($server),
            escapeshellarg((string) $server['ip_address']),
            (int) ($server['ipmi_port'] ?? 623),
            escapeshellarg((string) ($server['ipmi_username'] ?? '')),
            escapeshellarg($password),
            $subcommand
        );
    }

    private function normalizeKvmMode(string $kvmMode): string
    {
        return match (trim($kvmMode)) {
            'novnc_legacy', 'vnc_classic' => 'vnc_classic',
            'jnlp_legacy', 'java_classic' => 'java_classic',
            default => 'html5',
        };
    }

    public function powerAction(int $serverId, string $action, int $actorUserId = 0): string
    {
        $server = $this->db->fetch('SELECT * FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            throw new Exception(__('servers.not_found'));
        }

        if (!in_array($action, ['on', 'off', 'reset', 'cycle'], true)) {
            throw new Exception(__('server.invalid_power_action'));
        }

        $command = $this->buildIpmiCmd($server, sprintf('chassis power %s', escapeshellarg($action)));

        exec($command, $output, $returnCode);
        if ($returnCode !== 0) {
            throw new Exception(implode("
", $output) ?: __('server.power_action_failed'));
        }

        $updatedStatus = match ($action) {
            'on' => 'online',
            'off' => 'offline',
            'cycle' => 'online',
            default => (string) ($server['status'] ?? 'offline'),
        };

        $this->db->update('servers', [
            'status' => $updatedStatus,
            'last_checked' => date('Y-m-d H:i:s'),
            'status_details' => implode("
", $output),
        ], 'id = ?', [$serverId]);

        if ($actorUserId > 0) {
            $this->logger->logActivity($actorUserId, $serverId, 'power_' . $action, 'Power action ' . $action . ' executed from MVC');
        }

        return __('server.power_action_success', ['action' => strtoupper($action)]);
    }

    public function getServerIps(int $serverId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM server_ips WHERE server_id = ? ORDER BY id ASC',
            [$serverId]
        );
    }

    public function addServerIp(int $serverId, array $input, int $actorUserId = 0): string
    {
        $ip = trim((string) ($input['ip_address'] ?? ''));
        $netmask = trim((string) ($input['netmask'] ?? ''));
        $gateway = trim((string) ($input['gateway'] ?? ''));
        $rdns = trim((string) ($input['rdns'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));

        if ($ip === '') {
            throw new Exception('IP address is required.');
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new Exception('Invalid IP address format.');
        }

        $server = $this->db->fetch('SELECT id FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            throw new Exception(__('servers.not_found'));
        }

        $this->db->insert('server_ips', [
            'server_id'   => $serverId,
            'ip_address'  => $ip,
            'netmask'     => ($netmask !== '' ? $netmask : null),
            'gateway'     => ($gateway !== '' ? $gateway : null),
            'rdns'        => ($rdns !== '' ? $rdns : null),
            'description' => ($description !== '' ? $description : null),
        ]);

        if ($actorUserId > 0) {
            $this->logger->logActivity($actorUserId, $serverId, 'ip_added', 'IP added: ' . $ip);
        }

        return __('servers.ip_added', ['ip' => $ip]);
    }

    public function deleteServerIp(int $ipId, int $serverId, int $actorUserId = 0): string
    {
        $row = $this->db->fetch('SELECT * FROM server_ips WHERE id = ? AND server_id = ?', [$ipId, $serverId]);
        if (!$row) {
            throw new Exception('IP record not found.');
        }

        $this->db->delete('server_ips', 'id = ?', [$ipId]);

        if ($actorUserId > 0) {
            $this->logger->logActivity($actorUserId, $serverId, 'ip_deleted', 'IP removed: ' . $row['ip_address']);
        }

        return __('servers.ip_deleted', ['ip' => $row['ip_address']]);
    }

    public function setBootDevice(int $serverId, string $device, bool $persistent, int $actorUserId = 0): string
    {
        if (!in_array($device, ['pxe', 'disk', 'cdrom', 'bios'], true)) {
            throw new Exception('Invalid boot device.');
        }

        $server = $this->db->fetch('SELECT * FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            throw new Exception(__('servers.not_found'));
        }

        $sub = $persistent
            ? sprintf('chassis bootdev %s options=persistent', escapeshellarg($device))
            : sprintf('chassis bootdev %s', escapeshellarg($device));

        $command = $this->buildIpmiCmd($server, $sub);
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception(implode("\n", $output) ?: __('server.boot_device_failed'));
        }

        if ($actorUserId > 0) {
            $this->logger->logActivity($actorUserId, $serverId, 'boot_device',
                'Boot device set to ' . $device . ($persistent ? ' (persistent)' : ' (one-time)'));
        }

        return __('server.boot_device_success', ['device' => strtoupper($device)]);
    }

    public function bmcReset(int $serverId, string $type, int $actorUserId = 0): string
    {
        if (!in_array($type, ['cold', 'warm'], true)) {
            throw new Exception('Invalid reset type.');
        }

        $server = $this->db->fetch('SELECT * FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            throw new Exception(__('servers.not_found'));
        }

        $command = $this->buildIpmiCmd($server, 'mc reset ' . $type);
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception(implode("\n", $output) ?: __('server.bmc_reset_failed'));
        }

        if ($actorUserId > 0) {
            $this->logger->logActivity($actorUserId, $serverId, 'bmc_reset',
                'BMC ' . $type . ' reset executed.');
        }

        return __('server.bmc_reset_success');
    }

    public function listIpmiUsers(int $serverId): array
    {
        $server = $this->db->fetch('SELECT * FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            throw new Exception(__('servers.not_found'));
        }

        $command = $this->buildIpmiCmd($server, 'user list 1');
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception(implode("\n", $output) ?: 'Could not list IPMI users.');
        }

        $users = [];
        foreach ($output as $i => $line) {
            if ($i === 0 || trim($line) === '') {
                continue;
            }
            // Split on 2+ spaces or tabs — reliable across BMC vendors
            $parts = preg_split('/[\t ]{2,}/', trim($line));
            if (count($parts) < 2) {
                continue;
            }
            $uid = (int) $parts[0];
            if ($uid <= 0) {
                continue;
            }
            // If the second field is a boolean, the name is empty
            $candidate = $parts[1];
            $name = in_array(strtolower($candidate), ['true', 'false'], true) ? '' : $candidate;
            // Last field is the privilege level (can be multi-word like "NO ACCESS")
            $priv = (string) end($parts);
            $users[] = ['id' => $uid, 'name' => $name, 'priv' => $priv];
        }

        return $users;
    }

    public function createIpmiUser(int $serverId, string $username, string $password, int $privLevel, int $actorUserId = 0): string
    {
        if (!preg_match('/^[a-zA-Z0-9_\-]{1,16}$/', $username)) {
            throw new Exception(__('server.ipmi_user_invalid_name'));
        }
        if (strlen($password) < 6 || strlen($password) > 20) {
            throw new Exception('Password must be 6-20 characters.');
        }
        if (!in_array($privLevel, [2, 3, 4], true)) {
            throw new Exception('Invalid privilege level.');
        }

        $server = $this->db->fetch('SELECT * FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            throw new Exception(__('servers.not_found'));
        }

        $users = $this->listIpmiUsers($serverId);
        $freeSlot = null;
        foreach ($users as $u) {
            if ($u['id'] > 1 && $u['name'] === '') {
                $freeSlot = $u['id'];
                break;
            }
        }

        if ($freeSlot === null) {
            throw new Exception(__('server.ipmi_user_no_slots'));
        }

        $steps = [
            sprintf('user set name %d %s', $freeSlot, escapeshellarg($username)),
            sprintf('user set password %d %s', $freeSlot, escapeshellarg($password)),
            sprintf('user priv %d %d 1', $freeSlot, $privLevel),
            sprintf('user enable %d', $freeSlot),
        ];

        foreach ($steps as $i => $sub) {
            $cmd = $this->buildIpmiCmd($server, $sub);
            exec($cmd, $out, $rc);
            $out = [];
            if ($rc !== 0) {
                throw new Exception('IPMI user setup failed at step ' . ($i + 1) . '.');
            }
        }

        if ($actorUserId > 0) {
            $this->logger->logActivity($actorUserId, $serverId, 'ipmi_user_create',
                'IPMI user created: ' . $username . ' (slot #' . $freeSlot . ')');
        }

        return __('server.ipmi_user_created');
    }

    public function deleteIpmiUser(int $serverId, int $ipmiUserId, int $actorUserId = 0): string
    {
        if ($ipmiUserId <= 1) {
            throw new Exception('Cannot delete the primary IPMI administrator.');
        }

        $server = $this->db->fetch('SELECT * FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            throw new Exception(__('servers.not_found'));
        }

        $cmd = $this->buildIpmiCmd($server, sprintf('user disable %d', $ipmiUserId));
        exec($cmd, $out, $rc);
        if ($rc !== 0) {
            throw new Exception('Failed to disable IPMI user: ' . implode("\n", $out));
        }

        // Clear username (best-effort; some BMCs may not support empty name)
        $cmd = $this->buildIpmiCmd($server, sprintf('user set name %d %s', $ipmiUserId, escapeshellarg('')));
        exec($cmd);

        if ($actorUserId > 0) {
            $this->logger->logActivity($actorUserId, $serverId, 'ipmi_user_delete',
                'IPMI user slot #' . $ipmiUserId . ' disabled and cleared.');
        }

        return __('server.ipmi_user_deleted');
    }
}
