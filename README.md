# IPMI Control Panel

A web-based IPMI management panel built in PHP. Manage your bare-metal servers remotely — power control, KVM console, ISO mounting, user access, and more.

---

## Features

- **Power management** — On, Off, Reset, Cycle per server or in bulk
- **KVM console** — Browser-based console via noVNC
- **ISO mounting** — Mount/unmount ISO images remotely
- **User management** — Role-based access (admin / user) with per-server permissions
- **IPMI user management** — Create and delete IPMI users directly from the panel
- **Boot device** — Set next boot device (PXE, disk, cdrom, BIOS)
- **BMC reset** — Warm and cold BMC reset
- **Server monitoring** — Automatic online/offline status checks
- **Activity logs** — Full audit trail of all actions
- **WHMCS integration** — REST endpoint for WHMCS module (power actions, SSO console, ISO management)
- **Two-factor authentication** — Optional 2FA for admin accounts
- **Hardware inventory** — Track serial numbers, CPU, RAM, disk and switch port per server
- **Multi-theme support** — Swap themes via `.env` without touching code

---

## Requirements

### Hardware (VPS/Server)

Minimum recommended:
- **1 vCPU** — 2+ vCPU recommended if monitoring more than 20 servers
- **512 MB RAM** — 1 GB+ recommended
- **10 GB disk** — for OS, panel, logs and backups

> The monitoring cron launches one `ipmitool` process per server every 5 minutes. Resource usage scales with the number of servers being monitored.

### Operating System

Linux only. Tested on:
- CentOS / RHEL 8+
- Rocky Linux 8+
- AlmaLinux 8+
- Ubuntu 20.04+
- Debian 11+



- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- `ipmitool` installed on the server
- Composer

---

## Installation

```bash
# 1. Clone the repo
git clone https://github.com/georgedc/panel-ipmi.git
cd panel-ipmi

# 2. Install dependencies
composer install

# 3. Create the database
mysql -u root -p -e "CREATE DATABASE ipmi_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p ipmi_panel < database.sql

# 4. Configure environment
cp .env.example .env
# Edit .env with your database credentials and settings

# 5. Set the encryption key in Apache (add to your VirtualHost)
# SetEnv ENCRYPTION_KEY "base64_encoded_32_byte_key"

# 6. Set permissions
chown -R apache:apache storage/ includes/logs/ includes/uploads/
```

---

## Configuration

Copy `.env.example` to `.env` and fill in your values:

```env
DB_HOST=localhost
DB_NAME=ipmi_panel
DB_USER=your_db_user
DB_PASS=your_db_password

APP_URL=https://your-domain.com/ipmi-panel

WHMCS_SSO_SECRET=your_random_secret
WHMCS_ORIGIN=https://your-whmcs-domain.com
```

The `ENCRYPTION_KEY` must be set as an Apache environment variable (not in `.env`) for security:

```apache
SetEnv ENCRYPTION_KEY "your_base64_encoded_key"
```

Generate a key:
```bash
php -r "echo base64_encode(random_bytes(32)) . PHP_EOL;"
```

---

## Default credentials

After running `database.sql`:

| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `Admin1234!` |

**Change the password immediately after first login.**

---

## Architecture

```
ipmi-panel/
├── api/              # External endpoints (WHMCS, KVM proxy)
├── app/
│   ├── Controllers/  # HTTP layer — receives requests, returns responses
│   ├── Services/     # Business logic — IPMI, KVM, ISO, Auth, SSO
│   ├── Repositories/ # Database access — SQL queries
│   ├── Http/         # Router, Request, Response, CSRF
│   └── Views/        # Templates (theme-based)
├── bootstrap/        # Application bootstrap
├── cron/             # Scheduled tasks
├── includes/         # Legacy classes (shared with api/ endpoints)
├── routes/           # Route definitions
├── themes/           # CSS, JS, images per theme
└── database.sql      # Database schema + default data
```

---

## Cron jobs

```bash
# Update server online/offline status every 5 minutes
*/5 * * * * php /var/www/html/ipmi-panel/cron/update_server_status.php

# Clean up rate limit table daily
0 3 * * * php /var/www/html/ipmi-panel/cron/cleanup_rate_limits.php
```

---

## Supported IPMI types

| Type | Interface |
|------|-----------|
| Supermicro | `lanplus` |
| Dell iDRAC | `lanplus` |
| HP iLO | `lanplus` |
| Generic | `lan` |
| ASRock | `lan` |
| TYAN / AMI MegaRAC | `lan` |

---

## Contributing

Pull requests are welcome. For major changes please open an issue first to discuss what you would like to change.

---

## License

This project is open source. No license has been defined yet — contributions and feedback welcome.

---

## Roadmap

Features planned or in progress. Contributions welcome.

### High priority

- **Automatic OS reinstall** — Trigger a bare-metal reinstall from the panel: select OS, set hostname/IP, confirm and execute. Planned via PXE boot + preseed/kickstart integration.
- **KVM console for all server types** — Currently only Supermicro has a real IPMI probe. Dell iDRAC, HP iLO, ASRock and generic servers need full KVM implementation.
- **websockify integration** — Set up websockify as a WebSocket-to-TCP proxy so noVNC can connect to VNC ports on the IPMI BMC without exposing them directly.
- **Secure WebSocket (wss://)** — KVM connections should use `wss://` when the panel runs on HTTPS.

### Medium priority

- **2FA for regular users** — Two-factor authentication is currently available for admin accounts only.
- **Server power history** — Graph of power state changes over time per server.
- **IPMI sensor data** — Display temperature, fan speed, voltage readings from `ipmitool sdr`.
- **Email alerts** — Notify when a server goes offline or a sensor threshold is exceeded.
- **API token per user** — Currently API tokens are per server. Per-user tokens would allow more granular integrations.

### Low priority / ideas

- **Dark mode** — Theme toggle without changing `APP_THEME` in `.env`.
- **Mobile app** — Companion app for quick power actions from phone.
- **Multi-language UI** — English and Spanish are partially supported. Full i18n coverage needed.
