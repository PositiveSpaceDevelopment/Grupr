angular.module('starter.controllers', [])

.controller('BrowseCtrl', function($scope) {

})

.controller('ViewAllGrupsCtrl', function($scope) {

})

.controller('createGroupCtrl', function($scope, $state, $http, ProfileData) {
  $scope.form = {};

  $scope.createGrup = function() {
    var data = {};

    data.user_id = ProfileData.data.user_id;

    if ($scope.form.group_name) {
      data.group_name = $scope.form.group_name;
    };
    if ($scope.form.location) {
      data.building = $scope.form.location;
    };
    if ($scope.form.location_details) {
      data.location_details = $scope.form.location_details;
    };

    // TODO: This date/time thing needs to be fixed for proper format
    // and data entry
    if ($scope.form.meeting_time) {
      data.time_of_meeting = $scope.form.date;
    };
    if ($scope.form.description) {
      data.description = $scope.form.description;
    };
    
    // TODO: Use a regex function thing for this
    // Grabs the class subject and number from the input
    // and splits it up and stores it as class subject and number
    var tempName = $scope.form.class_name;
    var nameNum = tempName.split(" ");

    data.class_subject = nameNum[0];
    data.class_number = nameNum[1];

    console.log(data);

    // Makes the POST http request
    $http({
      method: 'POST',
      url: 'http://private-fa798-grupr.apiary-mock.com/creategrup',
      // url: 'http://www.grupr.me/creategrup',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      console.log("Status: " + response.data.status);
    });

  }
.controller('addClassCtrl', function($scope,$state, $http, ProfileData, classes) {
	$scope.form = {};
	$scope.first_name = ProfileData.data.first_name;
	  $scope.last_name = ProfileData.data.last_name;
	  $scope.email = ProfileData.data.email;
	  $scope.user_id = ProfileData.data.user_id;
	  $scope.user_classes = ProfileData.data.classes;
	  $scope.classes = classes.data.classes;
	  $scope.level = ProfileData.data.level;
	  $scope.icon = ProfileData.data.icon;
	$scope.form.class_subject = "ACCT";
	  
	$scope.addClassSubmit = function() {
		var data = {};
		console.log($scope.form.class_subject );
		console.log($scope.form.class_number);
		data.class_subject = $scope.form.class_subject;
		data.class_number = $scope.form.class_nubmber; 
		$scope.form.class_subject = "ACCT";
		$scope.form.class_number = "";
    
	};
	
  
})

.controller('ChatDetailCtrl', function($scope, $stateParams, Chats) {
  $scope.chat = Chats.get($stateParams.chatId);
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
      url: 'http://www.grupr.me/login',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      ProfileData.data = response.data;
      console.log(ProfileData.data);
      console.log(ProfileData.data.user_id);
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
      url: 'http://www.grupr.me/registeruser',
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

.controller('ProfileCtrl', function($scope,$state, $http,ProfileData) {
  $scope.form = {};
  $scope.first_name = ProfileData.data.first_name;
  $scope.last_name = ProfileData.data.last_name;
  $scope.email = ProfileData.data.email;
  $scope.user_id = ProfileData.data.user_id;
  $scope.classes = ProfileData.data.classes;
  $scope.level = ProfileData.data.level;
  $scope.icon = ProfileData.data.icon;
  
    $scope.addClass = function() {
    $state.go('addClass');
  }
});
