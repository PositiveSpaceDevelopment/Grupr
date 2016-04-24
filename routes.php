<?php

//To do
//site map and er diagram
//fix the stuff on the production server for utilities


//Questions:

// Routes

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
    echo json_encode($user_info, JSON_PRETTY_PRINT);
});

// {"first_name":"Sam","last_name":"Calvert","email":"scalvert@smu.edu","password":"calvert"}
$app->post('/registeruser', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $salt = generateRandomString();
    $password = $decode->password;
    $password = crypt($password, $salt);
    $query = 'INSERT INTO user_info (email, password, first_name, last_name, salt, level, last_login) VALUES (:email,:password,:first_name,:last_name,:salt, 1.0, now())';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':email', $decode->email);
    $stmt->bindParam(':password', $password);
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
$app->post('/leavegroup', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $user_id = $decode->user_id;
    $group_id = $decode->group_id;

    $query = 'SELECT owner_id FROM groups WHERE group_id = :group_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':group_id', $group_id);
    try {
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        $owner_id = $result->owner_id;
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    if($user_id == $owner_id)
    {
        $query = 'DELETE FROM members WHERE user_id = :user_id AND group_id = :group_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':group_id', $group_id);
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $query = 'SELECT user_id FROM groups NATURAL JOIN members WHERE group_id = :group_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':group_id', $group_id);
        try {
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            $owner_id = $result->user_id;
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $query = 'UPDATE groups SET owner_id = :owner_id WHERE group_id = :group_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->bindParam(':group_id', $group_id);
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

    } else {
        $query = 'DELETE FROM members WHERE user_id = :user_id AND group_id = :group_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':group_id', $group_id);
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }
    }

    $query = 'UPDATE user_info SET level = level - 0.1 WHERE user_id = :user_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);

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
        $query = 'INSERT INTO students VALUES (:user_id, :class_id, TRUE)';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':class_id', $class_id);
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $query = 'UPDATE user_info SET level = level + 0.3 WHERE user_id = :user_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

    } else {
        $query = 'UPDATE user_info SET level = level + 0.1 WHERE user_id = :user_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }
    }
});

//{"user_id": "1", "group_name":"Teddy's study buddies", "time_of_meeting": "2016-04-15 19:00:00", "description": "stupid people trying to learn good", "class_subject": "CSE", "class_number": "1341", "professor": "Fontenot", "location_details": "Junkins 110"}
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
    $professor = $decode->professor;

    //location_id
    // $query = 'SELECT location_id FROM locations WHERE location = :location';
    // $stmt = $dbc->prepare($query);
    // $stmt->bindParam(':location', $location);
    //
    // try {
    //     $stmt->execute();
    //     $location_id = $stmt->fetch(PDO::FETCH_ASSOC);
    //     $location_id = $location_id["location_id"];
    // } catch(PDOException $e) {
    //     echo json_encode($e->getMessage());
    // }

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
        $query = 'INSERT INTO students VALUES (:user_id, :class_id, TRUE)';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':class_id', $class_id);
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }
    }

    $query = 'INSERT INTO groups (group_name, time_of_meeting, description, creation_time, owner_id, class_id, location_details, professor) VALUES (:group_name, :time_of_meeting, :description, now(), :owner_id, :class_id, :location_details, :professor)';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':group_name', $group_name);
    $stmt->bindParam(':time_of_meeting', $time_of_meeting);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':owner_id', $user_id);
    $stmt->bindParam(':class_id', $class_id);
    $stmt->bindParam(':professor', $professor);
    $stmt->bindParam(':location_details', $location_details);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    //group_id
    $query = 'SELECT group_id FROM groups WHERE group_name = :group_name AND time_of_meeting = :time_of_meeting AND description = :description AND owner_id = :owner_id AND class_id = :class_id AND professor = :professor';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':group_name', $group_name);
    $stmt->bindParam(':time_of_meeting', $time_of_meeting);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':owner_id', $user_id);
    $stmt->bindParam(':class_id', $class_id);
    $stmt->bindParam(':professor', $professor);

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

    $query = 'UPDATE user_info SET level = level + 0.5 WHERE user_id = :user_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

});

// {"email":"aterra@smu.edu","password":"terra"}
$app->post('/login', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $email = $decode->email;
    $password = $decode->password;
    if(login($email, $password, $dbc) == true)
    {
        $query = 'UPDATE user_info SET last_login = now() WHERE email = :email';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':email', $email);

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $query = 'SELECT user_id FROM user_info WHERE email = :email';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':email', $email);

        try {
            $stmt->execute();
            $user_id = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id = $user_id["user_id"];
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $stringToReturn = array();

        $query = 'SELECT email, user_id, first_name, last_name, level FROM user_info WHERE email = :email';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':email', $email);

        try {
            $stmt->execute();
            $login_info = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $classes = "";
        $query = 'SELECT class_subject, class_number FROM classes NATURAL JOIN students WHERE user_id = :user_id AND is_active = TRUE';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        try {
            $stmt->execute();
            $classes = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

        $login_info["classes"] = $classes;
        echo json_encode($login_info, JSON_PRETTY_PRINT);

        $query = 'UPDATE user_info SET level = level + 0.1 WHERE user_id = :user_id';
        $stmt = $dbc->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            echo json_encode($e->getMessage());
        }

    } else {
        echo "login failed";
    }
});

$app->post('/profile', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $user_id = $decode->user_id;

    $query = 'SELECT email, user_id, first_name, last_name, level FROM user_info WHERE user_id = :user_id';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);

    try {
        $stmt->execute();
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $classes = "";
    $query = 'SELECT class_subject, class_number FROM classes NATURAL JOIN students WHERE user_id = :user_id AND is_active = TRUE';
    $stmt = $dbc->prepare($query);
    $stmt->bindParam(':user_id', $user_id);

    try {
        $stmt->execute();
        $classes = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }
    $user_info["classes"] = $classes;

    echo json_encode($user_info, JSON_PRETTY_PRINT);

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
    // session_start();
    // $_SESSION = array();
    // if (ini_get("session.use_cookies")) {
    //     $params = session_get_cookie_params();
    //     setcookie(session_name(), '', time() - 42000,
    //         $params["path"], $params["domain"],
    //         $params["secure"], $params["httponly"]
    //     );
    // }
    // session_destroy();
});

// {"user_id": "3"}
$app->post('/getusergroups', function ($request, $response, $args) {
    $body = $request->getBody();
    $decode = json_decode($body);
    $dbc = $this->dbc;
    $user_id = $decode->user_id;
    $groups = array();
    //and time_of_meeting > now()
    $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description class_subject, class_number, location_details, professor FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE user_id = :user_id ORDER BY time_of_meeting ASC';
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
        $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location_details, professor FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id AND user_id = :user_id';
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
    // $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN classes NATURAL JOIN locations WHERE time_of_meeting > now() ORDER BY time_of_meeting ASC';
    $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location_details, professor FROM groups NATURAL JOIN classes NATURAL JOIN locations ORDER BY time_of_meeting ASC';
    $stmt = $dbc->prepare($query);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }

    $all_groups = $stmt->fetchAll();
    foreach($all_groups as $row)
    {
        $group_id = $row["group_id"];
        $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location_details, professor FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

// {"user_id": "3", "class_subject": "CSE", "class_number": "1342"}
$app->post('/addclass', function($request, $response, $args) {
  $body = $request->getBody();
  $decode = json_decode($body);
  $dbc = $this->dbc;
  $userId = $decode->user_id;
  $courseSubject = $decode->class_subject;
  $courseNumber = $decode->class_number;
  $query = 'SELECT classes.class_id FROM classes WHERE class_subject =? AND class_number = ?';
  $stmt = $dbc->prepare($query);
  $stmt->execute([$courseSubject, $courseNumber]);
  $classIDNum = $stmt->fetchColumn(0);
  //If the course does not exist add it to the class table.
  if($classIDNum == NULL) {
    $classesQuery = 'INSERT INTO classes (class_subject,class_number) VALUES(?,?);';
    $classesTableInsert = $dbc->prepare($classesQuery);
    $classesTableInsert->execute([$courseSubject, $courseNumber]);
    $stmt->execute([$courseSubject, $courseNumber]);
    $classIDNum = $stmt->fetchColumn(0);
    $studentsQuery = 'INSERT INTO students (user_id, class_id, is_active) VALUES (?,?, TRUE);';
    $studentTableInsert = $dbc->prepare($studentsQuery);
    $studentTableInsert->execute([$userId, $classIDNum]);
  }
  //The course does exist. Add the user to the bridge table.
  else {
    $studentTableQuery = 'Select user_id, class_id from students WHERE is_active = TRUE AND user_id =? AND class_id =?;';
    $studentTableExists = $dbc->prepare($studentTableQuery);
    $studentTableExists->execute([$userId, $classIDNum]);
    $studentTableEntry = $studentTableExists->fetchAll();
    if($studentTableEntry == NULL) {
      $userQuery = 'INSERT INTO students (user_id, class_id, is_active) VALUES (?,?, TRUE);';
      $insertUser = $dbc->prepare($userQuery);
      $insertUser->execute([$userId, $classIDNum]);
    }
    else {
    }
  }
  //send back a list of all classes that the user is in
  $allClassesQuery = 'SELECT class_subject,class_number from classes INNER JOIN students on classes.class_id = students.class_id WHERE user_id =? AND is_active = TRUE;';
  $fetchAllClasses = $dbc->prepare($allClassesQuery);
  $fetchAllClasses ->execute([$userId]);
  $classList = $fetchAllClasses->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($classList);
  $query = 'UPDATE user_info SET level = level + 0.1 WHERE user_id = :user_id';
  $stmt = $dbc->prepare($query);
  $stmt->bindParam(':user_id', $userId);

  try {
      $stmt->execute();
  } catch(PDOException $e) {
      echo json_encode($e->getMessage());
  }

});

// {"user_id": 1, "class_subject_to_change": "CSE", "class_subject_change_to": "CSEE", "class_number_to_change": "9999", "class_number_change_to": ""}
$app->post('/editclass', function($request, $response, $args) {
  $body = $request->getBody();
  $decode = json_decode($body);
  $dbc = $this->dbc;
  $user_id = $decode->user_id;
  $class_subject_to_change = $decode->class_subject_to_change;
  $class_subject_change_to = $decode->class_subject_change_to;
  $class_number_to_change = $decode->class_number_to_change;
  $class_number_change_to = $decode->class_number_change_to;

  if(empty($class_number_change_to))
  {
      $query = 'UPDATE classes SET class_subject = :class_subject_change_to WHERE class_subject = :class_subject_to_change AND class_number = :class_number_to_change';
      $stmt = $dbc->prepare($query);
      $stmt->bindParam(':class_subject_change_to', $class_subject_change_to);
      $stmt->bindParam(':class_subject_to_change', $class_subject_to_change);
      $stmt->bindParam(':class_number_to_change', $class_number_to_change);
      try {
          $stmt->execute();
      } catch(PDOException $e) {
          echo json_encode($e->getMessage());
      }
  }

  elseif (empty($class_subject_change_to)) {
      $query = 'UPDATE classes SET class_number = :class_number_change_to WHERE class_subject = :class_subject_to_change AND class_number = :class_number_to_change';
      $stmt = $dbc->prepare($query);
      $stmt->bindParam(':class_number_change_to', $class_number_change_to);
      $stmt->bindParam(':class_subject_to_change', $class_subject_to_change);
      $stmt->bindParam(':class_number_to_change', $class_number_to_change);
      try {
          $stmt->execute();
      } catch(PDOException $e) {
          echo json_encode($e->getMessage());
      }
  }

  elseif (!empty($class_subject_change_to) && !empty($class_number_change_to)) {
      $query = 'UPDATE classes SET class_number = :class_number_change_to, class_subject = :class_subject_change_to WHERE class_subject = :class_subject_to_change AND class_number = :class_number_to_change';
      $stmt = $dbc->prepare($query);
      $stmt->bindParam(':class_number_change_to', $class_number_change_to);
      $stmt->bindParam(':class_subject_change_to', $class_subject_change_to);
      $stmt->bindParam(':class_subject_to_change', $class_subject_to_change);
      $stmt->bindParam(':class_number_to_change', $class_number_to_change);
      try {
          $stmt->execute();
      } catch(PDOException $e) {
          echo json_encode($e->getMessage());
      }
  }

  else {
      echo "you done goofed";
  }



  $query= 'SELECT class_subject,class_number from classes INNER JOIN students on classes.class_id = students.class_id WHERE user_id = :user_id AND is_active = TRUE';
  $stmt = $dbc->prepare($query);
  $stmt->bindParam(':user_id', $user_id);
  try {
      $stmt->execute();
  } catch(PDOException $e) {
      echo json_encode($e->getMessage());
  }
  $classList = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($classList);



});

// {"user_id": "3", "class_subject": "CSE", "class_number": "1342"}
$app->post('/removeclass', function($request, $response, $args) {
  $body = $request->getBody();
  $decode = json_decode($body);
  $dbc = $this->dbc;
  $userId = $decode->user_id;
  $courseSubject = $decode->class_subject;
  $courseNumber = $decode->class_number;
  $query = 'SELECT classes.class_id FROM classes WHERE class_subject =? AND class_number = ?';
  $stmt = $dbc->prepare($query);
  $stmt->execute([$courseSubject, $courseNumber]);
  $classIDNum = $stmt->fetchColumn(0);
  //If the course does not exist add it to the class table.
  if($classIDNum != NULL) {
    $studentTableQuery = 'Select user_id, class_id from students WHERE is_active = TRUE AND user_id =? AND class_id =?;';
    $studentTableExists = $dbc->prepare($studentTableQuery);
    $studentTableExists->execute([$userId, $classIDNum]);
    $studentTableEntry = $studentTableExists->fetchAll();
    if($studentTableEntry != NULL) {
      $userQuery = 'UPDATE students SET is_active = FALSE WHERE user_id =? AND class_id =?;';
      $insertUser = $dbc->prepare($userQuery);
      $insertUser->execute([$userId, $classIDNum]);
    }
  }
  //send back a list of all classes that the user is in
  $allClassesQuery = 'SELECT class_subject,class_number from classes INNER JOIN students on classes.class_id = students.class_id WHERE user_id =? AND is_active = TRUE;';
  $fetchAllClasses = $dbc->prepare($allClassesQuery);
  $fetchAllClasses ->execute([$userId]);
  $classList = $fetchAllClasses->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($classList);

  $query = 'UPDATE user_info SET level = level - 0.1 WHERE user_id = :user_id';
  $stmt = $dbc->prepare($query);
  $stmt->bindParam(':user_id', $userId);

  try {
      $stmt->execute();
  } catch(PDOException $e) {
      echo json_encode($e->getMessage());
  }
});

// {"user_id": "3"}
$app->post('/getusersclasses', function($request, $response, $args) {
  $body = $request->getBody();
  $decode = json_decode($body);
  $dbc = $this->dbc;

  $userId = $decode->user_id;

  //send back a list of all classes that the user is in
  $allClassesQuery = 'SELECT class_subject,class_number from classes INNER JOIN students on classes.class_id = students.class_id WHERE user_id =? AND is_active = TRUE;';
  $fetchAllClasses = $dbc->prepare($allClassesQuery);
  $fetchAllClasses ->execute([$userId]);

  $classList = $fetchAllClasses->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($classList, JSON_PRETTY_PRINT);
});

// {"location": "Lyle"}
//someone fix all of this for professors
$app->post('/filtergroups', function($request, $response, $args) {
	$body = $request->getBody();
	$decode = json_decode($body);
	$dbc = $this->dbc;
    $groups = array();
	//he sends me one, two, three or four arguments for narrowing down search
	//use if/switch statements
	$location = $decode->location;
	$group_name = $decode->group_name;
	$class_subject = $decode->class_subject;
	$class_number = $decode->class_number;
	try
        {
		if(empty($class_number) && empty($location) && empty($group_name) && !empty($class_subject))	//just class subject
		{
            // $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_subject = :class_subject AND time_of_meeting > now() ORDER BY time_of_meeting ASC';
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_subject = :class_subject ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
    		$stmt->bindParam(':class_subject', $class_subject);
            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$class_subject_groups = $stmt->fetchAll();

            foreach($class_subject_groups as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

    	       //more stuff

		}
		else if(empty($class_subject) && empty($location) && empty($group_name) && !empty($class_number))//just class_number
		{
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_number = :class_number ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
    		$stmt->bindParam(':class_number', $class_number);
            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$class_number_groups = $stmt->fetchAll();

            foreach($class_number_groups as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

		}
        else if(empty($class_number) && empty($location) && empty($class_subject) && !empty($group_name))	//just groupname
		{
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_name = :group_name ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
            $like = '%';
            $group_name = $like . $group_name . $like;
    		$stmt->bindParam(':group_name', $group_name);
            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$group_name_groups = $stmt->fetchAll();

            foreach($group_name_groups as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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


		}
		else if(empty($class_number) && empty($class_subject) && empty($group_name) && !empty($location)) //just location
		{
    		$query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE location = :location AND time_of_meeting > now() ORDER BY time_of_meeting ASC';
    		$stmt = $dbc->prepare($query);
    		$stmt->bindParam(':location', $location);
            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$location_groups = $stmt->fetchAll();

            foreach($location_groups as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

		}
		else if(empty($class_number) && empty($group_name) && !empty($class_subject) && !empty($location)) //just class subject and location
		{
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_subject = :class_subject AND location = :location ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
    		$stmt->bindParam(':class_subject', $class_subject);
            $stmt->bindParam(':location', $location);
            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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


		}
		else if(empty($location) && empty($group_name) && !empty($class_number) && !empty($class_subject))//just class subject and class number
		{
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location_details, professor FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_subject = :class_subject AND class_number = :class_number ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
    		$stmt->bindParam(':class_subject', $class_subject);
            $stmt->bindParam(':class_number', $class_number);
            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location_details, professor FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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


		}
		else if(empty($location) && !empty($class_number) && !empty($class_subject) && !empty($group_name)) //class subj, number and groupname
		{
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_subject = :class_subject AND class_number = :class_number AND group_name = :group_name ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
            $like = '%';
            $group_name = $like . $group_name . $like;
    		$stmt->bindParam(':class_subject', $class_subject);
            $stmt->bindParam(':class_number', $class_number);
            $stmt->bindParam(':group_name', $group_name);
            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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


		}
		else if(empty($class_subject) && empty($group_name) && !empty($class_number) && !empty($location))	//just class number, location (added 4-14)
		{
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE location = :location AND class_number = :class_number ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
    		$stmt->bindParam(':location', $location);
            $stmt->bindParam(':class_number', $class_number);
            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

		}
		else if(empty($class_subject) && empty($class_number) && !empty($group_name) && !empty($location))//just group_name and location (added 4-14)
		{
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_name = :group_name AND location = :location ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
            $like = '%';
            $group_name = $like . $group_name . $like;
    		$stmt->bindParam(':group_name', $group_name);
            $stmt->bindParam(':location', $location);
            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

		}
		else if(!empty($location) && !empty($class_subject) && !empty($class_number) && !empty($group_name))	//all 4
        {
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_subject = :class_subject AND class_number = :class_number AND location = :location AND group_name =:group_name ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
            $like = '%';
            $group_name = $like . $group_name . $like;
    		$stmt->bindParam(':class_subject', $class_subject);
            $stmt->bindParam(':class_number', $class_number);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':group_name', $group_name);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

        	$groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description,  class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

		}
        else if(empty($location) && !empty($class_subject) && empty($class_number) && !empty($group_name))	//subject/name
        {
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_subject = :class_subject AND group_name =:group_name ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
            $like = '%';
            $group_name = $like . $group_name . $like;
            $stmt->bindParam(':class_subject', $class_subject);
            $stmt->bindParam(':group_name', $group_name);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

            $groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

        }
        else if(empty($location) && empty($class_subject) && !empty($class_number) && !empty($group_name))	//number/name
        {
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_number = :class_number AND group_name = :group_name ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
            $like = '%';
            $group_name = $like . $group_name . $like;
            $stmt->bindParam(':class_number', $class_number);
            $stmt->bindParam(':group_name', $group_name);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

            $groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

        }
        else if(!empty($location) && !empty($class_subject) && !empty($class_number) && empty($group_name))	//subject/number/location
        {
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_subject = :class_subject AND class_number = :class_number AND location = :location ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
            $stmt->bindParam(':class_subject', $class_subject);
            $stmt->bindParam(':class_number', $class_number);
            $stmt->bindParam(':location', $location);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

            $groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

        }
        else if(!empty($location) && empty($class_subject) && !empty($class_number) && !empty($group_name))	//number/location/name
        {
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_number = :class_number AND location = :location AND group_name =:group_name ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
            $like = '%';
            $group_name = $like . $group_name . $like;
            $stmt->bindParam(':class_number', $class_number);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':group_name', $group_name);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

            $groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

        }
        else if(!empty($location) && !empty($class_subject) && empty($class_number) && !empty($group_name))	//subject/location/name
        {
            $query = 'SELECT DISTINCT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE class_subject = :class_subject AND location = :location AND group_name =:group_name ORDER BY time_of_meeting ASC';
            $stmt = $dbc->prepare($query);
            $like = '%';
            $group_name = $like . $group_name . $like;
            $stmt->bindParam(':class_subject', $class_subject);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':group_name', $group_name);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                echo json_encode($e->getMessage());
            }

            $groups1 = $stmt->fetchAll();

            foreach($groups1 as $row)
            // while ($group_info = $stmt->fetchAll(PDO::FETCH_ASSOC))
            {
                // $group_id = $group_info["group_id"];
                $group_id = $row["group_id"];
                $query = 'SELECT group_id, group_name, time_of_meeting, description, class_subject, class_number, location, location_details FROM groups NATURAL JOIN members NATURAL JOIN locations NATURAL JOIN classes WHERE group_id = :group_id';
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

        }

        else
		{
			echo "you done goofed up";
		}

        $json = json_encode($groups, JSON_PRETTY_PRINT);
        echo $json;
	}
	catch(PDOException $e) {
          echo json_encode($e->getMessage());
    }
});

$app->get('/d3', function ($request, $response, $args) {
    $dbc = $this->dbc;
    $query = 'SELECT class_subject, class_number, cnt FROM classes NATURAL JOIN (SELECT  class_id, is_active, count(user_id) AS cnt FROM students GROUP BY class_id) AS tbl WHERE is_active = TRUE';
    $stmt = $dbc->prepare($query);
    $stmt->execute();
    $class_info = $stmt->fetchAll(PDO::FETCH_OBJ);
    $json = json_encode($class_info, JSON_PRETTY_PRINT);
    // print $json;
    $file = 'd3json_real.json';
    file_put_contents($file, $json);
    ?>
    <html>
    <head>
            <title> d3 tutorial </title>
            <script src ="http://d3js.org/d3.v3.min.js"></script>
    </head>
    <body>

        <div id = "chart"></div>
        <script>


            d3.json("d3json_real.json", function (data) {
            // d3.json($json, function (data) {
                var margin = {top: 30, right: 30, bottom: 40, left: 50};
                var width = 1250- margin.top-margin.bottom;
                var height = 700-margin.right-margin.left;

                var tooltip = d3.select('body').append('div')
                                .style('position', 'absolute')
                                .style('background', '#f4f4f4')
                                .style('padding', '5 15px')
                                .style('border', '1px #333 solid')
                                .style('border-radius', '5px')
                                .style('opacity', '0');

                var yScale = d3.scale.linear()
                                .domain([0, 8])
                                .range([0, height]);
                var xScale = d3.scale.ordinal()
                                .domain(d3.range(0, data.length))
                                .rangeBands([0, width]);
                var colors = d3.scale.linear()
                                .domain([0, data.length])
                                .range(['#860a26', '#3a13a6']);

                var myChart = d3.select('#chart').append('svg')
                        .attr('width', width +margin.right + margin.left + 50)
                        .attr('height', height + margin.top + margin.right)
                        .append('g')
                        .attr('transform', 'translate('+(margin.left+50)+','+margin.top+')')
                        .style('background', '#f4f4f4')
                        .selectAll('rect')
                            .data(data)
                            .enter().append('rect')
                                .style('fill', function (d, i) {
                                    return colors(i);
                                })
                                .attr('width', xScale.rangeBand())
                                .attr('height', 0)
                                .attr('x', function (d, i) {
                                    return xScale(i);
                                })
                                .attr('y', height)
                            .on('mouseover', function(d) {
                                tooltip.transition()
                                    .style('opacity', 1)
                                tooltip.html(d.class_subject+d.class_number)
                                    .style('left', (d3.event.pageX)+'px')
                                    .style('top', (d3.event.pageY)+'px')
                                d3.select(this).style('opacity', 0.5)
                            })
                            .on('mouseout', function(d) {
                                tooltip.transition()
                                    .style('opacity', 0)
                                d3.select(this).style('opacity', 1)
                            })


                    myChart.transition()
                        .attr('height', function(d) {
                            return yScale(parseInt(d.cnt))
                        })
                        .attr('y', function(d){
                            return height - yScale(parseInt(d.cnt))
                        })
                        .duration(700)
                        .delay(function (d,i) {
                            return i * 30;
                        })
                        .ease('elastic')

                var vScale = d3.scale.linear()
                            .domain([0, 8])
                            .range([height, 0]);
                var hScale = d3.scale.ordinal()
                            .domain(d3.range(0, data.length))
                            .rangeBands([0, width]);

                var vAxis = d3.svg.axis()
                            .scale(vScale)
                            .orient('left')
                            .ticks(9)
                            .tickPadding(1)
                var vGuide = d3.select('svg')
                            .append('g')
                                vAxis(vGuide)
                                vGuide.attr('transform', 'translate('+(margin.left+50)+','+margin.top+')')
                                vGuide.selectAll('path')
                                    .style('fill', 'none')
                                    .style('stroke', '#fff')
                                vGuide.selectAll('line')
                                    .style('stroke', '#fff')



                var vis = d3.select("body")
                    .append("svg:svg")
                    .attr("width", 1250- margin.top-margin.bottom)
                    .attr("height", 50);

                var vis2 = d3.select("body")
                    .append("svg:svg")
                    .attr("width", 75 )
                    .attr("height", 600);
                    // .attr("transform", "translate(300,50)");

                var hAxis = d3.svg.axis()
                            .scale(hScale)
                            .orient('bottom')
                            .tickValues(hScale.domain().filter(function(d,i) {
                                return 0;
                            }))


                var hGuide = d3.select('svg')
                            .append('g')
                                hAxis(hGuide)
                                hGuide.attr('transform', 'translate('+(margin.left+50)+','+(height+margin.top)+')')
                                hGuide.selectAll('path')
                                    .style('fill', 'none')
                                    .style('stroke', '#fff')
                                hGuide.selectAll('line')
                                    .style('stroke', '#fff')

                var yName = d3.select('svg')
                            .append('g')
                            .append('text')
                            .attr("transform", "rotate(-90)")
                            .attr("y", 50)
                            .attr("x", -300)
                            .attr("dy", "1em")
                            .attr("fill", "#fff")
                            .style("text-anchor", "middle")
                            .text('# of students in class')

                var xName = d3.select('svg')
                            .append('g')
                            .append('text')
                            .attr("y", 655)
                            .attr("x", 600)
                            .attr("dy", "1em")
                            .attr("fill", "#fff")
                            .style("text-anchor", "middle")
                            .text("Active Classes")
            // var svg = d3.select("body").append("svg")
            //     .attr("width", width + margin.right + margin.left)
            //     .attr("height", height + margin.top + margin.bottom)
            //     .attr("class", "graph-svg-component");

            });

        </script>
        <body style="background: #000000">
    </body>

    </html>


    <?php
});

$app->get('/circles', function ($request, $response, $args) {
    $dbc = $this->dbc;
    $query = 'SELECT class_subject AS name, cnt AS value FROM classes NATURAL JOIN (SELECT class_id, is_active, count(user_id) AS cnt FROM students NATURAL JOIN classes GROUP BY class_subject) AS tbl where is_active = true ORDER BY class_subject';
    $stmt = $dbc->prepare($query);
    $stmt->execute();
    $outside_json_object = array();
    $outside_json_object["name"] = "parent";
    $outside_json_object["value"] = 200;

    $info = $stmt->fetchAll(PDO::FETCH_OBJ);

    $inside_json_array = array();
    $outside_json_object["children"] = $info;

    $json = json_encode($outside_json_object, JSON_PRETTY_PRINT);
    // echo $json;
    $file = 'd3real_circles.json';
    file_put_contents($file, $json);
    ?>

    <!DOCTYPE html>
    <meta charset="utf-8">


    <body>
    <script src="http://d3js.org/d3.v3.min.js"></script>
    <script>


    var tooltip = d3.select('body').append('div')
                    .style('position', 'absolute')
                    .style('background', '#f4f4f4')
                    .style('padding', '5 15px')
                    .style('border', '1px #333 solid')
                    .style('border-radius', '5px')
                    .style('opacity', '0');

        var width = 1200,
            height = 725;

        var canvas = d3.select("body").append("svg")
                    .attr("width", width)
                    .attr("height", height)
                    .append("g")
                        .attr("transform", "translate(50, 50)");

        var pack = d3.layout.pack()
                    .size([width, height - 50])
                    .padding(10);



        d3.json("d3real_circles.json", function (data) {
            var color = d3.scale.category20();


            var nodes = pack.nodes(data);

            var node = canvas.selectAll(".node")
                        .data(nodes)
                        .enter()
                        .append("g")
                            .attr("class", "node")
                            .attr("transform", function (d) {return "translate("+d.x+","+d.y+")";});



            node.append("circle")
                .attr("r", function (d) {return (d.r);})
                .attr("opacity", 0.95)
                .transition()
                .duration(700)
                .delay(function (d,i) {
                    return i * 50;
                })
                .ease('bounce')
                .attr("stroke", "#ADADAD")
                .attr("stroke-width", "2")
                .style('fill', function(d) { return color(d.name); });

            // node.transition().duration(1000).attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });

            node.append("text")
                .text(function (d) { return d.children ? "" : d.name;})
                .style("font-size", "1px")
                .each(getSize)
                .style("font-size", function (d) {return (d.scale -5) +"px";})
                .style("text-anchor", "middle")
                .style("alignment-baseline", "middle")
                .attr("transform", "translate(0,1)")
                .on('mouseover', function(d) {
                    tooltip.transition()
                        .style('opacity', 1)
                    tooltip.html("students: "+d.value)
                        .style('left', (d3.event.pageX)+'px')
                        .style('top', (d3.event.pageY)+'px')
                    d3.select(this).style('opacity', 0.5)
                })
                .on('mouseout', function(d) {
                    tooltip.transition()
                        .style('opacity', 0)
                    d3.select(this).style('opacity', 1)
                });

            node.selectAll("circle")
            .on('mouseover', function(d) {
                tooltip.transition()
                    .style('opacity', 1)
                tooltip.html("students: "+d.value)
                    .style('left', (d3.event.pageX)+'px')
                    .style('top', (d3.event.pageY)+'px')
                d3.select(this).style('opacity', 0.5)
            })
            .on('mouseout', function(d) {
                tooltip.transition()
                    .style('opacity', 0)
                d3.select(this).style('opacity', 1)
            })

            function getSize(d) {
                  var bbox = this.getBBox(),
                  cbbox = this.parentNode.getBBox(),
                  scale = Math.min(cbbox.width/bbox.width, cbbox.height/bbox.height);
                  d.scale = scale;
            }

        });

    </script>
    <body style="background: #000000">
    </body>


    <?php
});

$app->get('/morecircles', function ($request, $response, $args) {
    $dbc = $this->dbc;
    $query = 'SELECT location AS "text", count(location_id) AS count FROM locations NATURAL JOIN groups GROUP BY location_id ORDER BY count DESC';
    $stmt = $dbc->prepare($query);
    $stmt->execute();

    $info = $stmt->fetchAll(PDO::FETCH_OBJ);

    $json_array = array();
    $json_array["items"] = $info;

    $json = json_encode($json_array, JSON_PRETTY_PRINT);
    // echo $json;
    // $file = 'd3real_circles.json';
    // file_put_contents($file, $json);
    ?>

    <html>
    <head>
      <title>Clicky circles</title>
      <meta charset="utf-8">

      <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200,600,200italic,600italic&subset=latin,vietnamese' rel='stylesheet' type='text/css'>

      <script src="http://phuonghuynh.github.io/js/bower_components/jquery/dist/jquery.min.js"></script>
      <script src="http://phuonghuynh.github.io/js/bower_components/d3/d3.min.js"></script>
      <script src="http://phuonghuynh.github.io/js/bower_components/d3-transform/src/d3-transform.js"></script>
      <script src="http://phuonghuynh.github.io/js/bower_components/cafej/src/extarray.js"></script>
      <script src="http://phuonghuynh.github.io/js/bower_components/cafej/src/misc.js"></script>
      <script src="http://phuonghuynh.github.io/js/bower_components/cafej/src/micro-observer.js"></script>
      <script src="http://phuonghuynh.github.io/js/bower_components/microplugin/src/microplugin.js"></script>
      <script src="http://phuonghuynh.github.io/js/bower_components/bubble-chart/src/bubble-chart.js"></script>
      <script src="http://phuonghuynh.github.io/js/bower_components/bubble-chart/src/plugins/central-click/central-click.js"></script>
      <script src="http://phuonghuynh.github.io/js/bower_components/bubble-chart/src/plugins/lines/lines.js"></script>
      <script src="clickycircles.js"></script>
      <style>
        .bubbleChart {
          min-width: 100px;
          max-width: 1200px;
          height: 700px;
          margin: 0 auto;
        }
        .bubbleChart svg{
          background: #000000;
        }
      </style>
    </head>
    <body style="background: #000000">
    <div class="bubbleChart"/>
    </body>
    </html>
    <?php
});
