<?PHP

session_start();
$msg = "";

if (empty($_POST['email']) || (! empty($_SESSION["username"]))) {
  include_once("view/forgot.html");
  die();
}

require_once("lib/db_interaction.php");
require_once("config/database.php");
$db = db_connect();

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);

// Verify email
$username = username_from_email($db, $email);
if (empty($username))
  $msg = "ERROR: Wrong email.";
else {
  // Send email with reset link
  $token = add_email($db, $username, "reset");
  $email_content = "To reset your account's password follow this link: http://"  .$_SERVER['SERVER_NAME']. ':' .$_SERVER['SERVER_PORT']. "/reset.php?token=$token";
  mail($email, "Reset your password for your Camagru account", $email_content);
  // Inform that the email has been sent
  $msg = ("To reset your account's password, click on the link on the email you received from us.");
}
include_once("view/forgot.html");

?>
