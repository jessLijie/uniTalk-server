<?php
require_once './config.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/userList', function (Request $request, Response $response, $args) {
    $params = $request->getQueryParams();
    $search_val = $params['search_val'] ?? null;
    $user_id = $params['user_id'] ?? null;
    $db = new db();
    $con = $db->connect();

    try {
        if ($search_val) {
            if ($search_val == "") {
                $query = "SELECT * FROM users";
                $stmt = $con->prepare($query);
            } else {
                $query = "SELECT * FROM users WHERE username=:searchVal or email=:searchVal";
                $stmt = $con->prepare($query);
                $stmt->bindValue("searchVal", $search_val);
            }
        } elseif ($user_id) {
            $query = "SELECT * FROM users WHERE id=:user_id";
            $stmt = $con->prepare($query);
            $stmt->bindValue("user_id", $user_id);
        } 
        else {
            $query = "SELECT * FROM users";
            $stmt = $con->prepare($query);
        }
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);

        $response->getBody()->write(json_encode($users));

    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }

});

$app->get('/userCount', function (Request $request, Response $response, $args) {
    $db = new db();
    $con = $db->connect();

    try {
        $query = "SELECT COUNT(*) AS total FROM users";
        $stmt = $con->prepare($query);
        $stmt->execute();
        $userCount = $stmt->fetch(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($userCount));
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