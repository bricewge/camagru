<?PHP

session_start();

require_once("config/database.php");
require_once("lib/db_interaction.php");
$db = db_connect();


if (! empty($_SESSION['username']))
  $take_picture = '<h2 id="take_picture"><a href="/editing.php">Take a new picture!</a></h2>';
else
  $take_picture = "";


$page = 1;
if(!empty($_GET['page'])) {
  $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array("options" => array("min_range"=>1)));
  if(false === $page) {
    $page = 1;
  }
}

$items_per_page = 5;
$offset = ($page - 1) * $items_per_page;

// Get numbers of images
$stmt = $db->prepare("SELECT * FROM images");
$stmt->execute();
$row_count = $stmt->rowCount();

$page_count = 0;
if ($row_count !== 0) {
  $page_count = (int)ceil($row_count / $items_per_page);
  if($page > $page_count) {
    header("Location: /index.php");
  }
}

$images = "";
foreach(get_images($db, $offset, $items_per_page) as $image) {
  $images .= "<a href='/image.php?image=". basename($image['path']) ."'><img src='". $image['path'] ."' alt='image'></a>";
}

// Page navigation links
$prev = '<p>Previous</p>';
if ($page > 1) {
  $prev_page = $page -1;
  $prev = "<a href='/index.php?page=$prev_page'>" . $prev . '</a>';
}
$next = '<p>Next</p>';
if ($page < $page_count) {
  $next_page = $page +1;
  $next = "<a href='/index.php?page=$next_page'>" . $next . '</a>';
}

include_once("view/index.html");
?>