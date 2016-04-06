/** Receive a class subject and course number.
 *  Check if the pair is already in the database.
 *  If it is add the class to the user.
 *  If not add the class then add it to the user.
 *  @author Bryce Stevenson
**/
<?php
  $app->post('/createclass', function() {
    global $conn;
    global $app;

    $userId = $app->request()->post('usrId');
    $courseSubject = $app->request()->post('courseSubj');
    $courseNumber = $app->request()->post('couseNum');

    $courseQuery = $conn->query("SELECT ")
    $courseExists = $courseQuery->fetch_assoc();

    $result['status'] = 1;
    //If the course does not exist add it to the class table.
    if($courseExists == NULL) {
      //The course doesnt exit add it to the table. Else return an error
      if($conn->query("INSERT INTO classes (class_subject,class_number) VALUES($courseSubject, $courseNumber)")) {

        $classIDNum = $conn->query("SELECT class_id FROM classes WHERE user_id = $userId AND class_subject = $courseSubject AND class_number = $courseNumber");
        $conn->query("INSERT INTO students (user_id, class_id) VALUES ($userId, $classIDNum)");
      }
      else {
        $result['status'] = 2;
      }
    }
    //The course does exist. Add the user.
    else {
      //Insert the class into the users info. Else return error.
      if($conn->query(
      "INSERT INTO"
      )) {

      }
      else {
        $result['status'] = 3;
      }
    }

    echo json_encode($result)
    return;
});
?>
