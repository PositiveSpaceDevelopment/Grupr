<?php

//Questions:
//How can I use sessions in routes?
//Can you look at our ER diagram and come up wiht some critiques?
//How do you like a url to a ip address
//How secure does our mobile app have to be?
    // I have found it difficult to use sesison ID's in routes...



// Routes
// {"first_name":"Ross"}
$app->post('/searchuserfirstname', function ($request, $response, $args) {
    session_start();
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $first_name = $decode->first_name;
    $query = 'SELECT first_name, last_name, user_id FROM user_info WHERE first_name LIKE :first_name';
    $stmt = $dbc->prepare($query);
    $like = '%';
    $first_name = $like . $first_name . $like;
    $stmt->bindParam(':first_name', $first_name);
    $stmt->execute();
    $user_info = $stmt->fetchAll(PDO::FETCH_OBJ);
    echo json_encode($user_info);
}
);

// {"last_name":"Johnson"}
$app->post('/searchuserlastname', function ($request, $response, $args) {
    session_start();
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $last_name = $decode->last_name;
    $query = 'SELECT first_name, last_name, user_id FROM user_info WHERE last_name LIKE :last_name';
    $stmt = $dbc->prepare($query);
    $like = '%';
    $last_name = $like . $last_name . $like;
    $stmt->bindParam(':last_name', $last_name);
    $stmt->execute();
    $user_info = $stmt->fetchAll(PDO::FETCH_OBJ);
    echo json_encode($user_info);
}
);
// {"first_name":"Ross", "last_name":"Miller"}
$app->post('/searchuser', function ($request, $response, $args) {
    session_start();
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $first_name = $decode->first_name;
    $last_name = $decode->last_name;
    $query = 'SELECT first_name, last_name, user_id FROM user_info WHERE first_name LIKE :first_name AND last_name LIKE :last_name';
    $stmt = $dbc->prepare($query);
    $like = '%';
    $first_name = $like . $first_name . $like;
    $last_name = $like . $last_name . $like;
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->execute();
    $user_info = $stmt->fetchAll(PDO::FETCH_OBJ);
    echo json_encode($user_info);
}
);

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

$app->post('/profile', function ($request, $response, $args)
{
    session_start();
    $dbc = $this->dbc;
    $stringToReturn = array();
    $body = $request->getBody();
    $decode = json_decode($body);
    $email = $decode->email;

    $query = 'SELECT user_id FROM user_info WHERE email = :email LIMIT 1';
    $dbc = $this->dbc;
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user_id = $stmt->fetch(PDO::FETCH_ASSOC);
    $profile_user_id = $user_id["user_id"];

    $query = 'SELECT email, user_id FROM user_info WHERE email = :email';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':email', $email);
    try{
        $stmt->execute();
        $profile = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }
    array_push($stringToReturn, json_encode($profile));

    $classes = "";
    $query = 'SELECT class_subject, class_number FROM classes WHERE user_id = :user_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $profile_user_id);
    try{
        $stmt->execute();
        $classes = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }
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
    echo json_encode($stringToReturn);
    // return $response->write('' . json_encode($stringToReturn));

});



// Run with curl -i -X POST -H "Content-Type: application/json"  -d '{"first_name":"Sam","last_name":"Calvert","email":"scalvert@smu.edu","password":"calvert"}' http://zero-to-slim.dev/registeruser

$app->post('/registeruser', function ($request, $response, $args) {
    session_start();
    $id = session_id();
    $_SESSION['session_id'] = $id;
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
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
    //set response vairables {email, user_id, first_name, last_name};
}
);

// {"user_id": "3", "group_id": "1"}
$app->post('/joingroup', function ($request, $response, $args) {
    session_start();
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $user_id = $decode->user_id;
    $group_id = $decode->group_id;

    $query = 'INSERT INTO members (user_id, group_id, time_joined) VALUES (:user_id, :group_id, now())';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':group_id', $group_id);
    $stmt->execute();

}
);

//{"user_id": "1", "group_name":"Teddy's study buddies", "time_of_meeting": "2016-04-15 19:00:00", "description": "stupid people trying to learn good", "class_subject": "CSE", "class_number": "1341", "semester": "Spring 2016", "building": "Junkins", "room": "110"}
$app->post('/creategroup', function ($request, $response, $args) {
    session_start();
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $user_id = $decode->user_id;
    $group_name = $decode->group_name;
    $time_of_meeting = $decode->time_of_meeting;
    $description = $decode->description;
    $class_subject = $decode->class_subject;
    $class_number = $decode->class_number;
    $semester = $decode->semester;
    $building = $decode->building;
    $room = $decode->room;
    //what i need?
        //location_id

    $query = 'SELECT location_id FROM locations WHERE building = :building AND room = :room';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':building', $building);
    $stmt->bindParam(':room', $room);
    $stmt->execute();
    $location_id = $stmt->fetch(PDO::FETCH_ASSOC);
    $location_id = $location_id["location_id"];
    //     //class_id
    $query = 'SELECT class_id FROM classes WHERE class_subject = :class_subject AND class_number = :class_number AND semester = :semester';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':class_subject', $class_subject);
    $stmt->bindParam(':class_number', $class_number);
    $stmt->bindParam(':semester', $semester);
    $stmt->execute();
    $class_id = $stmt->fetch(PDO::FETCH_ASSOC);
    $class_id = $class_id["class_id"];
    //     //owner_id
    //     //owner_id is user_id since a group only gets created once, whoever is logged on and creates it will be the one that creates the group
    //     //time_of_meeting
    $query = 'INSERT INTO groups (group_name, time_of_meeting, description, creation_time, owner_id, class_id, location_id) VALUES (:group_name, :time_of_meeting, :description, now(), :owner_id, :class_id, :location_id)';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':group_name', $group_name);
    $stmt->bindParam(':time_of_meeting', $time_of_meeting);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':owner_id', $user_id);
    $stmt->bindParam(':class_id', $class_id);
    $stmt->bindParam(':location_id', $location_id);
    $stmt->execute();

    $query = 'SELECT group_id FROM groups WHERE group_name = :group_name AND time_of_meeting = :time_of_meeting AND description = :description AND owner_id = :owner_id AND class_id = :class_id AND location_id = :location_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':group_name', $group_name);
    $stmt->bindParam(':time_of_meeting', $time_of_meeting);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':owner_id', $user_id);
    $stmt->bindParam(':class_id', $class_id);
    $stmt->bindParam(':location_id', $location_id);
    $stmt->execute();
    $group_id = $stmt->fetch(PDO::FETCH_ASSOC);
    $group_id = $group_id["group_id"];

    $query = 'INSERT INTO members (user_id, group_id, time_joined) VALUES (:user_id, :group_id, now())';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':group_id', $group_id);
    $stmt->execute();

    //set response vairables {email, user_id, first_name, last_name};
}
);


// Run with curl -i -X POST -H "Content-Type: application/json"  -d '{"email":"rmiller@smu.edu","password":"miller"}' http://zero-to-slim.dev/login

$app->get('/profile', function ($request, $response, $args)
{
    session_start();
    $dbc = $this->dbc;
    $stringToReturn = array();
    $profile = "";
    // $json_user_id = $_SESSION['user_id'];
    // $json_email = $_SESSION['email'];
    echo $json_user_id;
    echo "<br>";
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
    if(login($email, $password, $dbc) == true)
    {
        $_SESSION['email'] = $email;
        $query = 'UPDATE user_info SET session_id = :session_id WHERE email = :email';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':session_id', $_SESSION['session_id']);
        $stmt->bindParam(':email', $_SESSION['email']);
        $stmt->execute();
        $query = 'UPDATE user_info SET last_login = now() WHERE email = :email';
        $stmt2 = $dbc->prepare($query);
        $stmt2->bindParam(':email', $_SESSION['email']);
        $stmt2->execute();
        $query = 'SELECT user_id FROM user_info WHERE session_id = :session_id AND email = :email';
        $stmt3 = $dbc->prepare($query);
        $stmt3->bindParam(':session_id', $_SESSION['session_id']);
        $stmt3->bindParam(':email', $_SESSION['email']);
        $stmt3->execute();
        $user_id = $stmt3->fetch(PDO::FETCH_ASSOC);
        $user_id = $user_id["user_id"];
        $_SESSION['user_id'] = $user_id;

        $stringToReturn = array();

        $query = 'SELECT user_id FROM user_info WHERE email = :email LIMIT 1';
        $dbc = $this->dbc;
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user_id = $stmt->fetch(PDO::FETCH_ASSOC);
        $profile_user_id = $user_id["user_id"];

        $query = 'SELECT email, user_id FROM user_info WHERE email = :email';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':email', $email);
        try{
            $stmt->execute();
            $profile = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }
        array_push($stringToReturn, json_encode($profile));

        $classes = "";
        $query = 'SELECT class_subject, class_number FROM classes WHERE user_id = :user_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $profile_user_id);
        try{
            $stmt->execute();
            $classes = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }
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
        echo json_encode($stringToReturn);
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

$app->post('/leavegroup', function($request, $response, $args) {
    session_start();
    $body = $request->getBody();
    $dbc = $this->dbc;
    $user_id = $decode->user_id;
    $group_id = $decode->group_id;
    $owner_id = $decode->owner_id;

    //if the owner is the one who deleted group AND
    //the group is now empty, delete the group (I think)
        //user_info.user_id is probably wrong...
    $query = 'SELECT owner_id FROM groups WHERE user_info.user_id = owner_id';
    $stmt = $dbc->prepare($query);
    $stmt->execute();
    //if the query returns null, then the user leaving the group is NOT the creator
    if(mysql_num_rows($query) == 0)
    {
        try
        {
            $query = 'DELETE FROM groups WHERE group_id = :group_id';
            $stmt = $dbc->prepare($query);
            $stmt->bindParam(':group_id', $group_id);
            $stmt->execute();
        }
        catch(PDOException $e)
        {
            echo json_encode($e->getMessage());
        }
    }
    else
    {
        //delete member from group
        try
        {
            $query = 'DELETE FROM members WHERE user_id = :user_id';
            $stmt = $dbc->prepare($query);
            $stmt->bindParam(':user_id', $user_id);

            $stmt->execute();
        }
        catch(PDOException $e) {
          echo json_encode($e->getMessage());
        }
    }
}
);
