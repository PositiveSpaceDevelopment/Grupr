<?php

  //Send all the whole class table to the front end
  $app->post('/getClasses', function() {
    global $conn;
    global $app;

    $classQuery = $conn->query("SELECT class_id, class_subject, class_number, semester, ta_id, teacher_id FROM classes");
    $result = $classQuery->fetch_assoc();

    echo json_encode($result);
    return;
  });
?>
