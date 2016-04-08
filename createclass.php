/** Receive a class subject and course number.
 *  Check if the pair is already in the database.
 *  If it is add the class to the user.
 *  If not add the class then add it to the user.
 *  @author Bryce Stevenson
**/
<?php
  $app->post('/addclass', function() {
    global $conn;
    global $app;

    // $data = json_decode($json);

    $userId = $app->request()->post('usrId');
    $courseSubject = $app->request()->post('courseSubj');
    $courseNumber = $app->request()->post('couseNum');

    $classIDNum = $conn->query("SELECT class_id FROM classes WHERE user_id = $userId AND class_subject = $courseSubject AND class_number = $courseNumber");
    $courseExists = $classIDNum->fetch_assoc();

    // $result['status'] = 1;
    //If the course does not exist add it to the class table.
    if($courseExists == NULL) {
      //The course doesnt exit add it to the table.
      if($conn->query("INSERT INTO classes (class_subject,class_number) VALUES($courseSubject, $courseNumber)")) {

        //Insert data into the bridge table
        $conn->query("INSERT INTO students (user_id, class_id) VALUES ($userId, $classIDNum)");
      }
      //Error inserting new clas
      else {
        // $result['status'] = 2;
      }
    }
    //The course does exist. Add the user to the bridge table.
    else {
      $conn->query("INSERT INTO students (user_id, class_id) VALUES ($userId, $classIDNum)");
    }

    //Send back all of the users classes
    echo json_encode($result)
    return;
});
?>
