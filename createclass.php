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
    if($courseExists == NULL) {
      if($conn->query("INSERT INTO")) {

      }
      else {
        $result['status'] = ;
      }
    }
    else {
      if($conn->query("INSERT INTO")) {

      }
      else {
        $result['status'] = 0;
      }
    }

    echo json_encode($result)
    return;
});
?>
