<?PHP

if (empty($_GET['token'])) {
  die("ERROR: Empty token.");
}

require_once("lib/db_interaction.php");
require_once("config/database.php");
$db = db_connect();

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
try {
  // Get username
  $username = username_from_token($db, $token);
  if (empty($username))
    die("ERROR: Invalid token.");
  // Delete email for database
  $stmt = $db->prepare("DELETE FROM emails WHERE token = :token;");
  $stmt->bindParam(":token", $token, PDO::PARAM_STR, 255);
  $stmt->execute();
  // Activate user acount
  $stmt = $db->prepare("UPDATE users SET active = 1 WHERE username = :username");
  $stmt->bindParam(":username", $username, PDO::PARAM_STR, 255);
  $stmt->execute();
  // Redirect to login page
  header("Location: /login.php");
} catch (PDOException $e) {
  die("DB ERROR: ". $e->getMessage());
}

?>