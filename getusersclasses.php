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

  echo json_encode($classList);
});
