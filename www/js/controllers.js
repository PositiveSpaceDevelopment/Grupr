angular.module('starter.controllers', [])

.controller('BrowseCtrl', function($scope, $state, $http, ProfileData, GroupFeed) {
  /*GroupFeed.getFeed().then(function(data) {
    $scope.feed = data;
  });*/

  $scope.newGroup = function() {
    $state.go('createGroup');
  }

    $scope.filter = function() {
	$state.go('filter')
  }

  $scope.viewGroup = function(id) {
    $state.go("tab.groupDetail",{grupID: id});
  }

  // Determines how the feed should be re-loaded
  // write the code to determine what the data array looks like
  var data = {
    user_id: "",
    class_subject: "",
    class_number: ""
  };

  // Makes the GET http request to fill the GroupFeed Data
  $http({
    method: 'POST',
    // url: 'http://private-fa798-grupr.apiary-mock.com/grups',
    // url: 'http://www.grupr.me/grups',
    url: 'http://54.213.15.90/grups',
    headers: {
      'Content-Type': 'application/json'
      },
    data: data
  }).then(function successCallback(response) {
    GroupFeed.data = response.data;
    var tempArray = [];
    var lastDay = 0;
    var lastHour = 0;
    var lastMonth = 0;

    for (var i = 0; i < GroupFeed.data.length; i++) {
      //converts the date time string provided by the database 
      // into a unix code datetime number that AngularJS can filter
      var dateString = GroupFeed.data[i].time_of_meeting;
      GroupFeed.data[i].time_of_meeting = new Date(dateString).getTime();

      // Determines when the day or hour of the meeting time changes and them
      // adds a list divider to mark the change in time
      var currentDate = new Date(dateString);
      if (currentDate.getDate() != lastDay || currentDate.getHours() != lastHour || currentDate.getMonth() != lastMonth){
        newItem = {
          dividerText: GroupFeed.data[i].time_of_meeting,
          time_of_meeting: (GroupFeed.data[i].time_of_meeting - 100),
          divider: true
        };
        tempArray.push(newItem);
        lastDay = currentDate.getDate();
        lastHour = currentDate.getHours();
        lastMonth = currentDate.getMonth();
      };
    };
    GroupFeed.data = GroupFeed.data.concat(tempArray);
    $scope.feed = GroupFeed.data;
    console.log(GroupFeed.data);

  }, function errorCallback(response) {
    console.log("something went wrong");
  });
})

.controller('GroupDetailCtrl', function($scope, $stateParams, $http, GroupFeed, ProfileData) {

  id = $stateParams.grupID;

  var index = 0; 
  while(true){
    if (GroupFeed.data[index].group_id == id) {
      break;
    };
    index++;
  }

  $scope.groupInfo = GroupFeed.data[index];
  console.log($scope.groupInfo);

  $scope.join = function() {

    var data = {}

    data.user_id = ProfileData.data.user_id;
    data.group_id = id;

    console.log(data);

    // Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/joingroup',
      // url: 'http://www.grupr.me/joingroup',
      url: 'http://54.213.15.90/joingroup',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      console.log("You Joined!");
    });


    // Makes the GET http request to fill the GroupFeed Data
    $http({
      method: 'GET',
      // url: 'http://private-fa798-grupr.apiary-mock.com/grups',
      // url: 'http://www.grupr.me/grups',
      url: 'http://54.213.15.90/grups',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response) {
      $scope.feed = response.data;
      GroupFeed.data = response.data;
      console.log(GroupFeed.data);
    }, function errorCallback(response) {
      console.log("something went wrong");
    });

  }

})

.controller('ViewAllGrupsCtrl', function($scope) {

  id = $stateParams.grupID;

  var index = 0; 
  while(true){
    if (GroupFeed.data[index].group_id == id) {
      break;
    };
    index++;
  }

  $scope.groupInfo = GroupFeed.data[index];
  console.log($scope.groupInfo);

  $scope.join = function() {

    var data = {}

    data.user_id = ProfileData.data.user_id;
    data.group_id = id;

    console.log(data);

    // Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/joingroup',
      // url: 'http://www.grupr.me/joingroup',
      url: 'http://54.213.15.90/joingroup',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      console.log("You Joined!");
    });
	$state.go("tab.ViewAllGrup");
  }

})

.controller('MyGroupsCtrl', function($scope, $state, $http, ProfileData, GroupFeed, UserGroups) {
	$scope.viewGroup = function(id){
    $state.go("tab.groupDetail",{grupID: id});
  }
	var data = {};
	data.user_id = ProfileData.data.user_id;
	// Makes the POST http request
    $http({
      method: 'POST',
	  
      // url: 'http://private-fa798-grupr.apiary-mock.com/getusergroups',
      // url: 'http://www.grupr.me/getusergroups',
      url: 'http://54.213.15.90/getusergroups',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      UserGroups.data = response.data;
      var tempArray = [];
      var lastDay = 0;
      var lastHour = 0;

      for (var i = 0; i < UserGroups.data.length; i++) {
        //converts the date time string provided by the database 
        // into a unix code datetime number that AngularJS can filter
        var dateString = UserGroups.data[i].time_of_meeting;
        UserGroups.data[i].time_of_meeting = new Date(dateString).getTime();

        // Determines when the day or hour of the meeting time changes and them
        // adds a list divider to mark the change in time
        var currentDate = new Date(dateString);
        if (currentDate.getDate() != lastDay || currentDate.getHours() != lastHour){
          newItem = {
            dividerText: UserGroups.data[i].time_of_meeting,
            time_of_meeting: (UserGroups.data[i].time_of_meeting - 100),
            divider: true
          };
          tempArray.push(newItem);
          lastDay = currentDate.getDate();
          lastHour = currentDate.getHours();
        };
      };
      UserGroups.data = UserGroups.data.concat(tempArray);

      $scope.UserGroups = UserGroups.data;
  	  console.log($scope.UserGroups);
      console.log(UserGroups.data);
    });
	
  $scope.newGroup = function() {
    $state.go('createGroup');
  }

  $scope.filter = function() {
	$state.go('filter')
  }

  $scope.viewMyGroup = function(id) {
    $state.go("tab.myGroupDetail",{grupID: id});
  }
})

.controller('MyGroupDetailCtrl', function($scope, $stateParams, $http, GroupFeed, ProfileData) {

  id = $stateParams.grupID;

  var index = 0; 
  while(true){
    if (GroupFeed.data[index].group_id == id) {
      break;
    };
    index++;
  }

  $scope.myGroupInfo = GroupFeed.data[index];
  console.log($scope.myGroupInfo);

  $scope.leave = function() {

  }

})

.controller('createGroupCtrl', function($scope, $state, $http, ProfileData) {
  $scope.form = {};
  $scope.form.date = new Date();

  var step = 1;
  $scope.form.createFirstStep = true;

  $scope.form.general = true;
  $scope.form.descriptionShow = false;
  $scope.form.locationDate = false;
  $scope.form.nextItem = true;
  $scope.form.lastItem = false;

  $scope.next = function() {
    switch(step) {
      case 1:
        $scope.form.general = false;
        $scope.form.locationDate = true;
        break;
      case 2:
        $scope.form.locationDate = false;
        $scope.form.descriptionShow = true;
        $scope.form.nextItem = false;
        $scope.form.lastItem = true;
        break;
    }
    step++;
    $scope.form.createFirstStep = false;
  }

  $scope.createGrup = function() {
    var data = {};

    data.user_id = ProfileData.data.user_id;

    if ($scope.form.group_name) {
      data.group_name = $scope.form.group_name;
    };
    if ($scope.form.professor) {
      data.professor = $scope.form.professor;
    };
    if ($scope.form.location_details) {
      data.location_details = $scope.form.location_details;
    };

    if ($scope.form.date) {
      var tempDate = new Date($scope.form.date);
      // var newTime = tempDate.getTime() - 18000000;
      // tempDate.setTime(newTime);

      var yearStr = tempDate.getUTCFullYear();
      var monthStr = tempDate.getUTCMonth()+1;
      var dayStr = tempDate.getUTCDate();
      var hourStr = tempDate.getUTCHours()-5;
      var minStr = tempDate.getUTCMinutes();
      var secStr = tempDate.getUTCSeconds();
      var dateStr = yearStr+'-'+monthStr+'-'+dayStr+' '+hourStr+':'+minStr+':'+secStr;

      data.time_of_meeting = dateStr;
    };
    if ($scope.form.description) {
      data.description = $scope.form.description;
    };
    
    // TODO: Use a regex function thing for this
    // Grabs the class subject and number from the input
    // and splits it up and stores it as class subject and number
    var tempName = $scope.form.class_name;
    if (tempName) {
      var nameNum = tempName.split(" ");

      data.class_subject = nameNum[0];
      data.class_number = nameNum[1];
    };
    
    console.log(data);
    console.log($scope.form.date);

    // Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/creategrup',
      // url: 'http://www.grupr.me/creategroup',
      url: 'http://54.213.15.90/creategroup',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      console.log(response.data);

      $state.go('tab.browse');
    });

    // Resets the values for the Creat Group form
    step = 1;
    $scope.form.createFirstStep = true;

    $scope.form.general = true;
    $scope.form.descriptionShow = false;
    $scope.form.locationDate = false;
    $scope.form.nextItem = true;
    $scope.form.lastItem = false;

    // Re-sets the input fields
    $scope.form.group_name = "";
    $scope.form.professor = "";
    $scope.form.location_details = "";
    $scope.form.date = new Date();
    $scope.form.description = "";
    $scope.form.class_name = "";
  }

  $scope.cancel = function() {
    // Resets the values for the Creat Group form
    step = 1;
    $scope.form.createFirstStep = true;

    $scope.form.general = true;
    $scope.form.descriptionShow = false;
    $scope.form.locationDate = false;
    $scope.form.nextItem = true;
    $scope.form.lastItem = false;

    // Re-sets the input fields
    $scope.form.group_name = "";
    $scope.form.professor = "";
    $scope.form.location_details = "";
    $scope.form.date = new Date();
    $scope.form.description = "";
    $scope.form.class_name = "";
    
    $state.go('tab.browse');
  }

  $scope.createGroupBack = function() {
    switch(step) {
      case 2:
        $scope.form.general = true;
        $scope.form.locationDate = false;

        $scope.form.createFirstStep = true;
        break;
      case 3:
        $scope.form.locationDate = true;
        $scope.form.descriptionShow = false;
        $scope.form.nextItem = true;
        $scope.form.lastItem = false;

        $scope.form.createFirstStep = false;
        break;
    }
    step--;
  }
})

.controller('addClassCtrl', function($scope,$state, $http, ProfileData, classes) {
	$scope.cancel = function() { 
    $state.go('tab.profile');
  }
	$scope.form = {};
	$scope.classes = classes.data.classes;
	$scope.form.class_subject = "ACCT";
	$scope.classes = ProfileData.data.classes;
	$scope.classesToTake = classes.data.classes;
	$scope.addClassSubmit = function() {
		var data = {};
		console.log($scope.form.class_subject.trim());
		console.log($scope.form.class_number);
		data.class_subject = $scope.form.class_subject.trim();
		data.class_number = $scope.form.class_number; 
		$scope.form.class_subject = "ACCT";
		$scope.form.class_number = "";
		data.user_id = ProfileData.data.user_id;
		console.log(data)
		// Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/login',
      // url: 'http://www.grupr.me/creategrup',
      url: 'http://54.213.15.90/addclass',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
		console.log(response.data);
     ProfileData.data.classes  = response.data;
     $scope.classes = ProfileData.data.classes;
      console.log(ProfileData.data.classes);
    });
	};
	
  $scope.done = function() {
    $state.go('tab.profile');
  }
})

.controller('editClassCtrl', function($scope,$state, $http, ProfileData, classes, editData) {
	console.log(editData.data.class_number); 
	$scope.class_subject_passed = editData.class_subject;
	$scope.class_number_passed = editData.class_number;
	$scope.cancel = function() { 
    $state.go('tab.profile');
  }
	$scope.form = {};
	$scope.classes = classes.data.classes;
	$scope.form.class_subject = "ACCT";
	$scope.classes = ProfileData.data.classes;
	$scope.classesToTake = classes.data.classes;
	$scope.editClassSubmit = function(){
		var data = {};
		console.log($scope.form.class_subject.trim());
		console.log($scope.form.class_number);
		data.class_subject_change_to = $scope.form.class_subject.trim();
		data.class_number_change_to = $scope.form.class_number; 
		data.class_subject_to_change = editData.data.class_subject;
		data.class_number_to_change = editData.data.class_number;
		$scope.form.class_subject = "ACCT";
		$scope.form.class_number = "";
		data.user_id = ProfileData.data.user_id;
		console.log(data)
		// Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/login',
      // url: 'http://www.grupr.me/creategrup',
      url: 'http://54.213.15.90/editclass',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
		console.log(response.data);
     ProfileData.data.classes  = response.data;
      console.log(ProfileData.data.classes);
	  $state.go('tab.profile',{}, {reload:true});
    });
	};
})

.controller('filterCtrl', function($scope,$state, $http, ProfileData, classes, GroupFeed) {
	$scope.user_classes = ProfileData.data.classes;
	$scope.filter = function(choice) {
	var data = {};
	data.class_subject = choice.class_subject; 
	data.class_number = choice.class_number;
	data.user_id = ProfileData.data.user_id;
	data.group_name = '';
	data.location = ''; 
	$http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/logout',
      // url: 'http://www.grupr.me/logout',
      url: 'http://54.213.15.90/filtergroups',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
     	 console.log("success");
		 console.log(response);
		GroupFeed.data = response.data;
    });
  }
  $scope.filterAll = function() {
	var data = {};
	data.user_id = ProfileData.data.user_id;
	$http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/logout',
      // url: 'http://www.grupr.me/logout',
      url: 'http://54.213.15.90/getuserclassesgroups',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
		console.log(response);
		GroupFeed.data = response.data;
	  console.log("success");
    });
  }
  $scope.done = function() {
    $state.go('tab.browse');
  }
})

.controller('LoginCtrl', function($scope, $state, $http, ProfileData) {
  $scope.form = {};

  //calls the login function, which puts the data entered in the login 
  //page and stores it into the 'data' object
  //This 'data' object is then passed to the POST request
  $scope.login = function() {
    var data = {};

    if ($scope.form.email) {
      data.email = $scope.form.email;
    };
    if ($scope.form.password) {
      data.password = $scope.form.password;
    };

    // resets the contents of the fields
    $scope.form.email = "";
    $scope.form.password = "";
    
    console.log(data);


    // Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/login',
      // url: 'http://www.grupr.me/login',
      url: 'http://54.213.15.90/login',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      if (response.data == 1) {
        console.log("something went wrong");
        alert("Incorrect Username or Password");
      }
      else {
        ProfileData.data = response.data;
        console.log(ProfileData.data);
        $state.go('tab.browse');
      }
    }, function errorCallback(response) {
      console.log("something went wrong");
    });

  }

  //Calls the 'register()' function, which is called when the 'Register' button
  // is clicked on the login page.
  //It takes the user to the Register page
  $scope.register = function() {
    $state.go('register');
  }
})

.controller('RegisterCtrl', function($scope, $state, $http, ProfileData) {
	$scope.form = {};

	//This function is called when the 'next >>' button is clicked at the 
	// bottom of the Register screen. It performs two functions:
	// 1) stores the data entered on the screen into the 'data' object
	// 2) advances the user to the Select Classes screen. This ensures that 
	//    the user is not able to complete registration without selecting classes
	$scope.register = function() {
		var data = {};

		if ($scope.form.first_name) {
		  data.first_name = $scope.form.first_name;
		};
		if ($scope.form.last_name) {
		  data.last_name = $scope.form.last_name;
		};
		if ($scope.form.email) {
		  data.email = $scope.form.email;
		};
		if ($scope.form.password) {
		  data.password = $scope.form.password;
		};

		console.log(data);

    // Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/register',
      // url: 'http://www.grupr.me/registeruser',
      url: 'http://54.213.15.90/registeruser',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      ProfileData.data = response.data;
      console.log(ProfileData.data.email);

      $state.go('registerClasses');
    });

	}
})
.controller('RegisterClassesCtrl', function($scope, $state, $http, ProfileData, classes) {
  $scope.registerClassesSkip = function() { 
    $scope.form.class_subject = "ACCT";
    $scope.form.class_number = "";

    $state.go('tab.browse');
  }

  $scope.form = {};
  $scope.classes = classes.data.classes;
  $scope.form.class_subject = "ACCT";
  $scope.classes = ProfileData.data.classes;
  $scope.classesToTake = classes.data.classes;
  $scope.addClassSubmit = function() {
    var data = {};
    console.log($scope.form.class_subject );
    console.log($scope.form.class_number);
    data.class_subject = $scope.form.class_subject.trim();
    data.class_number = $scope.form.class_number; 
    $scope.form.class_subject = "ACCT";
    $scope.form.class_number = "";
    data.user_id = ProfileData.data.user_id;
    console.log(data.user_id)
    // Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/login',
      // url: 'http://www.grupr.me/creategrup',
      url: 'http://54.213.15.90/addclass',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      ProfileData.data.classes  = response.data;
      $scope.classes = ProfileData.data.classes;
      console.log(ProfileData.data.classes);
    });
  };
  
  $scope.registerFinish = function() {
    $state.go('tab.browse');
  }
})

.controller('ProfileCtrl', function($scope,$state, $http,ProfileData, GroupFeed) {
  $scope.form = {};
  $scope.first_name = ProfileData.data.first_name;
  $scope.last_name = ProfileData.data.last_name;
  $scope.email = ProfileData.data.email;
  $scope.user_id = ProfileData.data.user_id;
  $scope.classes = ProfileData.data.classes;
  if (ProfileData.data.level < 10) {
    $scope.level = (ProfileData.data.level | 0);
  }
  else {
    $scope.level = 10;
  }
  $scope.icon = ProfileData.data.icon;
  $scope.editClass = function(classToEdit)
  {
	  editData.data = classToEdit;
	  console.log(editData);
	  $state.go('editClass');
  }
  $scope.deleteClass = function(classToDelete)
  {
	 var data = {};
	 data.user_id = ProfileData.data.user_id;
	 data.class_subject = classToDelete.class_subject;
	 data.class_number = classToDelete.class_number;
	// Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/logout',
      // url: 'http://www.grupr.me/logout',
      url: 'http://54.213.15.90/removeclass',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
	ProfileData.data.classes = response.data;
	$scope.classes = ProfileData.data.classes;
	console.log($scope.classes);
    }); 
	
  }
  $scope.addClass = function() 
  {
	$state.go('addClass');
  }
 $scope.filter = function() {
	$state.go('filter')
  }
  $scope.logout = function() {
    var data = ProfileData.user_id;

    // clears data stored in ProfileData
    ProfileData.data = null;
    GroupFeed.data = null;


    // Makes the POST http request
    $http({
      method: 'POST',
      // url: 'http://private-fa798-grupr.apiary-mock.com/logout',
      // url: 'http://www.grupr.me/logout',
      url: 'http://54.213.15.90/logout',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      // returns the user to the login screen
      $state.go('login');
    });  
  }
});
