angular.module('starter.controllers', [])

.controller('AppCtrl', function($scope, $ionicModal, $timeout) {

  // With the new view caching in Ionic, Controllers are only called
  // when they are recreated or on app start, instead of every page change.
  // To listen for when this page is active (for example, to refresh data),
  // listen for the $ionicView.enter event:
  //$scope.$on('$ionicView.enter', function(e) {
  //});

})

.controller('PlaylistsCtrl', function($scope) {
  $scope.playlists = [
    { title: 'Reggae', id: 1 },
    { title: 'Chill', id: 2 },
    { title: 'Dubstep', id: 3 },
    { title: 'Indie', id: 4 },
    { title: 'Rap', id: 5 },
    { title: 'Cowbell', id: 6 }
  ];
})

//Controller for the Login Screen
.controller('LoginCtrl', function($scope, $state) {
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
    
    console.log(data);


    // //My attempt at getting http POST to work. Not sure how to test this.
    // // Ill re-visit this when I learn more about how to test it
    // $http({
    //   method: 'POST',
    //   url: '',
    //   headers: {
    //     'Content-Type': JSON
    //     },
    //   data: data
    // }).then(function(){

    // });

    // post('', data);

  }

  //Calls the 'register()' function, which is called when the 'Register' button
  // is clicked on the login page.
  //It takes the user to the Register page
  $scope.register = function() {
    $state.go('app.register');
  }

})

// Controller for the Register Screen
.controller('RegisterCtrl', function($scope, $state) {
  $scope.form = {};

  //This function is called when the 'next >>' button is clicked at the 
  // bottom of the Register screen. It performs two functions:
  // 1) stores the data entered on the screen into the 'data' object
  // 2) advances the user to the Select Classes screen. This ensures that 
  //    the user is not able to complete registration without selecting classes
  $scope.next = function() {
    var data = {};

    if ($scope.form.name) {
      data.name = $scope.form.name;
    };
    if ($scope.form.email) {
      data.email = $scope.form.email;
    };
    if ($scope.form.password) {
      data.password = $scope.form.password;
    };
    
    console.log(data);

    $state.go('app.selectClass');
  }
})

.controller('SelectClassCtrl', function($scope, $state) {
  

})




