<?php

//Questions:
//What is the difference between doing it /test, and /login?
//How can I use sessions in routes?
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

// $app->post('/registeruser', function ($request, $response, $args) {
//     session_start();
//     $id = session_id();
//     $_SESSION['session_id'] = $id;
//     $body = $request->getBody();
//     $decode = json_decode($body);
//     $hostname = "localhost";
//     $username = "grupr";
//     $dbname = "grupr";
//     $password = "hunter2";
//     $dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
//     connect to database! Please try again later.");
//     mysqli_select_db($dbc, $dbname);
//     $salt = generateRandomString();
//     $password = $decode->password;
//     $password = crypt($password, $salt);
//     $stringToReturn = "";
//     $query = "INSERT INTO user_info (email, password, session_id, first_name, last_name, salt) VALUES (?,?,?,?,?,?)";
//     $stmt = mysqli_prepare($dbc, $query);
//     mysqli_stmt_bind_param($stmt, 'ssssss', $decode->email, $password, $_SESSION['session_id'], $decode->first_name, $decode->last_name, $salt);
//     mysqli_stmt_execute($stmt);
//     mysqli_stmt_close($stmt);
// }
// );

$app->post('/registeruser', function ($request, $response, $args) {
    session_start();
    $id = session_id();
    $_SESSION['session_id'] = $id;
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    // $hostname = "localhost";
    // $username = "grupr";
    // $dbname = "grupr";
    // $password = "hunter2";
    // $dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
    // connect to database! Please try again later.");
    // mysqli_select_db($dbc, $dbname);
    $salt = generateRandomString();
    $password = $decode->password;
    $password = crypt($password, $salt);
    $query = 'INSERT INTO user_info (email, password, session_id, first_name, last_name, salt) VALUES (:email,:password,:session_id,:first_name,:last_name,:salt)';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':email', $decode->email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':session_id', $_SESSION['session_id']);
    $stmt->bindParam(':first_name', $decode->first_name);
    $stmt->bindParam(':last_name', $decode->last_name);
    $stmt->bindParam(':salt', $salt);
    $stmt->execute();
}
);

// Run with curl -i -X POST -H "Content-Type: application/json"  -d '{"email":"rmiller@smu.edu","password":"miller"}' http://zero-to-slim.dev/login

$app->get('/salt', function ($request, $response, $args) {
    $query = 'SELECT salt FROM user_info WHERE email = :email LIMIT 1';
    $dbc = $this->dbc;
    $stmt = $dbc->prepare($query);
    $login_email = "aterra@smu.edu";
    $stmt->bindParam(':email', $login_email);
    $stmt->execute();
    $salt = $stmt->fetch(PDO::FETCH_ASSOC);
    $salt_to_return = json_encode($salt);
    $salt = $salt["salt"];
    echo $salt;
    echo "<br>";
    return $response->write('' . $salt_to_return);
});

$app->get('/password', function ($request, $response, $args) {
    $query = 'SELECT password FROM user_info WHERE email = :email LIMIT 1';
    $dbc = $this->dbc;
    $stmt = $dbc->prepare($query);
    $login_email = "aterra@smu.edu";
    $stmt->bindParam(':email', $login_email);
    $stmt->execute();
    $db_password = $stmt->fetch(PDO::FETCH_ASSOC);
    $pass_to_return = json_encode($db_password);
    $db_password = $db_password["password"];
    echo $db_password;
    echo "<br>";
    return $response->write('' . $pass_to_return);
});

$app->get('/user_id', function ($request, $response, $args) {
    $query = 'SELECT user_id FROM user_info WHERE email = :email LIMIT 1';
    $dbc = $this->dbc;
    $stmt = $dbc->prepare($query);
    $login_email = "aterra@smu.edu";
    $stmt->bindParam(':email', $login_email);
    $stmt->execute();
    $user_id = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id_to_return = json_encode($user_id);
    $user_id = $user_id["user_id"];
    echo $user_id;
    echo "<br>";
    return $response->write('' . $user_id_to_return);
});

$app->post('/login', function ($request, $response, $args) {
    session_start();
    $id = session_id();
    $_SESSION['session_id'] = $id;
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $email = $decode->email;
    $password = $decode->password;
    echo $email;
    echo "email<br>";
    echo $password;
    echo "password<br>";
    // $stringToReturn = "";
    // $query = "INSERT INTO user_info (email, password, session_id, first_name, last_name, salt) VALUES (?,?,?,?,?,?)";
    // $stmt = mysqli_prepare($dbc, $query);
    // mysqli_stmt_bind_param($stmt, 'ssssss', $decode->email, $password, $_SESSION['session_id'], $decode->first_name, $decode->last_name, $salt);
    // mysqli_stmt_execute($stmt);
    // mysqli_stmt_close($stmt);
    if(login($email, $password, $dbc) == true)
    {
        $_SESSION['email'] = $email;
        $query = 'UPDATE user_info SET session_id = :session_id WHERE email = :email';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':session_id', $_SESSION['session_id']);
        $stmt->bindParam(':email', $_SESSION['email']);
        $stmt->execute();
        // $stmt = mysqli_prepare($dbc, "UPDATE user_info SET session_id = ? WHERE email = ?");
        // mysqli_stmt_bind_param($stmt, 'ss', $_SESSION['session_id'], $_SESSION['email']);
        // mysqli_stmt_execute($stmt);
        // mysqli_stmt_close($stmt);
        // how do I set the timezone to my timezone
        $query = 'UPDATE user_info SET last_login = now() WHERE email = :email';
        $stmt2 = $dbc->prepare($query);
        $stmt2->bindParam(':email', $_SESSION['email']);
        $stmt2->execute();
        // $stmt = mysqli_prepare($dbc, "UPDATE user_info SET last_login = now() WHERE email = ?");
        // mysqli_stmt_bind_param($stmt, 's', $_SESSION['email']);
        // mysqli_stmt_execute($stmt);
        // mysqli_stmt_close($stmt);
        $query = 'SELECT user_id FROM user_info WHERE session_id = :session_id AND email = :email';
        $stmt3 = $dbc->prepare($query);
        $stmt3->bindParam(':session_id', $_SESSION['session_id']);
        $stmt3->bindParam(':email', $_SESSION['email']);
        $stmt3->execute();
        $user_id = $stmt3->fetch(PDO::FETCH_ASSOC);
        $user_id = $user_id["user_id"];
        echo $user_id;
        echo "userid<br>";

        // $stmt = mysqli_prepare($dbc, "SELECT user_id FROM user_info WHERE session_id = ? AND email = ?");
        // mysqli_stmt_bind_param($stmt, 'ss', $_SESSION['session_id'], $_SESSION['email']);
        // mysqli_stmt_execute($stmt);
        // mysqli_stmt_bind_result($stmt, $user_id);
        // mysqli_stmt_fetch($stmt);
        // mysqli_stmt_close($stmt);
        $_SESSION['user_id'] = $user_id;
    } else {
        echo "login failed";
    }
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
