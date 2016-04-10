/** Receive a class subject and course number.
 *  Check if the pair is already in the database.
 *  If it is add the class to the user.
 *  If not add the class then add it to the user.
 *  @author Bryce Stevenson
**/
<?php
$app->post('/addclass', function($request, $response, $args) {

  $body = $request->getBody();
  $decode = json_decode($body);
  $dbc = $this->dbc;

  $userId = $decode->user_id;
  $courseSubject = $decode->class_subject;
  $courseNumber = $decode->class_number;

  $query = 'SELECT class_id FROM classes WHERE user_id LIKE :userId AND class_subject Like :courseSubject AND class_number LIKE :courseNumber';
  $stmt = $dbc->prepare($query);
  $like = '%';
  $userId = $like . $userId . $like;
  $courseSubject = $like . $courseSubject . $like;
  $courseNumber = $like . $courseNumber . $like;

  $stmt->bindParam(':userId', $userId);
  $stmt->bindParam(':courseSubject', $courseSubject);
  $stmt->bindParam(':courseNumber', $courseNumber);
  $stmt->execute();

  $class_info = $stmt->fetchAll(PDO::FETCH_OBJ);
  echo json_encode($class_info);

});
?>
