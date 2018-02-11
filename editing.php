<?PHP

session_start();

if (empty($_SESSION['username']))
  header("Location: /");

require_once("lib/db_interaction.php");
require_once("config/database.php");
$db = db_connect();

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  include_once("view/editing.html");
  die();
}


// Check if image file is a actual image
if(! empty($_FILES["image"])) {
  if (! @getimagesize($_FILES["image"]["tmp_name"])) {
    $msg = "ERROR: File is not an image.";
  }
  else if ($_FILES["image"]["size"] > 5000000) {
    $msg = "ERROR: Image is too large.";
  }
  else if (exif_imagetype($_FILES["image"]["tmp_name"]) <=  IMAGETYPE_GIF &&
           exif_imagetype($_FILES["image"]["tmp_name"]) >= IMAGETYPE_PNG) {
    $msg = "ERROR: Only JPG, PNG & GIF files are allowed.";
  }
  else {
    $image_path = "upload/" . bin2hex(random_bytes(32));
    move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    try  {
      $stmt = $db->prepare("INSERT INTO images(username, path) VALUES(:username, :path);");
      $stmt->bindParam(":username", $_SESSION['username'], PDO::PARAM_STR, 255);
      $stmt->bindParam(":path", $image_path, PDO::PARAM_STR, 255);
      $stmt->execute();
    } catch (PDOException $e) {
      error_log("DB ERROR: ". $e->getMessage());
      die();
    }
  }
}

include_once("view/editing.html");

?>