angular.module('starter.controllers', [])

.controller('BrowseCtrl', function($scope) {

})

.controller('ViewAllGrupsCtrl', function($scope) {

})

.controller('addClassCtrl', function($scope) {
  
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


    //My attempt at getting http POST to work. Not sure how to test this.
    // Ill re-visit this when I learn more about how to test it
    $http({
      method: 'POST',
      url: 'http://54.213.15.90/login',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function(response){
      ProfileData.data = response.data;
      console.log(ProfileData.data.status);
    });

    // post('', data);

    $state.go('tab.browse');
  }

  //Calls the 'register()' function, which is called when the 'Register' button
  // is clicked on the login page.
  //It takes the user to the Register page
  $scope.register = function() {
    $state.go('tab.register');
  }
})

.controller('RegisterCtrl', function($scope, $state) {
	$scope.form = {};

	//This function is called when the 'next >>' button is clicked at the 
	// bottom of the Register screen. It performs two functions:
	// 1) stores the data entered on the screen into the 'data' object
	// 2) advances the user to the Select Classes screen. This ensures that 
	//    the user is not able to complete registration without selecting classes
	$scope.next = function() {
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

    $http({
      method: 'POST',
      url: 'http://private-fa798-grupr.apiary-mock.com/register',
      headers: {
        'Content-Type': 'application/json'
        },
      data: data
    }).then(function(response){
      console.log(response);
    });

		$state.go('tab.selectClass');
	}
})

.controller('ProfileCtrl', function($scope) {
  $scope.settings = {
    enableFriends: true
  };
});