<?php
include ("./config.php");
//  $db = new db();
//     $con = $db->connect();

$con = mysqli_connect("localhost", "root", "jkty12138", "unitalk");
if (!$con) {
    die('Could not connect: ' . mysqli_connect_error());
}


$createUser = "CREATE TABLE users (
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username varchar(50) NOT NULL,
    email varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    role varchar(20) NOT NULL,
    CONSTRAINT UC_User_Username UNIQUE (username),
    CONSTRAINT UC_User_Email UNIQUE (email)
    );";
mysqli_query($con, $createUser);

$createTalk = "CREATE TABLE talks (
     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    img VARCHAR(255),
    posted_datetime DATETIME NOT NULL,
    likes int DEFAULT 0
    );";
mysqli_query($con, $createTalk);

$createComment = "CREATE TABLE comments(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    comment_content TEXT NOT NULL,
    user_id INT NOT NULL,
    talk_id INT NOT NULL,
    parent_id INT NOT NULL,
    posted_datetime DATETIME NOT NULL
    );";
mysqli_query($con, $createComment);


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