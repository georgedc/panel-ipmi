<?php

namespace App\Controllers;

use App\Http\Request;
use App\Database\Database;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $db = Database::getInstance();

        $servers = $db->fetchAll(
            'SELECT id, name, client_label, location, status, serial_number, cpu_info, ram_gb, disk_info, switch_port, notes
             FROM servers
             ORDER BY name ASC'
        );

        $total = count($servers);
        $complete = 0;
        $missing = 0;
        foreach ($servers as $s) {
            $hasData = !empty($s['cpu_info']) || !empty($s['ram_gb']) || !empty($s['disk_info']) || !empty($s['serial_number']);
            if ($hasData) {
                $complete++;
            } else {
                $missing++;
            }
        }

        return $this->view('inventory/index', [
            'title' => __('inventory.title'),
            'servers' => $servers,
            'total' => $total,
            'complete' => $complete,
            'missing' => $missing,
        ]);
    }
}
