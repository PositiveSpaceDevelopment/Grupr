$app->post('/test', function(){
    global $conn;
    global $app;
    $result = array();
    $test=$conn->query("SELECT * FROM Users WHERE user_id = 1");
    if ($t = $test->fetch_assoc()){
        echo "It works";
    }
    else {
        echo "It doesn't work";
    }
    return;
 });
