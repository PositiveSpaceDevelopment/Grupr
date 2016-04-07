angular.module('starter.controllers', [])

.controller('BrowseCtrl', function($scope) {

})

.controller('ViewAllGrupsCtrl', function($scope) {

})

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
      url: 'http://private-fa798-grupr.apiary-mock.com/login',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function successCallback(response){
      ProfileData.data = response.data;
      console.log(ProfileData.data.classes[0].class_subject);
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

		if ($scope.form.firstName) {
		  data.firstName = $scope.form.firstName;
		};
		if ($scope.form.lastName) {
		  data.lastName = $scope.form.lastName;
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
      url: 'http://private-fa798-grupr.apiary-mock.com/register',
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
