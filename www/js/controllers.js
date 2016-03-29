angular.module('starter.controllers', [])

.controller('BrowseCtrl', function($scope) {})

.controller('ViewAllGrupsCtrl', function($scope) {})

.controller('addClassCtrl', function($scope) {
  
})

.controller('ChatDetailCtrl', function($scope, $stateParams, Chats) {
  $scope.chat = Chats.get($stateParams.chatId);
})

.controller('ProfileCtrl', function($scope) {
  $scope.settings = {
    enableFriends: true
  };
});
