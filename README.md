# Drive-Me / CarRental

## Run with XAMPP
1. Copy this `car_rental` folder to `C:\xampp\htdocs\car_rental`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin: `http://localhost/phpmyadmin/`.
4. Create database `car_rental_db`.
5. Import `car_rental_db.sql`.
6. Open `http://localhost/car_rental/`.

## Admin
Open `http://localhost/car_rental/admin/login.php`.
All admin pages use the same session configuration in `admin/session.php`, including sorting/filtering pages.

## Images
- `images/car-bg.jpg` is the functional hero background.
- `images/home-design.png` is the generated premium landing-page design supplied with this upgraded package and is used as a decorative hero layer.

## Online hosting
The PHP project can be hosted on a PHP-compatible server. The MySQL database must be hosted online separately. Set DB_HOST, DB_PORT, DB_USER, DB_PASSWORD and DB_NAME using the environment/configuration method supported by your host.
