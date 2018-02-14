<?PHP

session_start();

if (empty($_SESSION['username']))
  header("Location: /");

require_once("lib/db_interaction.php");
require_once("config/database.php");
$db = db_connect();

$msg = "";
if (empty($_POST['action'])) {
  include_once("view/editing.html");
  die();
}
else if ($_POST['action'] === 'webcam') {
    if (empty($_POST['layer'] || empty($_POST['webcamImg']))) {
        $msg = "ERROR: you nedd to select a valid layer AND take a working webcam";
        include_once("view/editing.html");
        die();
    }
    if (! in_array($_POST['layer'],
                 array('yoshi', 'mario', 'peach', 'bowser'), true )) {
        $msg = 'ERROR: Your layer is invalid.';
        include_once("view/editing.html");
        die();
    }
    $img = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['webcamImg']));
    file_put_contents("upload/webcam.png", $img);
    // TODO Error if not an image
    $back = imagecreatefromstring(file_get_contents("upload/webcam.png"));
    $layer = imagecreatefromstring(file_get_contents("assets/" .$_POST['layer']. ".png"));
    $layer_size = getimagesize("assets/" .$_POST['layer']. ".png");
    imagealphablending($layer, true);
    imagesavealpha($layer, true);
    imagecopy($back, $layer, 0, 0, 0, 0, $src_size[0], $src_size[1]);
    imagepng($back, 'upload/test.png');
}

// Check if image file is a actual image
if ($_POST['action'] === 'upload') {
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