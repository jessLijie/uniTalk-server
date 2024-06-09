<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/signup', function (Request $request, Response $response, $args) {
    $db = new db();
    $con = $db->connect();
    
    $data = $request->getParsedBody();
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    // Check if all required fields are provided
    if (empty($username) || empty($email) || empty($password)) {
        $response->getBody()->write(json_encode(["message" => "Username, email, and password are required."]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Hash the password (MD5 in this example, but consider using stronger hashing algorithms)
    $hashedPassword = md5($password);

    try {
        // Check if the email is already registered
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $existingUser = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($existingUser) {
            // If the email is already registered, return an error
            $response->getBody()->write(json_encode(["message" => "Email already exists."]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        } else {
            // If the email is not registered, proceed with signup
            $insertQuery = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $insertStmt = $con->prepare($insertQuery);
            $insertStmt->bindParam(':username', $username);
            $insertStmt->bindParam(':email', $email);
            $insertStmt->bindParam(':password', $hashedPassword);
            $insertStmt->execute();

            $response->getBody()->write(json_encode(["message" => "Signup successful"]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        }
    } catch (PDOException $e) {
        // Handle database errors
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});
?>
