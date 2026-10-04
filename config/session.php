```php
<?php

/*
|--------------------------------------------------------------------------
| Persistent PHP Session Handler
|--------------------------------------------------------------------------
| Stores PHP sessions in TiDB/MySQL.
| This allows sessions to survive between different Vercel
| serverless function instances.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/db.php";


class DatabaseSessionHandler implements SessionHandlerInterface
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT session_data
             FROM app_sessions
             WHERE session_id = ?
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "s", $id);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        mysqli_stmt_bind_result($stmt, $sessionData);

        if (mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            return $sessionData;
        }

        mysqli_stmt_close($stmt);

        // New session
        return "";
    }

    public function write(string $id, string $data): bool
    {
        $time = time();

        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO app_sessions
                (session_id, session_data, last_activity)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
                session_data = VALUES(session_data),
                last_activity = VALUES(last_activity)"
        );

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $id,
            $data,
            $time
        );

        $success = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        return $success;
    }

    public function destroy(string $id): bool
    {
        $stmt = mysqli_prepare(
            $this->conn,
            "DELETE FROM app_sessions
             WHERE session_id = ?"
        );

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "s", $id);

        $success = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        return $success;
    }

    public function gc(int $max_lifetime): int|false
    {
        $expire = time() - $max_lifetime;

        $stmt = mysqli_prepare(
            $this->conn,
            "DELETE FROM app_sessions
             WHERE last_activity < ?"
        );

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "i", $expire);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $deleted = mysqli_stmt_affected_rows($stmt);

        mysqli_stmt_close($stmt);

        return $deleted;
    }
}


/*
|--------------------------------------------------------------------------
| SESSION CONFIGURATION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    /*
    | Use one fixed session name for the entire website.
    | Every PHP page must use this same session.
    */
    session_name("CAR_RENTAL_SESSION");


    /*
    | Register database session handler.
    */
    $handler = new DatabaseSessionHandler($conn);

    session_set_save_handler(
        $handler,
        true
    );


    /*
    | Vercel uses HTTPS in production.
    | The cookie should therefore be secure on HTTPS.
    */
    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
            && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');


    /*
    | Session cookie settings.
    */
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);


    /*
    | Start session.
    */
    session_start();
}
