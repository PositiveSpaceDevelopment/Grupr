<?php

//Questions:
//What is the difference between doing it /test, and /login?
//How can I set up the database connection in dependencies?
//How can I use sessions in routes?
//How would I put functions into routes file?
//Should I switch to using PDO?
//Can you look at our ER diagram and come up wiht some critiques?


// Routes

$app->get('/allclasses', function ($request, $response, $args){
    $dbc = $this->dbc;
    $strToReturn = '';
    $classes = '';
    $sql = 'SELECT * FROM classes';
    try {
      $stmt = $dbc->query($sql);
      $classes = $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    catch(PDOException $e) {
      echo json_encode($e->getMessage());
    }
    $strToReturn = json_encode($classes);
    return $response->write('' . $strToReturn);
  }
);


$app->get('/profile', function ($request, $response, $args)
{
    session_start();
    $dbc = $this->dbc;
    $stringToReturn = array();
    $profile = "";
    $json_user_id = $_SESSION['user_id'];
    $json_email = $_SESSION['email'];
    // Create a query for the database
    $profile_user_id = $_SESSION['user_id'];
    $query = 'SELECT email, user_id FROM user_info WHERE user_id = :user_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $profile_user_id);
    try{
        $stmt->execute();
        $profile = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }
    array_push($stringToReturn, json_encode($profile));
    // $strToReturn = json_encode($profile);
    // return $response->write('' . $strToReturn);
    $classes = "";
    $query = "SELECT class_subject, class_number FROM classes WHERE user_id = :user_id";
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $profile_user_id);
    try{
        $stmt->execute();
        $classes = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }
    // $strToReturn = json_encode($profile);
    array_push($stringToReturn, json_encode($classes));

    $names = "";
    $query = "SELECT first_name, last_name FROM user_info WHERE user_id = :user_id";
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $profile_user_id);
    try{
        $stmt->execute();
        $names = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }
    array_push($stringToReturn, json_encode($names));
    return $response->write('' . json_encode($stringToReturn));

});

// Run with curl -i -X POST -H "Content-Type: application/json"  -d '{"first_name":"Sam","last_name":"Calvert","email":"scalvert@smu.edu","password":"calvert"}' http://zero-to-slim.dev/registeruser

$app->post('/registeruser', function ($request, $response, $args) {
    session_start();
    $id = session_id();
    $_SESSION['session_id'] = $id;
    $body = $request->getBody();
    $decode = json_decode($body);
    $hostname = "localhost";
    $username = "grupr";
    $dbname = "grupr";
    $password = "hunter2";
    $dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
    connect to database! Please try again later.");
    mysqli_select_db($dbc, $dbname);
    $salt = generateRandomString();
    $password = $decode->password;
    $password = crypt($password, $salt);
    $stringToReturn = "";
    $query = "INSERT INTO user_info (email, password, session_id, first_name, last_name, salt) VALUES (?,?,?,?,?,?)";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, 'ssssss', $decode->email, $password, $_SESSION['session_id'], $decode->first_name, $decode->last_name, $salt);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
);

// $app->get('/[{name}]', function ($request, $response, $args) {
$app->get('/hello', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/goodbye', function ($request, $response, $args) {
    return $response->write("Time to go. Goodbye!");
});


$app->get('/login', function ($request, $response, $args) {
    return $this->renderer->render($response, 'login.php', $args);
});
