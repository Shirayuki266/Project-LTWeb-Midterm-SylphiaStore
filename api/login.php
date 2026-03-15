if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$username = $_POST['username'];
$password = $_POST['password'];

$result = $auth->userLogin($username,$password);

if ($result['success']) {
header("Location: index.php");
exit;
} else {
$error = $result['error'];
}
}