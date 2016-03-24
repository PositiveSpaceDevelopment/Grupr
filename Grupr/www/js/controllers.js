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

.controller('LoginCtrl', function($scope, $state) {
  $scope.form = {};

  $scope.login = function() {
    var data = {};

    
    data.email = $scope.form.email;
    console.log(data.email);
  }


  $scope.register = function() {
    $state.go('app.register');
  }

})

.controller('RegisterCtrl', function($scope, $state) {
  $scope.next = function() {
    $state.go('app.selectClass');
  }
})

