# Kanorelim

## Overview
A PHP website for the **Kanorelim** tavern featuring public pages and an admin dashboard.

### Features
- **Public**: menu, events calendar, gallery, contact and reservations
- **Admin**: manage menu items, events, gallery images, reservations, and user accounts

## Folder Structure
```
/              # Public site
├─ index.php
├─ menu.php
├─ evenements.php
├─ galerie.php
├─ contact.php
├─ includes/
│  ├─ config.php       # Database credentials
│  ├─ functions.php
│  ├─ header.php
│  └─ footer.php
├─ assets/
│  ├─ css/
│  ├─ js/
│  └─ images/
└─ admin/
   ├─ index.php
   ├─ login.php
   ├─ menu.php
   ├─ evenements.php
   ├─ galerie.php
   ├─ reservations.php
   ├─ utilisateurs.php
   ├─ install.php
   └─ install_additional_tables.php
```

## Setup
### Prerequisites
- PHP 7.x or higher with PDO
- MySQL database (update `includes/config.php`)

### Installation
1. Clone the repository.
2. Update database settings in `includes/config.php`.
3. (Optional) run installation scripts:
   ```bash
   php admin/install.php
   php admin/install_additional_tables.php
   ```
4. (Optional) create an admin account via `admin/create_admin.php`.

## Usage
Start a local server from the project root:
```bash
php -S localhost:8000
```
Then open [http://localhost:8000/index.php](http://localhost:8000/index.php). The admin dashboard lives at `/admin`.

## License
Released under the [MIT License](LICENSE) © 2025 Samy - Nyx.
