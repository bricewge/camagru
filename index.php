<?PHP

session_start();

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  include_once("view/index.html");
  die();
}

?>