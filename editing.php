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
        $msg = "ERROR: You need to select a valid layer AND take a working webcam";
    }
    else if (! in_array($_POST['layer'],
                        array('yoshi', 'mario', 'peach', 'bowser'), true )) {
        $msg = 'ERROR: Your layer is invalid.';
    }
    else {
        $img = str_replace(' ', '+', $_POST['webcamImg']);
        $img = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $img));
        $layer_file = "assets/" .$_POST['layer']. ".png";
        $back = @imagecreatefromstring($img);
        $layer = @imagecreatefrompng($layer_file);
        if ((! $back) || (! $layer)) {
            $msg = "ERROR: Invalid image.";
        }
        else {
            $layer_size = getimagesize($layer_file);
            imagealphablending($layer, true);
            imagesavealpha($layer, true);
            imagecopy($back, $layer, 0, 0, 0, 0, $layer_size[0], $layer_size[1]);
            $image_path = "upload/" . bin2hex(random_bytes(32));
            imagepng($back, $image_path);
            add_image($db, $image_path, $_SESSION['username']);
        }
    }
}
else if ($_POST['action'] === 'upload') {
    // Check if image file is a actual image
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
        $img = $_FILES["image"]["tmp_name"];
        $layer_file = "assets/" .$_POST['layer']. ".png";
        $back = @imagecreatefromstring(@file_get_contents($img));
        $layer = @imagecreatefrompng($layer_file);
        if ((! $back) || (! $layer)) {
            $msg = "ERROR: Invalid image.";
        }
        else {
            $layer_size = getimagesize($layer_file);
            @imagealphablending($layer, true);
            @imagesavealpha($layer, true);
            @imagecopy($back, $layer, 0, 0, 0, 0, $layer_size[0], $layer_size[1]);
            $image_path = "upload/" . bin2hex(random_bytes(32));
            @imagepng($back, $image_path);
            @add_image($db, $image_path, $_SESSION['username']);
        }
        // $image_path = "upload/" . bin2hex(random_bytes(32));
        // move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
        // add_image($db, $image_path, $_SESSION['username']);
    }
}

include_once("view/editing.html");

?>