/** Receive a class subject and course number.
 *  Check if the pair is already in the database.
 *  If it is add un enroll the user.
 *  @author Bryce Stevenson
**/

<?php

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
});
