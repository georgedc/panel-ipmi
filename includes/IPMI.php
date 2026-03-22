<?php
class IPMI {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function encryptPassword($password) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryption_key = base64_decode(ENCRYPTION_KEY);
        $encrypted = openssl_encrypt($password, 'aes-256-cbc', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public static function decryptPassword($encryptedPassword) {
        if (empty($encryptedPassword)) return '';
        try {
            $encryption_key = base64_decode(ENCRYPTION_KEY);
            $decoded = base64_decode($encryptedPassword);
            if (strpos($decoded, '::') === false) return '';
            list($encrypted_data, $iv) = explode('::', $decoded, 2);
            return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
        } catch (Exception $e) { return ''; }
    }

    public function getServer($serverId) {
        return $this->db->fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
    }

    public function checkServerStatus($serverId) {
        $server = $this->getServer($serverId);
        if (!$server) return false;
       
        $password = self::decryptPassword($server['ipmi_password']);

        $command = sprintf(
             'timeout 10 sudo /usr/bin/ipmitool -I lanplus -H %s -p %d -U %s -P %s chassis status 2>&1',
            escapeshellarg($server['ip_address']),
            $server['ipmi_port'],
            escapeshellarg($server['ipmi_username']),
            escapeshellarg($password)
        );

        exec($command, $output, $returnCode);
        
        $status = 'offline';
        if ($returnCode === 0) {
            foreach ($output as $line) {
                if (stripos($line, 'System Power') !== false) {
                    if (preg_match('/:\s+on\b/i', $line)) {
                        $status = 'online';
                    }
                    break;
                }
            }
        }

        $this->db->update('servers', [
            'status' => $status,
            'last_checked' => date('Y-m-d H:i:s'),
            'status_details' => implode("\n", $output)
        ], 'id = ?', [$serverId]);

        return $status === 'online';
    }

    public function getServers($userId = null, $onlyAccessible = true) {
        if ($userId && $onlyAccessible) {
            return $this->db->fetchAll(
                "SELECT s.*, us.access_level FROM servers s
                 JOIN user_servers us ON s.id = us.server_id
                 WHERE us.user_id = ? ORDER BY s.name", [$userId]
            );
        }
        return $this->db->fetchAll("SELECT * FROM servers ORDER BY name");
    }

    public function deleteServer($serverId) {
        return $this->db->delete('servers', 'id = ?', [$serverId]);
    }
}