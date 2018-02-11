<?PHP

session_start();

$msg = "";
if (! empty($_GET["logout"]))
  unset($_SESSION["username"]);
if (empty($_POST["action"])) {
  include_once("view/login.html");
  die();
}

require_once("lib/db_interaction.php");
require_once("config/database.php");
$db = db_connect();

$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if ($_POST['action'] === "login") {
  if (empty($username) or empty($_POST['password']))
    $msg = "ERROR: The fields aren't all filled.";
  else if (!authenticate($db, $username, $_POST['password']))
    $msg = "Bad login.";
  else {
    $_SESSION["username"] = $username;
    echo ("<script>window.location.href='/';</script>");
  }
}
else if ($_POST['action'] === "register") {
  if (empty($username) or empty($_POST['email']) or empty($_POST['password']))
    $msg = "ERROR: The fields aren't all filled.";
  else if (strlen($_POST['password']) < 8)
    $msg = "ERROR: Password is less than 8 characters.";
  else if (user_exists($db, $username))
    $msg = "ERROR: The user $username already exists.";
  else if (email_exists($db, $email))
    $msg = "ERROR: The email $email is already in use.";
  else {
    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    try {
      // Add user to database
      $stmt = $db->prepare("INSERT INTO users(username, email, password)
                        VALUES(:username, :email, :hash)");
      $stmt->bindParam(":username", $username, PDO::PARAM_STR, 255);
      $stmt->bindParam(":email", $email, PDO::PARAM_STR, 255);
      $stmt->bindParam(":hash", $hash, PDO::PARAM_STR, 255);
      $stmt->execute();
    } catch (PDOException $e) {
      error_log("DB ERROR: ". $e->getMessage());
      die();
    }
    // Add activation email to database
    $token = add_email($db, $username, "activate");
    // Send email
    $email_content = "To activate your account click on the following link: http://localhost:8000/activate.php?token=$token";
    mail($email, "Active your Camagru account", $email_content);
    $msg = ("To activate your account, click on the link on the email you received from us.");
  }
}

include_once("view/login.html");
?>