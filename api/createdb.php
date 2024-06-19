<?php
include ("./config.php");
//  $db = new db();
//     $con = $db->connect();

$con = mysqli_connect("localhost", "root", "aeiou12345", "unitalk");
if (!$con) {
    die('Could not connect: ' . mysqli_connect_error());
}


$createUser = "CREATE TABLE users (
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username varchar(50) NOT NULL,
    email varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    role varchar(20) NOT NULL DEFAULT 'user',
    CONSTRAINT UC_User_Username UNIQUE (username),
    CONSTRAINT UC_User_Email UNIQUE (email)
    );";
mysqli_query($con, $createUser);

$createTalk = "CREATE TABLE talks (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50),
    image VARCHAR(255),
    posted_datetime DATETIME,
    likes INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending'
    );";
mysqli_query($con, $createTalk);

$createComment = "CREATE TABLE comments(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    talk_id INT NOT NULL,
    comment_content TEXT NOT NULL,
    parent_id INT DEFAULT 0,
    posted_datetime DATETIME
    );";
mysqli_query($con, $createComment);

$createLike = "CREATE TABLE likes(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    talk_id INT NOT NULL
    );

";
mysqli_query($con, $createLike);

$insertUsers = "INSERT INTO users (id, username, email, password, role) VALUES
(1, 'admin', 'admin@gmail.com', md5('11223344'), 'admin'),
(2, 'Jess', 'jess@gmail.com', md5('11223344'), 'user'),
(3, 'qianhui', 'qianhui@graduate.utm.my', md5('11223344'), 'admin'),
(4, 'nickia', 'nickia@gmail.com', md5('11223344'), 'user'),
(5, 'loy', 'loychai888@gmail.com', md5('11223344'), 'user');
";
mysqli_query($con, $insertUsers);

$insertTalks = "INSERT INTO unitalk.talks (user_id, title, content, category, image, posted_datetime, likes, status) VALUES
(1, 'Talk Title 1', 'This is the content of talk 1.', 'Technology', 'image1.jpg', '2024-06-09 12:34:56', 0, 'pending'),
(2, 'Talk Title 2', 'This is the content of talk 2.', 'Fashion', 'image2.jpg', '2024-06-09 13:45:07', 0, 'pending'),
(3, 'Talk Title 3', 'This is the content of talk 3.', 'Sports', 'image3.jpg', '2024-06-09 14:56:18', 0, 'pending'),
(4, 'Talk Title 4', 'This is the content of talk 4.', 'Technology', 'image4.jpg', '2024-06-09 15:01:23', 0, 'pending'),
(5, 'Talk Title 5', 'This is the content of talk 5.', 'Fashion', 'image5.jpg', '2024-06-09 16:12:34', 0, 'pending'),
(6, 'Talk Title 6', 'This is the content of talk 6.', 'Sports', 'image6.jpg', '2024-06-09 17:23:45', 0, 'pending'),
(7, 'Talk Title 7', 'This is the content of talk 7.', 'Technology', 'image7.jpg', '2024-06-09 18:34:56', 0, 'pending');
";
mysqli_query($con, $insertTalks);

echo "Tables created";
mysqli_close($con);
?>