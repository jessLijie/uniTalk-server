<?php
require_once './config.php';
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withStatus(200);
});

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

$app->get('/talks/recent', function (Request $request, Response $response, $args) {
    $db = new db();
    $con = $db->connect();

    try {
        $query = "SELECT * FROM talks ORDER BY posted_datetime DESC LIMIT 5";
        $stmt = $con->prepare($query);
        $stmt->execute();
        $recentTalks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($recentTalks));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
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

$app->get('/categoryCounts', function (Request $request, Response $response, $args) {
    $db = new db();
    $con = $db->connect();

    try {
        $query = "SELECT category, COUNT(*) AS total FROM talks GROUP BY category";
        $stmt = $con->prepare($query);
        $stmt->execute();
        $categoryCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categoryIcons = [
            'Food' => ['component' => 'fa fa-cutlery', 'background' => 'dark'],
            'Technology' => ['component' => 'ni ni-world-2', 'background' => 'dark'],
            'Fashion' => ['component' => 'fa fa-heart', 'background' => 'dark'],
            'Sports' => ['component' => 'fa fa-futbol-o', 'background' => 'dark'],
            'Transport' => ['component' => 'fa fa-bus', 'background' => 'dark'],
        ];

        $categories = [];
        foreach ($categoryCounts as $categoryCount) {
            $category = $categoryCount['category'];
            $icon = isset($categoryIcons[$category]) ? $categoryIcons[$category] : ['component' => '', 'background' => ''];
            $categories[] = [
                'icon' => $icon,
                'label' => $category,
                'description' => 'Total <strong>' . $categoryCount['total'] . '</strong> talks'
            ];
        }

        $response->getBody()->write(json_encode($categories));
    } catch (PDOException $e) {
        // Handle exceptions
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/talkCount', function (Request $request, Response $response, $args) {
    $db = new db();
    $con = $db->connect();

    try {
        $query = "SELECT COUNT(*) AS total FROM talks";
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

$app->post('/talks/create', function (Request $request, Response $response, $args) {
    $db = new db();
    $con = $db->connect();
    
    $data = $request->getParsedBody();
    try {
        $userId = $data['userId']??'';
        $title = $data['title']??'';
        $topic = $data['topic']??'';
        $content = $data['content']??'';
        $image = $request->getUploadedFiles()['image'];
        
        // Handling image upload
        $directory = __DIR__ . '/uploads';
        $filename = moveUploadedFile($directory, $image);

        $query = "INSERT INTO talks (user_id, title, content, category, image, posted_datetime, status)
                  VALUES (:user_id, :title, :content, :category, :image, NOW(), 'pending')";
        $stmt = $con->prepare($query);
        $stmt->bindValue(":user_id", $userId); // Assuming a static user ID for now
        $stmt->bindValue(":title", $title);
        $stmt->bindValue(":content", $content);
        $stmt->bindValue(":category", $topic);
        $stmt->bindValue(":image", $filename);

        $stmt->execute();
        
        $response->getBody()->write(json_encode(["message" => "Talk created successfully!"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

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

function moveUploadedFile($directory, $uploadedFile) {
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

$app->get('/talks', function (Request $request, Response $response, $args) {
    $db = new db();
    $con = $db->connect();

    try {
        $query = "SELECT * FROM talks ORDER BY posted_datetime DESC";
        $stmt = $con->query($query);
        $talks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add the base URL to the image path
        $baseUrl = 'http://localhost/PHP/uniTalk-server/api/uploads/';
        foreach ($talks as &$talk) {
            if ($talk['image']) {
                $talk['image'] = $baseUrl . $talk['image'];
            }
        }

        $response->getBody()->write(json_encode($talks));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

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

$app->get('/talks/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $db = new db();
    $con = $db->connect();

    try {
        $query = "SELECT * from talks WHERE id = :id";
        $stmt = $con->prepare($query);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        $talk = $stmt->fetch();

        // Add the base URL to the image path
        $baseUrl = 'http://localhost/PHP/uniTalk-server/api/uploads/';
        if ($talk && $talk['image']) {
            $talk['image'] = $baseUrl . $talk['image'];
        }

        if ($talk) {
            $response->getBody()->write(json_encode($talk));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(["message" => "Talk not found"]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
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

$app->put('/talks/{action}/{id}/{like}', function (Request $request, Response $response, $args) {

    $talkId = $args['id'];
    $talkLike = $args['like'];
    $action = $args['action'];

    $db = new db();
    $con = $db->connect();

    $data = $request->getParsedBody();

    $userId = $data['userId']??'';
    try {
        $query = "UPDATE talks SET likes = :talkLike WHERE id = :id";
        $stmt = $con->prepare($query);
        if($action === "add"){
        $stmt->bindValue("talkLike", $talkLike+1);}
        else{
        $stmt->bindValue("talkLike", $talkLike-1);
        }
        $stmt->bindValue("id", $talkId);

        $stmt->execute();
        
        if ($action === "add") {
            $insertQuery = "INSERT INTO likes (user_id, talk_id) VALUES (:user_id, :talk_id)";
            $insertStmt = $con->prepare($insertQuery);
            $insertStmt->bindValue("user_id", $userId);
            $insertStmt->bindValue("talk_id", $talkId);

            $insertStmt->execute();
        } else {
            $deleteQuery = "DELETE FROM likes WHERE user_id = :user_id AND talk_id = :talk_id";
            $deleteStmt = $con->prepare($deleteQuery);
            $deleteStmt->bindValue("user_id", $userId);
            $deleteStmt->bindValue("talk_id", $talkId);

            $deleteStmt->execute();
        }
        
        $response->getBody()->write(json_encode(["message" => "Talk like update successfully"]));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});
    
$app->get('/talks/check-like/{userId}/{talkId}', function (Request $request, Response $response, $args) {
        $userId = $args['userId'];
        $talkId = $args['talkId'];
    
        $db = new db();
        $con = $db->connect();
    
        try {
            $query = "SELECT COUNT(*) as count FROM likes WHERE user_id = :userId AND talk_id = :talkId";
            $stmt = $con->prepare($query);
            $stmt->bindValue("userId", $userId);
            $stmt->bindValue("talkId", $talkId);
            $stmt->execute();
    
            $result = $stmt->fetch();
            $liked = $result['count'] > 0;
    
            $response->getBody()->write(json_encode(["liked" => $liked]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $error = ["message" => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
});

$app->post('/talks/comment', function (Request $request, Response $response, $args) {
        $db = new db();
        $con = $db->connect();
        
        $data = $request->getParsedBody();
        try {
            $userId = $data['userId'] ?? '';
            $talkId = $data['talkId'] ?? '';
            $comment = $data['comment'] ?? '';
            $parentId = $data['parentId'] ?? '0';
    
            // Assuming the 'comments' table exists with the correct structure
            $query = "INSERT INTO comments (user_id, talk_id, comment_content, parent_id, posted_datetime)
                      VALUES (:user_id, :talk_id, :comment_content, :parent_id, NOW())";
            $stmt = $con->prepare($query);
            $stmt->bindValue(":user_id", $userId);
            $stmt->bindValue(":talk_id", $talkId);
            $stmt->bindValue(":comment_content", $comment);
            $stmt->bindValue(":parent_id", $parentId);
    
            $stmt->execute();
            
            $response->getBody()->write(json_encode(["message" => "Comment added successfully!"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    
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
    
$app->get('/talks/{id}/comment', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $db = new db();
    $con = $db->connect();
    
    try {
        // Assuming the 'comments' table exists with the correct structure
        $query = "SELECT * FROM comments WHERE id = :id ORDER BY posted_datetime DESC";
        $stmt = $con->prepare($query);
        $stmt->bindValue(":id", $id);

        $stmt->execute();
        $comment = $stmt->fetch();
        
        $response->getBody()->write(json_encode($comment));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

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

$app->get('/talklist/{userid}', function(Request $request, Response $response, $args){
    $userid = $args['userid'];
    $db = new db();
    $con = $db->connect();

    try{
        $query = "SELECT * FROM talks WHERE user_id = :userid";
        $stmt = $con->prepare($query);
        $stmt -> bindValue(":userid", $userid);
        $stmt -> execute();
        $talk = $stmt -> fetchALL(PDO::FETCH_ASSOC);

        if($talk){
            $response -> getBody() -> write(json_encode($talk));
            return $response -> withStatus(200) -> withHeader('Content-Type', 'application/json');
        }
    }catch(PDOexception $e){
        $error = [
            "message" => $e -> getMessage()
        ];
        $response -> getBody() -> write(json_encode($error));
        return $response -> withStatus(500)->writeHeader('Content-Type', 'application/json');
    }finally{
        $con = null;
    }
});

$app->put('/talks/{userid}/{talkid}', function(Request $request, Response $response, $args){
    $userid = $args['userid'];
    $talkid = $args['talkid'];
    $db = new db();
    $con = $db->connect();

    $data = $request->getParsedBody();
    $title = $data['title'] ?? '';
    $content = $data['content'] ?? '';
    $category = $data['category'] ?? '';
    

    try{
        $query = "SELECT * FROM talks WHERE user_id = :userid AND id = :talkid";
        $stmt = $con->prepare($query);
        $stmt->bindValue(":userid", $userid);
        $stmt->bindValue(":talkid", $talkid);
        $stmt->execute();
        $talk = $stmt->fetch(PDO::FETCH_ASSOC);

        if($talk){

            $userId = $data['userId']??'';
            $title = $data['title']??'';
            $content = $data['content']??'';
            $category = $data['category']??'';
            $uploadedFiles = $request->getUploadedFiles();
            if (isset($uploadedFiles['image'])) {
                $image = $uploadedFiles['image'];
            }
                        
            // Handling image upload
            $directory = __DIR__ . '/uploads';
            $filename = moveUploadedFile($directory, $image);            

            $updateQuery = "UPDATE talks SET title = :title, content = :content, category = :category, image = :image, posted_datetime = NOW(), status = 'pending' WHERE user_id = :userid AND id = :talkid";
            $updateStmt = $con->prepare($updateQuery);
            $updateStmt->bindValue(":title", $title);
            $stmt->bindValue(":content", $content);
            $stmt->bindValue(":category", $category);
            $stmt->bindValue(":image", $filename);
            $updateStmt->bindValue(":userid", $userid);
            $updateStmt->bindValue(":talkid", $talkid);
            $updateStmt->execute();

            $response->getBody()->write(json_encode(["message" => "Talk updated successfully."]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(["message" => "Talk not found."]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }catch(PDOexception $e){
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }finally{
        $con = null;
    }
});


?>