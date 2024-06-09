<?php
include("./config.php");
//  $db = new db();
//     $con = $db->connect();

$con = mysqli_connect("localhost", "root", "jkty12138", "unitalk");
if(!$con){
    die('Could not connect: '.mysqli_connect_error());
}


$sql1 = "CREATE TABLE users (
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username varchar(50) NOT NULL,
    email varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    role varchar(20) NOT NULL,
    CONSTRAINT UC_User_Username UNIQUE (username),
    CONSTRAINT UC_User_Email UNIQUE (email)

)";
mysqli_query($con, $sql1);


$sql = "INSERT INTO users (id, username, email, password, role) VALUES
(1, 'admin', 'admin@gmail.com', md5('11223344'), 'admin'),
(2, 'Jess', 'jess@gmail.com', md5('11223344'), 'user'),
(3, 'qianhui', 'qianhui@graduate.utm.my', md5('11223344'), 'admin'),
(4, 'nickia', 'nickia@gmail.com', md5('11223344'), 'user'),
(5, 'loy', 'loychai888@gmail.com', md5('11223344'), 'user');
";

mysqli_query($con, $sql);

echo "Tables created";
mysqli_close($con);
?>
