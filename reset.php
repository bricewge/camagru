<?PHP


$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  include_once("view/reset.html");
  die();
}

require_once("lib/db_interaction.php");
require_once("config/database.php");
$db = db_connect();

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
try {
  // Verify token
  $stmt = $db->prepare("SELECT username FROM emails WHERE token = :token;");
  $stmt->bindParam(":token", $token, PDO::PARAM_STR, 255);
  $stmt->execute();
  $username = $stmt->fetch(PDO::FETCH_ASSOC)['username'];
  if (empty($username)) {
    $msg = "ERROR: Invalid token.";
    die();
  }
  // Delete email for database
  $stmt = $db->prepare("DELETE FROM emails WHERE token = :token;");
  $stmt->bindParam(":token", $token, PDO::PARAM_STR, 255);
  $stmt->execute();
  // Modify password
  $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
  $stmt = $db->prepare("UPDATE users SET password = :hash WHERE username = :username");
  $stmt->bindParam(":hash", $hash, PDO::PARAM_STR, 255);
  $stmt->bindParam(":username", $username, PDO::PARAM_STR, 255);
  $stmt->execute();
} catch (PDOException $e) {
  error_log("DB ERROR: ". $e->getMessage());
  die();
}

header("Location: /login.php");
?>