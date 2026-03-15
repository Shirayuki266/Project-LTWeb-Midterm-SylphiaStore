```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);

/* Logout user */
$auth->logout();

/* Redirect về login */
header('Location: login.php');
exit;
?>
```