<?php
require_once './config.php';
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/talkList', function (Request $request, Response $response, $args) {
    $params = $request->getQueryParams();
    $status = $params['status'] ?? null;

    $db = new db();
    $con = $db->connect();

    try {
        $query = "SELECT t.*, COUNT(c.id) as comment_count 
            FROM talks t
            LEFT JOIN comments c ON t.id = c.talk_id
            WHERE t.status = :status
            GROUP BY t.id ";
        $stmt = $con->prepare($query);
        $stmt->bindValue("status", $status);
        
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

$app->put('/talks/{id}/approve', function (Request $request, Response $response, $args) {
    $talkId = $args['id'];

    $db = new db();
    $con = $db->connect();

    try {
        $query = "UPDATE talks SET status = 'approved' WHERE id = :id";
        $stmt = $con->prepare($query);
        $stmt->bindValue("id", $talkId);

        $stmt->execute();
        
        $response->getBody()->write(json_encode(["message" => "Talk approved successfully"]));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->delete('/talks/{id}/delete', function (Request $request, Response $response, $args) {
    $id = $args['id'];

    $db = new db();
    $con = $db->connect();

    try {
        $query = "DELETE FROM talks WHERE id = :id";
        $stmt = $con->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(["message" => "Talk deleted successfully."]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(["message" => "Failed to delete talk."]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    } finally {
        $con = null;
    }
});
?>