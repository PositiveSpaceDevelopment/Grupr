<?php

//To do
//delete classes
//active classes database
//need to get a intermediate step that filters down classes in the beginning
//fix reset password
//leaving a group
//get the current groups, not groups in the past
//delete and get everything correctly
//edit classes

//Questions:

// Routes

// {"first_name":"Ross"}
$app->post('/searchuserfirstname', function ($request, $response, $args) {
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
});

// {"last_name":"Johnson"}
$app->post('/searchuserlastname', function ($request, $response, $args) {
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
});

// {"first_name":"Ross", "last_name":"Miller"}
$app->post('/searchuser', function ($request, $response, $args) {
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
});

// {"first_name":"Sam","last_name":"Calvert","email":"scalvert@smu.edu","password":"calvert"}
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
    $query = 'INSERT INTO user_info (email, password, session_id, first_name, last_name, salt, level, last_login) VALUES (:email,:password,:session_id,:first_name,:last_name,:salt, 1.0, now())';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':email', $decode->email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':session_id', $_SESSION['session_id']);
    $stmt->bindParam(':first_name', $decode->first_name);
    $stmt->bindParam(':last_name', $decode->last_name);
    $stmt->bindParam(':salt', $salt);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }
});

// {"user_id": "3", "group_id": "1"}
$app->post('/joingroup', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $user_id = $decode->user_id;
    $group_id = $decode->group_id;

    $query = 'INSERT INTO members (user_id, group_id, time_joined) VALUES (:user_id, :group_id, now())';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':group_id', $group_id);
    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $query = 'SELECT class_subject, class_number, class_id FROM groups NATURAL JOIN classes where group_id = :group_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':group_id', $group_id);
    try {
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        $class_subject = $result->class_subject;
        $class_number = $result->class_number;
        $class_id = $result->class_id;
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $query = 'SELECT count(*) FROM classes NATURAL JOIN students NATURAL JOIN user_info where class_subject = :class_subject AND class_number = :class_number AND user_id = :user_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':class_subject', $class_subject);
    $stmt->bindParam(':class_number', $class_number);
    $stmt->bindParam(':user_id', $user_id);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $has_class = $stmt->fetchColumn();
    if($has_class == 0)
    {
        $query = 'INSERT INTO students VALUES (:user_id, :class_id)';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':class_id', $class_id);
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }
    }

});

//{"user_id": "1", "group_name":"Teddy's study buddies", "time_of_meeting": "2016-04-15 19:00:00", "description": "stupid people trying to learn good", "class_subject": "CSE", "class_number": "1341", "location": "Lyle", "location_details": "Junkins 110"}
$app->post('/creategroup', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $user_id = $decode->user_id;
    $group_name = $decode->group_name;
    $time_of_meeting = $decode->time_of_meeting;
    $description = $decode->description;
    $class_subject = $decode->class_subject;
    $class_number = $decode->class_number;
    $location_details = $decode->location_details;
    $location = $decode->location;

    //location_id
    $query = 'SELECT location_id FROM locations WHERE location = :location';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':location', $location);

    try {
        $stmt->execute();
        $location_id = $stmt->fetch(PDO::FETCH_ASSOC);
        $location_id = $location_id["location_id"];
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    //class_id
    $query = 'SELECT count(*) FROM classes WHERE class_subject = :class_subject AND class_number = :class_number';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':class_subject', $class_subject);
    $stmt->bindParam(':class_number', $class_number);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $number_of_rows = $stmt->fetchColumn();

    if($number_of_rows != 0)
    {
        $query = 'SELECT class_id FROM classes WHERE class_subject = :class_subject AND class_number = :class_number';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':class_subject', $class_subject);
        $stmt->bindParam(':class_number', $class_number);

        try {
            $stmt->execute();
            $class_id = $stmt->fetch(PDO::FETCH_ASSOC);
            $class_id = $class_id["class_id"];
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

    } else {
        $query = 'INSERT INTO classes (class_subject, class_number) VALUES (:class_subject, :class_number)';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':class_subject', $class_subject);
        $stmt->bindParam(':class_number', $class_number);

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $query = 'SELECT class_id FROM classes WHERE class_subject = :class_subject AND class_number = :class_number';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':class_subject', $class_subject);
        $stmt->bindParam(':class_number', $class_number);

        try {
            $stmt->execute();
            $class_id = $stmt->fetch(PDO::FETCH_ASSOC);
            $class_id = $class_id["class_id"];
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }
    }

    $query = 'SELECT count(*) FROM classes NATURAL JOIN students NATURAL JOIN user_info where class_subject = :class_subject AND class_number = :class_number AND user_id = :user_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':class_subject', $class_subject);
    $stmt->bindParam(':class_number', $class_number);
    $stmt->bindParam(':user_id', $user_id);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $has_class = $stmt->fetchColumn();
    if($has_class == 0)
    {
        $query = 'INSERT INTO students VALUES (:user_id, :class_id)';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':class_id', $class_id);
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }
    }

    $query = 'INSERT INTO groups (group_name, time_of_meeting, description, creation_time, owner_id, class_id, location_id, location_details) VALUES (:group_name, :time_of_meeting, :description, now(), :owner_id, :class_id, :location_id, :location_details)';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':group_name', $group_name);
    $stmt->bindParam(':time_of_meeting', $time_of_meeting);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':owner_id', $user_id);
    $stmt->bindParam(':class_id', $class_id);
    $stmt->bindParam(':location_id', $location_id);
    $stmt->bindParam(':location_details', $location_details);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    //group_id
    $query = 'SELECT group_id FROM groups WHERE group_name = :group_name AND time_of_meeting = :time_of_meeting AND description = :description AND owner_id = :owner_id AND class_id = :class_id AND location_id = :location_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':group_name', $group_name);
    $stmt->bindParam(':time_of_meeting', $time_of_meeting);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':owner_id', $user_id);
    $stmt->bindParam(':class_id', $class_id);
    $stmt->bindParam(':location_id', $location_id);

    try {
        $stmt->execute();
        $group_id = $stmt->fetch(PDO::FETCH_ASSOC);
        $group_id = $group_id["group_id"];
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $query = 'INSERT INTO members (user_id, group_id, time_joined) VALUES (:user_id, :group_id, now())';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':group_id', $group_id);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

});

// {"email":"aterra@smu.edu","password":"terra"}
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
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':email', $_SESSION['email']);

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $query = 'SELECT user_id FROM user_info WHERE session_id = :session_id AND email = :email';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':session_id', $_SESSION['session_id']);
        $stmt->bindParam(':email', $_SESSION['email']);

        try {
            $stmt->execute();
            $user_id = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id = $user_id["user_id"];
            $_SESSION['user_id'] = $user_id;
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $stringToReturn = array();

        $query = 'SELECT email, user_id, level FROM user_info WHERE email = :email';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':email', $email);

        try {
            $stmt->execute();
            $profile = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        array_push($stringToReturn, $profile);

        $classes = "";
        $query = 'SELECT class_subject, class_number FROM classes NATURAL JOIN students WHERE user_id = :user_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);

        try {
            $stmt->execute();
            $classes = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        array_push($stringToReturn, $classes);

        $names = "";
        $query = "SELECT first_name, last_name FROM user_info WHERE user_id = :user_id";
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);

        try {
            $stmt->execute();
            $names = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        array_push($stringToReturn, $names);

        echo json_encode($stringToReturn, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    } else {
        echo "login failed";
    }
});

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
});
 // {"user_id": "1", "password": "hunter2"}

$app->post('/resetpassword', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $user_id = $decode->user_id;
    $password = $decode->password;
    $salt = generateRandomString();
    $db_pass = crypt($password, $salt);
    $query = 'UPDATE user_info SET password = :db_pass, salt = :salt WHERE user_id = :user_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':db_pass', $db_pass);
    $stmt->bindParam(':salt', $salt);
    $stmt->bindParam(':user_id', $user_id);
    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }


});

$app->post('/logout', function ($request, $response, $args) {
    session_start();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
});

// {"user_id": "3"}
$app->post('/getusergroups', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $user_id = $decode->user_id;
    $groups = array();
    $query = 'SELECT group_id, group_name, time_of_meeting, description, ta_attending, teacher_attending, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE user_id = :user_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $user_groups = $stmt->fetchAll();
    foreach($user_groups as $row)
    // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
    {
        // $group_id = $group_info["group_id"];
        $group_id = $row["group_id"];
        $query = 'SELECT group_id, group_name, time_of_meeting, description, ta_attending, teacher_attending, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id AND user_id = :user_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':group_id', $group_id);
        $stmt->bindParam(':user_id', $user_id);

        try {
            $stmt->execute();
            $group_info = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $query = 'SELECT first_name, last_name FROM members NATURAL JOIN groups NATURAL JOIN user_info WHERE group_id = :group_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':group_id', $group_id);
        try {
            $stmt->execute();
            $member = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $group_info["members"] = $member;
        array_push($groups, $group_info);

    }

    $json = json_encode($groups, JSON_PRETTY_PRINT);
    echo $json;


});

$app->get('/grups', function ($request, $response, $args) {
    $dbc = $this->dbc;
    $groups = array();
    $query = 'SELECT group_id, group_name, time_of_meeting, description, ta_attending, teacher_attending, class_subject, class_number, location, location_details FROM groups NATURAL JOIN classes NATURAL JOIN locations';
    $stmt = $dbc->prepare($query);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $all_groups = $stmt->fetchAll();
    foreach($all_groups as $row)
    // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
    {
        // $group_id = $group_info["group_id"];
        $group_id = $row["group_id"];
        $query = 'SELECT group_id, group_name, time_of_meeting, description, ta_attending, teacher_attending, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':group_id', $group_id);

        try {
            $stmt->execute();
            $group_info = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $query = 'SELECT first_name, last_name FROM members NATURAL JOIN groups NATURAL JOIN user_info WHERE group_id = :group_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':group_id', $group_id);

        try {
            $stmt->execute();
            $member = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $group_info["members"] = $member;
        array_push($groups, $group_info);

    }

    $json = json_encode($groups, JSON_PRETTY_PRINT);
    echo $json;


});

// {"group_id": "6", "user_id": "1", "content": "I love sports"}
$app->post('/sendmessage', function ($request, $response, $args) {
    $dbc = $this->dbc;
    $body = $request->getBody();
    $decode = json_decode($body);
    $group_id = $decode->group_id;
    $user_id = $decode->user_id;
    $content = $decode->content;

    $query = 'SELECT member_id FROM members NATURAL JOIN groups WHERE user_id = :user_id AND group_id = :group_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':group_id', $group_id);
    try {
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        $member_id = $result->member_id;
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $query = 'INSERT INTO messages (content, send_time, member_id, group_id) VALUES (:content, now(), :member_id, :group_id)';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':member_id', $member_id);
    $stmt->bindParam(':group_id', $group_id);
    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

});

// {"group_id": "6"}
$app->post('/getmessages', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $group_id = $decode->group_id;
    $messages = array();

    $query = 'SELECT content, send_time, member_id, first_name, last_name FROM messages NATURAL JOIN members NATURAL JOIN user_info WHERE group_id = :group_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':group_id', $group_id);
    try {
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $row)
        {
            array_push($messages, $row);
        }
        echo json_encode($messages, JSON_PRETTY_PRINT);
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }
});
