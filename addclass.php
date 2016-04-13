/** Receive a class subject and course number.
 *  Check if the pair is already in the database.
 *  If it is add the class to the user.
 *  If not add the class then add it to the user.
 *  @author Bryce Stevenson
**/
<?php
// Routes

// session_start();

$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

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

    $studentsQuery = 'INSERT INTO students (user_id, class_id) VALUES (?,?);';
    $studentTableInsert = $dbc->prepare($studentsQuery);
    $studentTableInsert->execute([$userId, $classIDNum]);
  }

  //The course does exist. Add the user to the bridge table.
  else {

    $userQuery = 'INSERT INTO students (user_id, class_id) VALUES (?,?);';
    $insertUser = $dbc->prepare($userQuery);
    $insertUser->execute([$userId, $classIDNum]);
  }

  // echo json_encode($classIDNum);
});
