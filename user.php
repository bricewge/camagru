<?PHP

session_start();

$msg = "";
$username="Username";
$email="Email";
$notify = "on";
if (empty($_SESSION["username"])) {
    header("Location: /index.php");
}

require_once("lib/db_interaction.php");
require_once("config/database.php");
$db = db_connect();

$settings = get_settings($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $params = array();
    $query = "UPDATE `users` SET username = :new_username, email = :email, notify_on_comment = :notify ";
    $params[":new_username"] = empty($_POST["username"]) ? $settings["username"] : filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $params[":email"] = empty($_POST["email"]) ? $settings["email"] : filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $params[":notify"] = empty($_POST["notify"]) ? 0 : 1;
    // Test user input
    if ($params[":new_username"] != $settings["username"] and user_exists($db, $params[":new_username"]))
        $msg = "ERROR: The user $username already exists.";
    else if ($params[":email"] != $settings["email"] and email_exists($db, $params[":email"]))
        $msg = "ERROR: The email $email is already in use.";
    else if (!empty($_POST["password"]) and strlen($_POST['password']) < 8)
        $msg = "ERROR: Password is less than 8 characters.";
    else if ($_POST['password'] === $username || $_POST['password'] === $email)
        $msg = "ERROR: Password can't be your username or email.";
    else if (common_password($_POST['password']))
        $msg = "ERROR: Your password is too common.";
    else {
        // Input correct, building query ...

        // if (! empty($_POST["notify"]))
        //   $settings["notify_on_comment"] = $_POST["notify"] == "on" ? 1 : 0;
        // $params[":notify"] = (int) $notify ;

        $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $query .= ", password = :hash ";
        $params[":hash"] = $hash;
        $query .= "WHERE username = :username;";
        $params[":username"] = $_SESSION["username"];
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            // var_dump($query, $params);
            // $stmt->debugDumpParams();
        } catch (PDOException $e) {
            error_log("DB ERROR: ". $e->getMessage());
            die();
        }
        if ($params[":new_username"] != $_SESSION["username"])
            $_SESSION["username"] = $params[":new_username"];
        $settings = get_settings($db);
    }
}

include_once("view/user.html");

function get_settings($db) {
    try {
        $stmt = $db->prepare("SELECT username, email, notify_on_comment FROM `users` WHERE username = :username;");
        $stmt->bindParam(":username", $_SESSION["username"], PDO::PARAM_STR, 255);
        $stmt->execute();
        return  $stmt->fetch(PDO::FETCH_ASSOC);
        // $username = $current["username"];
        // $email = $current["email"];
        // $notify = $current["notify_on_comment"] == 1 ? "on" : NULL;
    } catch (PDOException $e) {
        error_log("DB ERROR: ". $e->getMessage());
        die();
    }
}



?>