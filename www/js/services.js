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

  // Some fake testing data
  var chats = [{
    id: 0,
    name: 'Ben Sparrow',
    lastText: 'You on your way?',
    face: 'img/ben.png'
  }, {
    id: 1,
    name: 'Max Lynx',
    lastText: 'Hey, it\'s me',
    face: 'img/max.png'
  }, {
    id: 2,
    name: 'Adam Bradleyson',
    lastText: 'I should buy a boat',
    face: 'img/adam.jpg'
  }, {
    id: 3,
    name: 'Perry Governor',
    lastText: 'Look at my mukluks!',
    face: 'img/perry.png'
  }, {
    id: 4,
    name: 'Mike Harrington',
    lastText: 'This is wicked good ice cream.',
    face: 'img/mike.png'
  }];

  return {
    all: function() {
      return chats;
    },
    remove: function(chat) {
      chats.splice(chats.indexOf(chat), 1);
    },
    get: function(chatId) {
      for (var i = 0; i < chats.length; i++) {
        if (chats[i].id === parseInt(chatId)) {
          return chats[i];
        }
      }
      return null;
    }
  };
});
