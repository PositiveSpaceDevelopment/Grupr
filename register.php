//Register a new user
$app->post('/registerUser', function(){
    global $conn;
    global $app;
    $result = array();
    $email = $app->request()->post('email');
    $password = $app->request()->post('password');
    $lastname = $app->request()->post('lname');
    $firstname = $app->request()->post('fname');
    $result['status'] = 2;
    $userQuery = $conn->query("SELECT email FROM Users WHERE email='$email'");
    $userExists = $userQuery->fetch_assoc();
    if ($userExists == NULL){
        if ($conn->query("INSERT INTO Users(first_name, last_name, password, email)
            VALUES ('$firstname', '$lastname', '$password', '$email')")) {
        }
        else {
            $result['status']=0;
        }
    }
    echo json_encode($result);
    return;
});
