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
	$scope.viewGroup = function(id) {
    $state.go("tab.groupDetail",{grupID: id});
  }
	var data = {};
	data.user_id = ProfileData.data.user_id;
	// Makes the POST http request
    $http({
      method: 'POST',
	  
      // url: 'http://private-fa798-grupr.apiary-mock.com/login',
      // url: 'http://www.grupr.me/creategrup',
      url: 'http://54.213.15.90/getusergroups',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      UserGroups.data = response.data;
	  $scope.UserGroups = response.data;
	  console.log($scope.UserGroups);
      console.log(UserGroups.data);
    });
	
  $scope.newGroup = function() {
    $state.go('createGroup');
  }

  $scope.filter = function() {
	$state.go('filter')
  }

  $scope.viewGroup = function(id) {
    $state.go("tab.groupDetail",{grupID: id});
  }
})

.controller('createGroupCtrl', function($scope, $state, $http, ProfileData) {
  $scope.form = {};

  var step = 1;
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
  }

  $scope.createGrup = function() {
    var data = {};

    data.user_id = ProfileData.data.user_id;

    if ($scope.form.group_name) {
      data.group_name = $scope.form.group_name;
    };
    if ($scope.form.location) {
      data.location = $scope.form.location;
    };
    if ($scope.form.location_details) {
      data.location_details = $scope.form.location_details;
    };

    if ($scope.form.date) {
      data.time_of_meeting = $scope.form.date;
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
    var step = 1;
    $scope.form.general = true;
    $scope.form.descriptionShow = false;
    $scope.form.locationDate = false;
    $scope.form.nextItem = true;
    $scope.form.lastItem = false;
  }

  $scope.cancel = function() {
    // Resets the values for the Creat Group form
    var step = 1;
    $scope.form.general = true;
    $scope.form.descriptionShow = false;
    $scope.form.locationDate = false;
    $scope.form.nextItem = true;
    $scope.form.lastItem = false;
    
    $state.go('tab.browse');
  }
})

.controller('addClassCtrl', function($scope,$state, $http, ProfileData, classes) {
	
	$scope.form = {};
	$scope.classes = classes.data.classes;
	$scope.form.class_subject = "ACCT";
	$scope.classes = ProfileData.data.classes;
	$scope.classesToTake = classes.data.classes;
	$scope.addClassSubmit = function() {
		var data = {};
		console.log($scope.form.class_subject );
		console.log($scope.form.class_number);
		data.class_subject = $scope.form.class_subject;
		data.class_number = $scope.form.class_nubmber; 
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
      console.log(ProfileData.data.classes);
    });
	};
	
  $scope.done = function() {
    $state.go('tab.profile');
  }
})
.controller('filterCtrl', function($scope,$state, $http, ProfileData, classes) {
	  $scope.user_classes = ProfileData.data.classes;
	  
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
      ProfileData.data = response.data;
      console.log(ProfileData.data);
       $state.go('tab.browse');
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
      url: 'http://54.213.15.90/creategroup',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      ProfileData.data = response.data;
      console.log(ProfileData.data.email);

      $state.go('tab.profile');
    });

	}
})

.controller('ProfileCtrl', function($scope,$state, $http,ProfileData, GroupFeed) {
  $scope.form = {};
  $scope.first_name = ProfileData.data.first_name;
  $scope.last_name = ProfileData.data.last_name;
  $scope.email = ProfileData.data.email;
  $scope.user_id = ProfileData.data.user_id;
  $scope.classes = ProfileData.data.classes;
  $scope.level = ProfileData.data.level;
  $scope.icon = ProfileData.data.icon;
  
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
