<?PHP

require_once("database.php");
require_once("lib/db_interaction.php");

// * create database and database's user
try {
  $db_root = new PDO("mysql:host=$DB_HOST;port=$DB_PORT",
                     $DB_ROOT,
                     $DB_ROOT_PASSWORD,
                     $DB_OPTIONS);

  $db_exists = $db_root->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$DB_NAME'");
  if ($db_exists !== 0) {
    $db_root->exec("DROP DATABASE `$DB_NAME`");
  }
  $db_root->exec("CREATE DATABASE `$DB_NAME`;
                CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
                GRANT ALL ON `$DB_NAME`.* TO '$DB_USER'@'localhost';
                FLUSH PRIVILEGES;")
      or die(print_r($db_root->errorInfo(), true));

} catch (PDOException $e) {
  die("DB ERROR: ". $e->getMessage());
}

// * create tables
try {
  $db = db_connect();

// ** users
  $db->exec("CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `notify_on_comment` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`));");
  $db->exec("INSERT INTO users(username, email, password, active)
  VALUES('toto', 'to@t.o', '". password_hash("toto", PASSWORD_BCRYPT) ."', 1);");

// ** emails
  $db->exec("CREATE TABLE `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `action` enum('activate', 'reset') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`));");

// ** images
  $db->exec("CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`));");

// ** likes
  $db->exec("CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `image_id` int(255) NOT NULL,
  PRIMARY KEY (`id`));");

// ** comments
  $db->exec("CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `image_id` int(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `comment_number` int(11) NOT NULL,
  PRIMARY KEY (`id`));");


} catch (PDOException $e) {
  die("DB ERROR: ". $e->getMessage());
}
?>