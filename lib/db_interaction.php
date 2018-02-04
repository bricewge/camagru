<?PHP

function user_exists($db, $username) {
  $query = "SELECT username FROM users WHERE username = :username;";
  $stmt = $db->prepare($query);
  $stmt->bindParam(":username", $username, PDO::PARAM_STR, 255);
  $stmt->execute();
  return !!$stmt->fetch(PDO::FETCH_ASSOC);
};

function email_exists($db, $email) {
  $query = "SELECT email FROM users WHERE email = :email;";
  $stmt = $db->prepare($query);
  $stmt->bindParam(":email", $email, PDO::PARAM_STR, 255);
  $stmt->execute();
  return !!$stmt->fetch(PDO::FETCH_ASSOC);
};

function username_from_token($db, $token) {
  $stmt = $db->prepare("SELECT username FROM emails WHERE token = :token;");
  $stmt->bindParam(":token", $token, PDO::PARAM_STR, 255);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC)['username'];
}

function username_from_email($db, $email) {
  $stmt = $db->prepare("SELECT username FROM users WHERE email = :email;");
  $stmt->bindParam(":email", $email, PDO::PARAM_STR, 255);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC)['username'];
}

function authenticate($db, $username, $password) {
  try {
    $query = "SELECT password, active FROM users WHERE username = :username;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR, 255);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("DB ERROR: ". $e->getMessage());
    die();
  }
  return $result["active"] == 1 and password_verify($_POST["password"], $result["password"]) ? TRUE : FALSE ;
};

function add_email($db, $username, $action) {
  $token = bin2hex(random_bytes(32));
  try {
    $stmt = $db->prepare("INSERT INTO emails(username, token, action)
                        VALUES(:username, :token, '$action')");
    $stmt->bindParam(":username", $username, PDO::PARAM_STR, 255);
    $stmt->bindParam(":token", $token, PDO::PARAM_STR, 255);
    $stmt->execute();
  } catch (PDOException $e) {
    error_log("DB ERROR: ". $e->getMessage());
    die();
  }
  return $token;
};

function get_images($db, $limit) {
  try {
    $query = "SELECT path FROM images LIMIT :limit;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("DB ERROR: ". $e->getMessage());
    die();
  }
  return $result;
}
?>