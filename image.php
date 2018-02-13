<?PHP

session_start();

require_once("config/database.php");
require_once("lib/db_interaction.php");
$db = db_connect();

$image = '';
$actions = '';
$comments = '';
$msg = '';

// * INPUTS
// ** image_id
$image_id = filter_input(INPUT_GET, 'image', FILTER_SANITIZE_STRING);
if ((! $image_id))
    header("Location: /index.php");
if (! image_exists($db, $image_id))
    header("Location: /index.php");

// ** like/unlike
if (! empty($_SESSION['username'])) {
    if (!empty($_POST['like']) && $_POST['like'] === "true" && ! user_likes_image($db, $_SESSION['username'], $image_id)) {
        try {
            $stmt = $db->prepare("INSERT INTO likes (username, image_id) VALUES(:username, :image_id);");
            $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR, 255);
            $stmt->bindParam(':image_id', $image_id, PDO::PARAM_STR, 255);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("DB ERROR: ". $e->getMessage());
        }
    }
    else if (! empty($_POST['like']) && $_POST['like'] === "false") {
        try {
            $stmt = $db->prepare("DELETE FROM likes WHERE username = :username AND image_id = :image_id;");
            $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR, 255);
            $stmt->bindParam(':image_id', $image_id, PDO::PARAM_STR, 255);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("DB ERROR: ". $e->getMessage());
        }
    }

// ** delete image
    if (! empty($_POST['delete']) && $_POST['delete'] === "true" &&
        image_owner($db, $image_id) === $_SESSION['username']) {
        $path = "upload/" . $image_id;
        try {
            $stmt = $db->prepare("DELETE FROM images WHERE path = :path;
                                  DELETE FROM comments WHERE image_id = :image_id;
                                  DELETE FROM likes WHERE image_id = :image_id;");
            $stmt->bindParam(':path', $path, PDO::PARAM_STR, 255);
            $stmt->bindParam(':image_id', $image_id, PDO::PARAM_STR, 255);
            $stmt->execute();
            $stmt->closeCursor();
            $stmt->execute();
            $stmt->closeCursor();
            $stmt->execute();
            $stmt->closeCursor();
        } catch (PDOException $e) {
            error_log("DB ERROR: ". $e->getMessage());
        }
        unlink($path);
        $msg = "Image deleted.";
        header("Location: /index.php");
    }

// ** add comment
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
    if ($comment) {
        try {
            $stmt = $db->prepare("INSERT INTO comments (username, image_id, content) VALUES(:username, :image_id, :content);");
            $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR, 255);
            $stmt->bindParam(':image_id', $image_id, PDO::PARAM_STR, 255);
            $stmt->bindParam(':content', $comment, PDO::PARAM_STR, 255);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("DB ERROR: ". $e->getMessage());
        }
    }
}

// * DISPLAY
// ** image
if (! image_exists($db, $_GET['image'])) {
    $msg = "The image you are looking for doesn't exists.";
    include_once("view/image.html");
    die();
}
else {
    $image = "<img src='/upload/". $image_id ."'>";
}

// ** like/unlike
if (! empty($_SESSION['username'])) {
    // Likes
    if (user_likes_image($db, $_SESSION['username'], $image_id))
    $actions .= '<form action="" method="post"><button type="submit" name="like" value="false">Unlike</button></form>';
    else
        $actions .= '<form action="" method="post"><button type="submit" name="like" value="true">Like</button></form>';

// ** delete image
    if (image_owner($db, $image_id) === $_SESSION['username']) {
        $actions .= '<form action="" method="post"><button type="submit" name="delete" value="true">Delete your image</button></form>';
    }
// ** comments
    foreach(get_comments($db, $image_id) as $comment) {
        $comments .= "<p>". $comment['username'] .": ". $comment['content'] ."</p>";
    }
// ** add comments
    $comments .= '<form action="" method="post"><textarea name="comment">Spread your salt...</textarea><button type="submit">Submit</button></form>';
}
include_once("view/image.html");
?>