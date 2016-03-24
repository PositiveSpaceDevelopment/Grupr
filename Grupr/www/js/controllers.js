angular.module('starter.controllers', [])

.controller('homeCtrl', function($scope, $state, $http, ProfileData) {
 $scope.form = {};                                                       
  {  
	user_id = $scope.user_id;
    $http({
      method: 'GET',
      url: '',               
      params: {                                                           
		user_id: user_id,
      }
    }).then(function successCallback(response) {
     ProfileData.data = response.data; 
	$scope.name = ProfileData.data.name;
  $scope.email = ProfileData.data.email;
  $scope.level = ProfileData.data.level;
  $scope.classes = ProfileData.data.classes;
      console.log("data--")                                  
      $state.go('app.profile');                                            
    });   
  }
})

.controller('profileCtrl', function($scope, $stateParams, ProfileData) {   
  $scope.name = ProfileData.data.name;
  $scope.email = ProfileData.data.email;
  $scope.level = ProfileData.data.level;
  $scope.classes = ProfileData.data.classes;
  
  $scope.gotoOtherPage = function(){
  };
  console.log("found this user", $scope.user);
})



.controller('profileCtrl', function($scope, $state, $http, ProfileData) {
  $scope.form = {};                                                       
  {  

	user_id = $scope.user_id;
    $http({
      method: 'GET',
      url: '',               
      params: {                                                           
		user_id: user_id,
      }
    }).then(function successCallback(response) {
      ProfileData.data = response.data; 

      console.log("data--")                                  
      $state.go('app.profile');                                            
    });   
  }
})

.controller('profileCtrl', function($scope, $stateParams, ProfileData) {   
  
  $scope.name = ProfileData.data[0];
  $scope.email = ProfileData.data[1];
  $scope.level = ProfileData.data[2];
  $scope.classes = ProfileData.data[3];
  
  console.log("found this user", $scope.user);
 });