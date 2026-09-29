# BoutiqueTrack — XAMPP setup

BoutiqueTrack is a single-shop retail inventory and sales system. It uses PHP 8, MySQL/MariaDB, and server-rendered pages so it can run on the local XAMPP server without a constant internet connection.

## Install on Windows with XAMPP

1. Install XAMPP with PHP 8.1 or newer and MySQL/MariaDB.
2. Copy the complete `boutiquetrack` folder into `C:\xampp\htdocs\`.
3. Start **Apache** and **MySQL** in the XAMPP Control Panel.
4. Open phpMyAdmin at `http://localhost/phpmyadmin` and create an empty database named `boutique_track`.
5. If your MySQL username or password differs from the XAMPP defaults, update `config/database.php`.
6. Open `http://localhost/boutiquetrack/`. The first-run page creates the database tables and your administrator account.
7. Optionally import `database/demo-data.sql` into `boutique_track` to try the sample boutique products. Do this before adding real products.

There is no shared default account. The first administrator chooses their own username and password. Add cashier accounts from **Team access** after signing in.

## First-run database settings

`config/database.php` defaults to the usual local XAMPP settings:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `boutique_track`
- Username: `root`
- Password: empty

Change these values in `config/database.php` if your local MySQL setup uses different credentials. Keep this file private if you add a real database password.

## Included modules

- Administrator and cashier sign-in with role-based access
- Product catalogue with separate size/color SKUs, cost, selling price, and reorder levels
- Point of sale with a cart, discounts, cash/other payment recording, and printable receipts
- Stock adjustments with a reason and movement history; completed sales deduct stock in a transaction
- Dashboard, sales history, daily/weekly/monthly reports, product performance, and CSV export
- Team account management for an administrator

## Local development preview

The Replit preview uses a local MariaDB database and sample products. It does not share data with your XAMPP installation. The XAMPP copy stores its data in the MySQL service on that computer.

## Important notes

- This first version is designed for one boutique on one local server; it does not include multi-branch or multiple-shop accounts.
- Back up the `boutique_track` database regularly using phpMyAdmin's **Export** option.
- Keep Apache and MySQL running while staff use the local installation. Other computers on the same network can access it through the host computer's local IP if Windows Firewall permits it.