angular.module('starter.services', [])

//factory to carry around user data
.factory("ProfileData",function() {
  data = {
    "email": "aterra@smu.edu",
    "user_id": "1",
    "classes": [
      {
        "class_subject": "CSE",
        "class_number": "3330"
      },
      {
        "class_subject": "CSE",
        "class_number": "3381"
      },
      {
        "class_subject": "CEE",
        "class_number": "3302"
      },
      {
        "class_subject": "PRW",
        "class_number": "2301"
      },
      {
        "class_subject": "CSE",
        "class_number": "3353"
      },
      {
        "class_subject": "CSE",
        "class_number": "3342"
      }
    ],
    "first_name": "Andrew",
    "last_name": "Terra"
  }
  return {data};

  // return {data:null};
})


.factory('Chats', function() {
  // Might use a resource here that returns a JSON array

  return {}
});
