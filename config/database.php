<?php
$DB_HOST = "127.0.0.1";
$DB_PORT = "3306";
$DB_ROOT = "root";
$DB_ROOT_PASSWORD = "trololol";

$DB_NAME = "camagru";
$DB_USER = "camagru";
$DB_PASSWORD = "password";

$DB_DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;port=$DB_PORT";
$DB_OPTIONS = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

function db_connect() {
  global $DB_DSN, $DB_USER, $DB_PASSWORD, $DB_OPTIONS;
  return new PDO($DB_DSN, $DB_USER, $DB_PASSWORD, $DB_OPTIONS);
}
?>