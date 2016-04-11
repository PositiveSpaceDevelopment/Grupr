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

  // $query = 'SELECT class_id FROM classes WHERE user_id LIKE :userId AND class_subject Like :courseSubject AND class_number LIKE :courseNumber';
  // $stmt = $dbc->prepare($query);
  // $like = '%';
  // $userId = $like . $userId . $like;
  // $courseSubject = $like . $courseSubject . $like;
  // $courseNumber = $like . $courseNumber . $like;
  //
  // $stmt->bindParam(':userId', $userId);
  // $stmt->bindParam(':courseSubject', $courseSubject);
  // $stmt->bindParam(':courseNumber', $courseNumber);
  // $stmt->execute();

  $query =
  'SELECT classes.class_id
  FROM classes
  INNER JOIN students ON classes.class_id = students.class_id
  WHERE user_id = ? AND class_subject =? AND class_number = ?';

  $stmt = $dbc->prepare($query);
  $stmt->execute([$userId, $courseSubject, $courseNumber]);
  $classIDNum = $stmt->fetchColumn(0);

  echo "classIDNum: " + $classIDNum;

  //If the course does not exist add it to the class table.
  if($classIDNum == NULL) {

    $classesQuery = 'INSERT INTO classes (class_subject,class_number) VALUES(?,?);';
    $classesTableInsert = $dbc->prepare($classesQuery);
    $classesTableInsert->execute([$courseSubject, $courseNumber]);

    // echo "in if\n";

    $stmt->execute([$userId, $courseSubject, $courseNumber]);
    $classIDNum = $stmt->fetchColumn(0);

    $studentsQuery = 'INSERT INTO students (user_id, class_id) VALUES (?,?);';
    $studentTableInsert = $dbc->prepare($studentsQuery);
    $studentTableInsert->execute([$userId, $classIDNum]);
  }


  //The course does exist. Add the user to the bridge table.
  else {
    // $conn->query("INSERT INTO students (user_id, class_id) VALUES ($userId, $classIDNum)");
    $userQuery = 'INSERT INTO students (user_id, class_id) VALUES (?,?);';
    $insertUser = $dbc->prepare($userQuery);
    $insertUser->execute([$userId, $classIDNum]);
  }

  echo json_encode($classIDNum);
});
