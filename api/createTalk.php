<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app -> post('/createTalk', function (Request $request, Response $response, $args){
    $db = new db();
    $con = $db -> connect();

    // to retrieve form data
    $data = $request -> getParsedBody();
    $user_id = $data['user_id'] ?? '';
    $title = '-';
    $content = $data['content'] ?? '';
    $category = $data['category'] ?? '';

    // to retrieve uploaded image file
    $uploadedFile = $request -> getUploadedFiles();
    $image = $uploadedFile['image'] ?? null;


    if (empty($user_id) || empty($title) || empty($content) || empty($category)) {
        $response -> getBody() -> write(json_encode(["message" => "User id, content and category are required."]));
        return $response -> withStatus(400) -> withHeader('Content-Type', 'application/json');
    }

    try {
        $query = "INSERT INTO talks (user_id, title, content, category, image, posted_datetime) VALUES (:user_id, :title, :content, :category, :image, NOW())";
        $stmt = $con -> prepare($query);
        $stmt -> bindParam(':user_id', $user_id);
        $stmt -> bindParam(':title', $title);
        $stmt -> bindParam(':content', $content);
        $stmt -> bindParam(':category', $category);
        $stmt -> bindParam(':image', $image);
        $stmt -> execute();

        $response -> getBody() -> write(json_encode(["message" => "Talk created successfully."]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $error = [
            "message" => $e -> getMessage()
        ];
        $response -> getBody() -> write(json_encode($error));
        return $response -> withStatus(500) -> withHeader('Content-Type', 'application/json');
    }
});