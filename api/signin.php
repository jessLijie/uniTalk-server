<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/signin', function (Request $request, Response $response, $args) {
    $db = new db();
    $con = $db->connect();
    
    $data = $request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $response->getBody()->write(json_encode(["message" => "Email and password are required."]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        $query = "SELECT * FROM users WHERE email = :email AND password = MD5(:password)";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($user) {
            // Set user session or token here if needed
            $response->getBody()->write(json_encode($user));
            
        } else {
            $response->getBody()->write(json_encode(["message" => "Invalid email or password."]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }

    return $response->withHeader('Content-Type', 'application/json');
});
?>
