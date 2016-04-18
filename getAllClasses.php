$app->get('/getallclasses', function($request, $response, $args) {

  $dbc = $this->dbc;

  //send back a list of all classes that the user is in
  $allClassesQuery = 'SELECT class_subject,class_number from classes;';
  $fetchAllClasses = $dbc->prepare($allClassesQuery);
  $fetchAllClasses ->execute();

  $classList = $fetchAllClasses->fetchAll(PDO::FETCH_ASSOC);

  $json = json_encode($classList);
  echo $json;
});
