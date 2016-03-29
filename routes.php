<?php
//Questions:
//What is the difference between doing it /test, and /login?
//Can you look at our ER diagram and come up wiht some critiques?
//How can I set up the database connection in dependencies?
//How can I use sessions in routes?
//How would I put functions into routes file?
//Should I switch to using PDO?


//found at http://stackoverflow.com/questions/4356289/php-random-string-generator
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

//found at http://stackoverflow.com/questions/4356289/php-random-string-generator
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Routes

$app->get('/test', function ($request, $response, $args){
    $hostname = "localhost";
    $username = "grupr";
    $dbname = "grupr";
    $password = "hunter2";
    $dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
    connect to database! Please try again later.");
    mysqli_select_db($dbc, $dbname);

    $stringToReturn = "";
    $query = "SELECT class_subject, class_number, user_id FROM classes";
    $json_classes_array = array();
    $result = mysqli_query($dbc, $query);
    if($result)
    {
        while($row = mysqli_fetch_array($result))
        {
            $class_subject = $row['class_subject'];
            $class_number = $row['class_number'];
            $user_id = $row['user_id'];
            $json_class_row = array('class_subject' => $class_subject, 'class_number' => $class_number, 'user_id' => $user_id);
            array_push($json_classes_array, $json_class_row);
        }
    }
    $stringToReturn = json_encode($json_classes_array);

    return $response->write("" . $stringToReturn);
});

$app->get('/profile', function ($request, $response, $args)
{
    session_start();
    $hostname = "localhost";
    $username = "grupr";
    $dbname = "grupr";
    $password = "hunter2";
    $dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
    connect to database! Please try again later.");
    mysqli_select_db($dbc, $dbname);

    $stringToReturn = "";

    $json_user_id = $_SESSION['user_id'];
    $json_email = $_SESSION['email'];
    $json_user_info_array = array('user_id' => $json_user_id, 'email' => $json_email);
    echo json_encode($json_user_info_array);
    echo "<br>";
    echo "<br>";
    // Create a query for the database
    $profile_user_id = $_SESSION['user_id'];
    $quote = "'";
    $profile_user_id = $quote . $profile_user_id . $quote;
    $query = "SELECT class_subject, class_number FROM classes WHERE user_id = $profile_user_id";
    $result = mysqli_query($dbc, $query);


    // If the query executed properly proceed
    if($result){
    // $json_classes_array = array();

    // mysqli_fetch_array will return a row of data from the query
    // until no further data is available
    while($row = mysqli_fetch_array($result)) {

    $class_subject = $row['class_subject'];
    $class_number = $row['class_number'];
    $json_classes_array = array('class_subject' => $class_subject, 'class_number' => $class_number);
    echo json_encode($json_classes_array);
    echo "<br>";

    }

    }
    echo "<br>";

    $query = "SELECT first_name, last_name FROM user_info WHERE user_id = $profile_user_id";
    $result = mysqli_query($dbc, $query);
    if($result)
    {
        $row = mysqli_fetch_array($result);
        $first_name = $row['first_name'];
        $last_name = $row['last_name'];
        $json_name_array = array('first_name' => $first_name, 'last_name' => $last_name);
        echo json_encode($json_name_array);
    }

    echo "<br>";


    return $response->write("" . $stringToReturn);
});

// Run with curl -i -X POST -H "Content-Type: application/json"  -d '{"first_name":"John","last_name":"Cena","email":"jcena@smu.edu","password":"cena"}' p://zero-to-slim.dev/registeruser

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
