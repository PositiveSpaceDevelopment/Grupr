<?php

    require 'vendor/autoload.php';
    require("sendgrid-php/sendgrid-php.php");
    require ("smtpapi-php/smtpapi-php.php");
    require ('sendgrid-php/lib/SendGrid/Email.php');
    require ('sendgrid-php/lib/SendGrid/Exception.php');
    require ('sendgrid-php/lib/SendGrid/Response.php');
    require ('smtpapi-php/lib/Smtpapi/Header.php');

    $app = new \Slim\Slim();
    $servername = "localhost";
    $username = "root";
    $password = "databases123";
    // $password = "rootpass";
    $dbname = "mydb";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }



    //APIARY LINK : docs.appsassins.apiary.io


    // Get all the data about a user, including:
    //  -Games they are involved in
    //  -Groups they are involved in

    $app->post('/test', function(){
        global $conn;
        global $app;
        $result = array();
        $test=$conn->query("SELECT * FROM Game WHERE game_ID=5");
        if ($t = $test->fetch_assoc()){
            echo "stuff";
        }
        else {
            echo "not stuff";
        }

        return;
     });


    $app->post('/quitGame', function(){
        global $conn;
        global $app;
        $result = array();
        $email = $app->request->post('email');
        $result['status'] = 0;
        if ($q = $conn->query("SELECT Player.player_ID, Player.user_ID, Game.game_ID FROM Player INNER JOIN Game ON Player.game_ID=Game.game_ID INNER JOIN User ON Player.user_ID=User.user_ID WHERE User.email = '$email'")) {
            $q = $q->fetch_assoc();
            $user_ID = $q['user_ID'];
            $game_ID = $q['game_ID'];
            $player_ID = $q['player_ID'];
        }
        else {
            $user_ID = 0;
            $game_ID = 0;
            $player_ID = 0;
        }

        // Re-assigning targets
        // tagger gets the target's target
        $transferTargetQuery = queryRow("SELECT target_ID FROM Player WHERE user_ID = '$user_ID' AND game_ID = '$game_ID'", array('target_ID'));
        $transferTarget = $transferTargetQuery['target_ID'];
        $targeted_by = queryRow("SELECT player_ID FROM Player WHERE target_ID = '$player_ID'", array("player_ID"));
        if ($targeted_by){
          $targeted_by = $targeted_by['player_ID'];
          $conn->query("UPDATE Player SET target_ID = '$transferTarget' WHERE player_ID = '$targeted_by' AND game_ID = '$game_ID'");
        }
        $conn->query("DELETE FROM Tags WHERE tagger_ID = '$user_ID'");
        $conn->query("DELETE FROM Tags WHERE target_ID = '$user_ID'");
        if($conn->query("DELETE FROM Player WHERE user_ID = '$user_ID'"))
            $result['status'] = 1;
        echo json_encode($result);
     });

    //Make a kill
    //Status codes
    //0 - SQL error
    //1 - Success
    //2 - Image exists
    //3 - Image too large
    $app->post('/killTarget', function(){
        global $conn;
        global $app;
        $result = array();
        $result['status'] = 0;

        $image = $_FILES["thumbnail"];
        $imageDest = "upload/" . basename($image['name']);

        $game_name = $app->request->post('gameName');
        $location = $app->request->post('location');
        $lat = $location['lat'];
        $lng = $location['lng'];
        $email = $app->request->post('email');

        //Upload Image
        if (file_exists($imageDest)) {
            $result['status'] = 2;
        }
        if ($image["size"] > 5000000) {
            $result['status'] = 3;
        }
        if ($result['status']==0) {
            if (move_uploaded_file($image['tmp_name'], $imageDest)) {
                $result['status']=1;
            } else {
                $result['status']=0;
            }
        }

        //Add tag to DB
        if ($player = $conn->query("SELECT Player.player_ID, Player.target_ID FROM Player INNER JOIN User ON User.user_ID=Player.user_ID  WHERE User.email='$email'")) {
            $query = $player->fetch_assoc();

            $target_ID = $query['target_ID'];

            $target_ID = queryRow("SELECT user_ID FROM Player WHERE player_ID = $target_ID", array("user_ID"));
            $target_ID = $target_ID['user_ID'];

            $player_ID = $query['player_ID'];
            $player_ID = queryRow("SELECT user_ID FROM Player WHERE player_ID = $player_ID", array("user_ID"));
            $player_ID = $player_ID['user_ID'];
        }
        $game_ID = queryRow("SELECT game_ID FROM Game WHERE game_name='$game_name'", array("game_ID"));
        $game_ID = $game_ID['game_ID'];
        if ($conn->query("INSERT INTO Tags(image,game_ID, lat, lng, tagger_ID, target_ID, status) VALUES ('$imageDest','$game_ID','$lat','$lng','$player_ID', '$target_ID', 0)")) {
            $result['status']=1;
        }
        else
            $result['status']=0;
        if ($q = $conn->query("SELECT user_ID FROM Player WHERE game_ID = '$game_ID' AND gm = 1")){
            $gm = $q->fetch_assoc();
            $gm_ID = $gm['user_ID'];
            $result['status'] = addNotification($gm_ID, $player_ID, $target_ID, $game_ID, 0);
        }
        echo json_encode($result);
    });


    function addNotification($gm_ID, $user1_ID = NULL, $user2_ID = NULL, $game_ID = NULL, $type) {
        global $conn;
        $status = 0;
        if ($conn->query("INSERT INTO Notification_Info(user1_ID, user2_ID, type, game_ID) VALUES ($user1_ID, $user2_ID, $type, $game_ID)")) {
            $notif_ID = queryRow("SELECT notif_ID FROM Notification_Info WHERE user1_ID = $user1_ID AND user2_ID = $user2_ID AND game_ID = $game_ID AND type = $type", array("notif_ID"));
            $notif_ID = $notif_ID['notif_ID'];
        }
        if ($gm_ID) {
            $conn->query("INSERT INTO User_Notifications (user_ID, viewed, notif_ID) VALUES ($gm_ID, 0, $notif_ID)");
        }
        $status = 1;
        return $status;
    }

    //GM Confirming a kill
    $app->post('/viewKill', function(){
        global $conn;
        global $app;
        $notif_ID = $app->request->post('notificationID');
        //Get notification info
        $notif_data = queryRow("SELECT * FROM Notification_Info WHERE notif_ID = '$notif_ID'", array("user1_ID","user2_ID","game_ID"));

        //Tagger name
        $tagger = $notif_data['user1_ID'];
        $target = $notif_data['user2_ID'];
        $game = $notif_data['game_ID'];
        $result = array();
        $image = queryRow("SELECT image FROM Tags WHERE tagger_ID = '$tagger' AND target_ID = '$target' AND game_ID = '$game'", array('image'));

        //Tagger
        $data = queryRow("SELECT first_name, last_name FROM User WHERE user_ID = '$tagger'", array("first_name","last_name"));
        $tagger = ucfirst($data['first_name']) . " " . ucfirst($data['last_name']);

        //Target
        $data = queryRow("SELECT first_name, last_name FROM User WHERE user_ID = '$target'", array("first_name","last_name"));
        $target = ucfirst($data['first_name']) . " " . ucfirst($data['last_name']);


        $image = "http://54.149.40.71/appsassins/api/" . $image['image'];
        if ($image == NULL)
            $result['status']=0;
        else {
            $result['image']=$image;
            $result['tagger']=$tagger;
            $result['target']=$target;
            $result['status']=1;
        }

        echo str_replace('\\/', '/', json_encode($result));
    });


    //Make a new game
    $app->post('/createGame', function(){
        global $conn;
        global $app;
        $result = array();
        $user = $app->request()->post('user');
        $gameName = $app->request()->post('name');
        $emails = $app->request()->post('emails');

        $result['status'] = 0;

        if($conn->query("INSERT INTO Game(game_mode_ID, game_name)
            VALUES(2, '$gameName')"))
            $result['status'] = 1;
         else
            $result['status'] = 0;

        // $gameID = queryRow("SELECT game_ID FROM Game WHERE game_name = '$gameName'", array("game_ID"));
        $gameQuery = $conn->query("SELECT game_ID FROM Game WHERE game_name = '$gameName'");
        $gameIDarray = $gameQuery->fetch_assoc();
        $gameID = $gameIDarray['game_ID'];

        // gets user ID for GM
        $gmID = $conn->query("SELECT user_ID FROM User WHERE email = '$user'");
        $gmIDArray = $gmID->fetch_assoc();
        $gmID0 = $gmIDArray['user_ID'];

        // creates a new player instance for the GM
        $conn->query("INSERT INTO Player (user_ID, game_ID, gm, alive) VALUES ('$gmID0', '$gameID', 1, 1)");

        // updates game instance to include the gm player ID
        $conn->query("UPDATE Game SET gm_ID = '$conn->insert_id' WHERE game_ID = '$gameID'");

        // for loop will retrieve all of the user_IDs based on email
        for($i = 0; $i < sizeof($emails); $i++){
            $userID = queryColumns("User", array('user_ID'),  "WHERE email = '$emails[$i]'");
            //Add game_ID and user_ID to invitations table
            $userID0 = $userID[0]['user_ID'];
            $conn->query("INSERT INTO Invitations (user_ID, game_ID) VALUES('$userID0' , '$gameID')");
            $conn->query("INSERT INTO Notification_Info (user1_ID, user2_ID, type, game_ID) VALUES ('$gmID0', '$userID0', 3, '$gameID')");
            $conn->query("INSERT INTO User_Notifications (user_ID, viewed, notif_ID) VALUES ('$userID0', 0,  '$conn->insert_id')");
        }

       echo json_encode($result);
       return;
    });

    //Register a new user
    $app->post('/registerUser', function(){
        global $conn;
        global $app;
        $result = array();
        $email = $app->request()->post('email');
        $password = $app->request()->post('password');
        $lastname = $app->request()->post('lName');
        $firstname = $app->request()->post('fName');

        $result['status'] = 2;

        $userQuery = $conn->query("SELECT email FROM User WHERE email='$email'");
        $userExists = $userQuery->fetch_assoc();

        if ($userExists == NULL){
            if ($conn->query("INSERT INTO User(first_name, last_name, password, email)
                VALUES ('$firstname', '$lastname', '$password', '$email')")) {

                // TODO: write an "Create notification" function?
                $userIDQuery = $conn->query("SELECT user_ID FROM User WHERE email = '$email' AND password = '$password'");
                $row = $userIDQuery->fetch_assoc();
                $userID = $row['user_ID'];


                // Creates a new "Welcome" notification for the user
                $conn->query("INSERT INTO Notification_Info (user1_ID, type) VALUES ('$userID', 6)");

                $notifQuery = $conn->query("SELECT notif_ID FROM Notification_Info WHERE user1_ID = '$userID' AND type = 6");
                $row = $notifQuery->fetch_assoc();
                $notifID = $row['notif_ID'];


                // Creates the corresponding notification alert in the user notification table
                $conn->query("INSERT INTO User_Notifications (user_ID, viewed, notif_ID) VALUES ('$userID', 0, '$notifID')");

                $result['status']=1;
            }
            else {
                $result['status']=0;
            }
        }
        echo json_encode($result);
        return;
    });


    //Login a user
    $app->post('/loginUser', function() {
        //Returns Status Codes:
        //      0 = SQL error
        //      1 = Success
        //      2 = Bad password
        //      3 = Bad email
        global $conn;
        global $app;
        $result = array();
        $result['status'] = 0;
        //$email = $_POST['email'];
        //$password = $_POST['password'];
        $user = $app->request()->post('User');
        $email = $app->request()->post('email');
        $password = $app->request()->post('password');

        $queryResult = queryRow("SELECT * FROM User WHERE email='$email' AND password='$password'", array('first_name', 'last_name'));

        if (empty($queryResult)){
            $userResult = queryRow("SELECT * FROM User WHERE email='$email'", array('email'));
            if (!empty($userResult['email'])==$email)
                $result['status'] = 2;
            else
                $result['status'] = 3;
        }
        else
        {
            $result['status'] = 1;
            $result['fName'] = $queryResult["first_name"];
            $result['lName'] = $queryResult["last_name"];
        }
        echo json_encode($result);
        return;
    });

    // Pull up data for the game that a particular user is currently in
    $app->post('/getCurrentGameStatus', function(){
        global $conn;
        global $app;

        $email = $app->request()->post('email');
        $activeGame = false;
        $result['status'] = 0;

        // check to see if 1) the user has any player instances 2) if the user is in any active games
        $playerQuery = $conn->query("SELECT player_ID, game_ID FROM Player INNER JOIN User ON User.user_ID = Player.user_ID WHERE User.email = '$email'");
        $playerArray = $playerQuery->fetch_assoc();

        if ($playerArray == NULL) {
            // user does not have any existing player instances associated with it
            $result['status'] = -1;
        }
        else {

            $playerQuery = $conn->query("SELECT player_ID, game_ID FROM Player INNER JOIN User ON User.user_ID = Player.user_ID WHERE User.email = '$email'");

            while ($players = $playerQuery->fetch_assoc()) {
                $gameID = $players['game_ID'];
                $statusQuery = $conn->query("SELECT game_status FROM Game WHERE game_ID = '$gameID'");
                $statusArray = $statusQuery->fetch_assoc();
                $gameStatus = $statusArray['game_status'];

                if ($gameStatus != -1) {
                    // Game is active
                    $activeGame = true;
                    break;
                }
            }
        }

        // gets info about the game if its an active game
        if($activeGame) {
            // Gets information about the user from the user table and adds it to the JSON file
            $userInfo = $conn->query("SELECT User.first_name, Player.gm FROM User INNER JOIN Player ON User.user_ID = Player.user_ID WHERE User.email = '$email'");
            if ($userInfo != false) {
                $row = $userInfo->fetch_assoc();
            }
            else {
                $result['status'] = "Couldn't find user info";
                echo json_encode($result);
                break;
            }
            $result['user']['fName'] = $row['first_name'];
            $result['user']['gameMaster'] = $row['gm'];

            // Gets information about the game from the Game table and adds it to the JSON file
            $gameInfo = $conn->query("SELECT game_name, game_status FROM Game WHERE game_ID = '$gameID'");
            $row = $gameInfo->fetch_assoc();
            $result['gameName'] = $row['game_name'];
            $result['gameStatus'] = $row['game_status'];

            // Gets the name and alive status for each player in the game and adds it to the JSON file
            $players_array = $conn->query("SELECT User.first_name, User.email, Player.alive FROM User INNER JOIN Player ON User.user_ID = Player.user_ID WHERE game_ID = '$gameID'");
            $i = 0;
            while ($row = $players_array->fetch_assoc()) {
                $result['players'][$i]['Player'] = $row['first_name'];
                $result['players'][$i]['email'] = $row['email'];
                $result['players'][$i]['Alive'] = $row['alive'];

                $i++;
            }

            // Gets death locations of any of the players who have been killed and adds them to the JSON file
            $location = queryColumns("Tags", array("lat","lng"),"WHERE status=1");
            $result['locations']=$location;
            $result['status'] = 1;
        }

        echo json_encode($result);

        return;
    });

    // Gets the player/target combos for all the players in a game
    $app->post('/getGameTargets', function() {
        global $conn;
        global $app;
        // $result['status'] = 0;

        $email = $app->request()->post('email');

        $gameIDquery = $conn->query("SELECT game_ID FROM Player INNER JOIN User ON Player.User_ID = User.User_ID WHERE User.email = '$email'");
        $gameIDarray = $gameIDquery->fetch_assoc();
        $gameID = $gameIDarray['game_ID'];

        $targetPairsQuery = $conn->query("SELECT user_ID, target_ID FROM Player WHERE game_ID = '$gameID'");

        $i = 0;
        while ($targetPairs = $targetPairsQuery->fetch_assoc()) {
            // gets the player ID and the ID of the player instance associated with the target
            $playerID = $targetPairs['user_ID'];
            $targetID = $targetPairs['target_ID'];

            // gets the player's name and adds it to the JSON
            $playerNameQuery = $conn->query("SELECT first_name FROM User WHERE user_ID = '$playerID'");
            $playerName = $playerNameQuery->fetch_assoc();
            $result['list'][$i]['player'] = $playerName['first_name'];

            // selects the name of the target and adds it to the JSON
            $targetNameQuery = $conn->query("SELECT first_name FROM User INNER JOIN Player ON User.user_ID = Player.user_ID WHERE player_ID = '$targetID'");
            $targetName = $targetNameQuery->fetch_assoc();
            $result['list'][$i]['target'] = $targetName['first_name'];

            $i++;
        }

        echo json_encode($result);

        return;
    });

    $app->post('/sendEmail', function() {
        $sendgrid = new SendGrid('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');
        $emails = $_POST['emails'];
        $email = new SendGrid\Email();
        for($i = 0; $i < count($emails); $i++) {
          $email->addTo($emails[$i]);
        }
        //$email->addTo('sbock@smu.edu');
        //$email->addTo('danhn@smu.edu');
            //->addTo('bar@foo.com') //One of the most notable changes is how `addTo()` behaves. We are now using our Web API parameters instead of the X-SMTPAPI header. What this means is that if you call `addTo()` multiple times for an email, **ONE** email will be sent with each email address visible to everyone.
        $email->setFrom('sjbock3@aol.com')
            ->setSubject('Invited to Game')
            ->setText('You have been invited to download the awesome, new app Appsassins! Your friends want you to join their game of assassin, an exciting, extended game of tag. Click this link to get started: Google Play Store. Click this link to go to Appsassins: Appsassins.')
            ->setHtml('<strong>You have been invited to play Appsassins!</strong><div>Your friends want you to join their game of assassin, an exciting, extended game of tag. <div>Click this link to get started: <a href="https://play.google.com/apps/testing/teamrocket.appsassins">Google Play Store.</a></div><div>Click this link to go to Appsassins: <a href="http://54.149.40.71/appsassins">Appsassins.</a></div></div>');
        try {
          $sendgrid->send($email);
        } catch(\SendGrid\Exception $e) {
          echo $e->getCode();
          foreach($e->getErrors() as $er) {
            echo $er;
          }
        }
        $result['status'] = 1;
        echo json_encode($result);
        return;
    });

    // Takes input of the new game status and changes the game status for a specific game in the database
    $app->post('/updateGameStatus', function(){
        global $conn;
        global $app;
        $result['status'] = 0;

        $gameName = $app->request()->post('gameName');
        // $gameName = $_POST['gameName'];
        $gameStatus = $app->request()->post('gameStatus');
        // $gameStatus = $_POST['gameStatus'];

        if ($conn->query("UPDATE Game SET game_status = '$gameStatus' WHERE game_name = '$gameName'")) {
            $result['status'] = 1;
        }

        echo json_encode($result);

        return;
    });

    // Gets the first name of the target of a current player
    $app->post('/getCurrentTarget', function(){
        global $conn;
        global $app;

        $email = $app->request()->post('email');

        // Find the target ID for the player who's user's email matches the input email
        $targetID = $conn->query("SELECT Player.target_ID FROM User INNER JOIN Player ON User.user_ID = Player.user_ID WHERE User.email = '$email'");
        $row = $targetID->fetch_assoc();
        $targetID = $row['target_ID'];

        // Find user first name associated with the player ID that matches the target ID
        $targetName = $conn->query("SELECT User.first_name, User.email FROM User INNER JOIN Player ON User.user_ID = Player.user_ID WHERE Player.player_ID = '$targetID'");
        $row = $targetName->fetch_assoc();

        $result['email'] = $row['email'];
        $result['target'] = $row['first_name'];

        echo json_encode($result);

        return;
    });

    // Returns the notifation status for a user
    $app->post('/getNotifications', function(){
        global $conn;
        global $app;
        $result['status'] = 0;

        $email = $app->request()->post('email');

        $UIDquery = $conn->query("SELECT user_ID FROM User WHERE email = '$email'");
        $userIDArray = $UIDquery->fetch_assoc();
        $userID = $userIDArray['user_ID'];

        $notifIDArray = $conn->query("SELECT notif_ID FROM User_Notifications WHERE user_ID = '$userID' AND viewed = 0");
        $i = 0;
        while ($row = $notifIDArray->fetch_assoc()) {
            // For each unread notification that is assigned to the user:
            $notifID = $row['notif_ID'];
            $notifInfo = $conn->query("SELECT * FROM Notification_Info WHERE notif_ID = '$notifID'");
            $info = $notifInfo->fetch_assoc();

            // selects names of the users involved with the notification
            $user1ID = $info['user1_ID'];
            $user2ID = $info['user2_ID'];
            $user1NameQuery = $conn->query("SELECT first_name FROM User WHERE user_ID = '$user1ID'");
            $user2NAmeQuery = $conn->query("SELECT first_name FROM User WHERE user_ID = '$user2ID'");
            $user1NameArray = $user1NameQuery->fetch_assoc();
            $user2NAmeArray = $user2NAmeQuery->fetch_assoc();
            $user1Name = $user1NameArray['first_name'];
            $user2Name = $user2NAmeArray['first_name'];

            // Adds notification type to the JSON file
            $result['notifications'][$i]['type'] = $info['type'];

            // Returns the info about 'the other guy', or returns the info about both users involved in the notification
            if ($info['user1_ID'] == $userID) {
                if($info['type'] == 8){
                    $result['notifications'][$i]['user1'] = $user1Name;
                }
                else {
                    $result['notifications'][$i]['user2'] = $user2Name;
                }
            }
            else if ($info['user2_ID'] == $userID) {
                $result['notifications'][$i]['user1'] = $user1Name;
            }
            else {
                $result['notifications'][$i]['user1'] = $user1Name;
                $result['notifications'][$i]['user2'] = $user2Name;

            }

            // Gets the game name and adds it to the JSON file
            $gameID = $info['game_ID'];
            $gameNameQuery = $conn->query("SELECT game_name FROM Game WHERE game_ID = '$gameID'");
            $gameName = $gameNameQuery->fetch_assoc();
            $result['notifications'][$i]['gameName'] = $gameName['game_name'];
            $result['notifications'][$i]['notifID'] = $notifID;


            $i++;
        }
        $result['status'] = 1;

        echo json_encode($result);

        return;
    });

    $app->post('/sendNotificationResponse', function(){
        global $conn;
        global $app;
        $result['status'] = 0;

        $notifID = $app->request()->post('notifID');
        $accepted = $app->request()->post('accepted');

        $Query = $conn->query("SELECT type, game_ID FROM Notification_Info WHERE notif_ID = '$notifID'");
        $infoArray = $Query->fetch_assoc();
        $gameID = $infoArray['game_ID'];
        $notifType = $infoArray['type'];

        // Gets the GM user ID so that the GM's notifications can be updated
        $gmQuery = $conn->query("SELECT user_ID FROM Player INNER JOIN Game ON Player.player_ID = Game.gm_ID WHERE Game.game_ID = '$gameID'");
        $gmIDArray = $gmQuery->fetch_assoc();
        $gmID = $gmIDArray['user_ID'];

        switch ($notifType) {
            // Pending Kill
            case '0':

                $type = 0;
                if ($accepted == 1) {
                    $type = 1;

                    // Updates the user notification table to set the new notification to unread for the two players involved
                    $usersQuery = $conn->query("SELECT user1_ID, user2_ID FROM Notification_Info WHERE notif_ID = '$notifID'");
                    $users = $usersQuery->fetch_assoc();
                    $user1 = $users['user1_ID'];  // tagger
                    $user2 = $users['user2_ID']; // target

                    /*$p_idQuery = $conn->query("SELECT player_ID FROM Player WHERE user_ID = '$user1'");
                    $pid1 = $p_idQuery->fetch_assoc();
                    $pid1 = $pid1['player_ID'];
                    $p_idQuery = $conn->query("SELECT player_ID FROM Player WHERE user_ID = '$user2'");
                    $pid2 = $p_idQuery->fetch_assoc();
                    $pid2 = $pid2['player_ID'];*/

                    $conn->query("UPDATE Tags SET status = 1 WHERE tagger_ID='$user1' AND target_ID='$user2'");
                    // Sets the notification for user1 and user2 to un-viewed because the notification type is now updated
                    $conn->query("UPDATE User_Notifications SET viewed = 0 WHERE notif_ID = '$notifID' AND user_ID = '$user1'");
                    $conn->query("UPDATE User_Notifications SET viewed = 0 WHERE notif_ID = '$notifID' AND user_ID = '$user2'");

                    // Updates the notification for the gm to viewed because they have viewed the notification in order to confirm or deny the tag
                    $conn->query("UPDATE User_Notifications SET viewed = 1 WHERE notif_ID = '$notifID' AND user_ID = '$gmID'");

                    // Re-assigning targets
                    // tagger gets the target's target
                    $transferTargetQuery = $conn->query("SELECT target_ID FROM Player WHERE user_ID = '$user2' AND game_ID = '$gameID'");
                    $transferTargetArray = $transferTargetQuery->fetch_assoc();
                    $transferTarget = $transferTargetArray['target_ID'];

                    $conn->query("UPDATE Player SET target_ID = '$transferTarget' WHERE user_ID = '$user1' AND game_ID = '$gameID'");

                    // Origional target player instance is set to dead
                    $conn->query("UPDATE Player SET alive = 0 WHERE user_ID = '$user2'");



                    //checks for winner!
                    $winner = $conn->query("SELECT user_ID FROM Player WHERE alive = 1 AND gm = 0 AND game_ID = '$gameID'");
                    // if ($winner) {
                    //echo var_dump($winner);
                    $results = array();
                    while ($r = $winner->fetch_assoc()) {
                        array_push($results, $r);
                    }

                    if (sizeof($results)==1) {
                        $results0 = $results[0]['user_ID'];
                        $conn->query("INSERT INTO Notification_Info (user1_ID, type, game_ID) VALUES ('$results0', 8, '$gameID')");
                        $NID = $conn->insert_id;
                        $playerList = $conn->query("SELECT user_ID FROM Player WHERE game_ID = '$gameID'");
                        while ($r = $playerList->fetch_assoc()){
                            $UID = $r['user_ID'];
                            $conn->query("INSERT INTO User_Notifications (user_ID, viewed, notif_ID) VALUES ('$UID', 0, '$NID')");

                        }
                    }
                }
                else {
                    $type = 2;
                }
                // Updates the type of the notification
                $conn->query("UPDATE Notification_Info SET type = '$type' WHERE notif_ID = '$notifID'");

                $result['status'] = 1;

                break;

            // Game Invite
            case '3':
            // update the game invitaiotn and add the player to the game
            // Should we create notification for GM saying 'this player has joined the game!'? - Does this need to happen? There is no notif type for this

                $usersQuery = $conn->query("SELECT user2_ID FROM Notification_Info WHERE notif_ID = '$notifID'");
                $users = $usersQuery->fetch_assoc();
                $user2 = $users['user2_ID'];

                // Change user2 notif to viewed
                $conn->query("UPDATE User_Notifications SET viewed = 1 WHERE notif_ID = '$notifID' AND user_ID = '$user2'");

                // Assuming for Invitation Status:
                // 0 = pending
                // 1 = accepted
                // 2 = denied
                if ($accepted == 0) {
                    $conn->query("UPDATE Invitations SET Status = 2 WHERE user_ID = '$user2'");
                }
                else {
                    // update invitations table
                    $conn->query("UPDATE Invitations SET Status = 1 WHERE user_ID = '$user2'");


                    // check if player instance already exists in this game
                    $playerQuery = $conn->query("SELECT * FROM Player WHERE game_ID = '$gameID' AND user_ID = '$user2'");
                    $playerExists = $playerQuery->fetch_assoc();
                    if ($playerExists == NULL) {
                        // create new player instance in game
                        $conn->query("INSERT INTO Player (user_ID, game_ID, gm, alive) VALUES ('$user2', '$gameID', 0, 1)");
                    }
                }

                // check if game status needs to be updated
                $statusQuery = $conn->query("SELECT Status FROM Invitations WHERE game_ID = '$gameID'");
                $gameReady = true;
                while ($status = $statusQuery->fetch_assoc()) {
                    if ($status['Status'] == 0) {
                        //Not all invitations have been answered
                        $gameReady = false;
                        break;
                    }
                }

                if ($gameReady) {
                    // Updates the game status if every invitation to join the game has been responded to
                    $conn->query("UPDATE Game SET game_status = 1 WHERE game_ID = '$gameID'");

                    // Creates a notification telling the GM that the game is ready
                    $conn->query("INSERT INTO Notification_Info (user1_ID, type, game_ID) VALUES ('$gmID', 7, '$gameID')");

                    $notifQuery = $conn->query("SELECT notif_ID FROM Notification_Info WHERE user1_ID = '$gmID' AND type = 7 AND game_ID = '$gameID'");
                    $row = $notifQuery->fetch_assoc();
                    $newNotifID = $row['notif_ID'];

                    // Creates the corresponding notification alert in the user notification table
                    $conn->query("INSERT INTO User_Notifications (user_ID, viewed, notif_ID) VALUES ('$gmID', 0, '$newNotifID')");
                }

                $result['status'] = 1;

                break;

            default:
                $result['status'] = -1;
                break;
        }

        echo json_encode($result);

        return;
    });

    // Updates the viewed status of a notification
    $app->post('/sendReadResponse', function(){
        global $conn;
        global $app;

        $notifID = $app->request()->post('notifID');
        $read = $app->request()->post('read');
        $email = $app->request()->post('email') ;

        $userIDQuery = $conn->query("SELECT user_ID FROM User WHERE email = '$email'");
        $userIDArray = $userIDQuery->fetch_assoc();
        $userID = $userIDArray['user_ID'];

        $typeQuery = $conn->query("SELECT type FROM Notification_Info WHERE notif_ID = '$notifID'");
        $type = $typeQuery->fetch_assoc();

        // Do not update the viewed status if its a pending kill notification (type 0)
        if ($type['type'] != 0) {
            // Updates the viewed status from un-viewed to viewed
            $conn->query("UPDATE User_Notifications SET viewed = '$read' WHERE notif_ID = '$notifID' AND user_ID = '$userID'");
        }

        $result['status'] = 1;

        echo json_encode($result);
      });

    ////postAllTargets inserts the target list into the database
    $app->post('/postAllTargets', function(){
        global $conn;
        global $app;
        $result['status'] = 0;
        $list = $app->request()->post('list');
        $gmEmail = $app->request()->post('gmEmail');

        $gameIDquery = $conn->query("SELECT Game.game_ID FROM Game INNER JOIN Player ON Game.gm_ID = Player.player_ID INNER JOIN User ON Player.user_ID = User.user_ID WHERE User.email = '$gmEmail'");
        $gameIDarray = $gameIDquery->fetch_assoc();
        $gameID = $gameIDarray['game_ID'];
        for($i = 0; $i < sizeof($list); $i++){
            $player = $list[$i]['player'];
            $target = $list[$i]['target'];

            $playerUID = queryColumns("User", array('user_ID'),  "WHERE email = '$player'"); ////gets user_ID of the player
            $targetUID = queryColumns("User", array('user_ID'),  "WHERE email = '$target'"); ////gets user_ID of the target
            $targetUID = $targetUID[0]['user_ID'];
            $targetID = queryColumns("Player", array('player_ID'), "WHERE user_ID = '$targetUID'  AND game_ID = '$gameID'"); ////gets player_ID of target

            //Updates player table with target info
            $targetID = $targetID[0]['player_ID'];
            $playerUID = $playerUID[0]['user_ID'];
            if($conn->query("UPDATE Player SET target_ID = '$targetID' WHERE user_ID = '$playerUID' AND game_ID = '$gameID'")) {
                $result['status'] = 1;
            }
            else{
                $result['status'] = 0;
           }
        }
        echo json_encode($result);
        return;
    });

    //Pass in: MySQL Query, and Column Variable Name
    //Returns: single variable result
    //Example: querySingleValue("SELECT meat_ID FROM Meat WHERE name='$meat'", 'meat_ID')
    function querySingleValue($query, $variable) {
        global $conn;
        $result = 0;
        $rows = $conn->query($query);
        while ($r = mysqli_fetch_assoc($rows))
            $result = $r[$variable];
        return $result;
    }

    //Pass in: table name (string), and array of Column Variable Names (separated by strings) and a WHERE condition (if needed)
    //Returns: array of values
    //Example Usage: queryColumns("Table Name", "Array of Column Names", "WHERE ... = ...")
    //NOTE: Currently you can only send in one condition, as in  "WHERE email = ckyle@smu.edu" NOT email = $emails
    function queryColumns($table, $columns, $condition) {
        global $conn;
        $query = implode(",", $columns);
        $queryResult = $conn->query("SELECT $query FROM $table $condition");
        $result = array();
        while($r = $queryResult->fetch_assoc()){
            array_push($result, $r);
        }
        return $result;
    }

    //Pass in: MySQL Query (string), and Array Column Variable Names (array of strings)
    //Returns: array of
    //Example Usage: queryRow("SELECT meat_ID FROM Meat WHERE name='$meat'", array('meat_ID', 'name'))
    function queryRow($query, $variables) {
        global $conn;
        $result = array();
        $rows = $conn->query($query);
        if ($rows){
            while ($r = mysqli_fetch_assoc($rows)){
                foreach ($variables as $cols)
                    $result[$cols] = $r[$cols];
            }
            return $result;
        }
        return;
    }


    $app->run();
    $conn->close();
?>
