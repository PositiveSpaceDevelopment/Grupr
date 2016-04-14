// Ionic Starter App

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute in index.html)
// the 2nd parameter is an array of 'requires'
// 'starter.services' is found in services.js
// 'starter.controllers' is found in controllers.js
angular.module('starter', ['ionic', 'starter.controllers', 'starter.services'])

.run(function($ionicPlatform) {
  $ionicPlatform.ready(function() {
    // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
    // for form inputs)
    if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard) {
      cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
      cordova.plugins.Keyboard.disableScroll(true);

    }
    if (window.StatusBar) {
      // org.apache.cordova.statusbar required
      StatusBar.styleDefault();
    }
  });
})

.config(function($stateProvider, $urlRouterProvider) {

  // Ionic uses AngularUI Router which uses the concept of states
  // Learn more here: https://github.com/angular-ui/ui-router
  // Set up the various states which the app can be in.
  // Each state's controller can be found in controllers.js
  $stateProvider

  // setup an abstract state for the tabs directive
  .state('tab', {
    url: '/tab',
    abstract: true,
    templateUrl: 'templates/tabs.html'
  })

  // Each tab has its own nav history stack:


  .state('login', {
      url: '/login',
      templateUrl: 'templates/login.html',
      controller: 'LoginCtrl'
    })
	
   .state('addClass', {
      url: '/addClass',
      templateUrl: 'templates/addClass.html',
      controller: 'addClassCtrl'
    })
	.state('filter', {
      url: '/filter',
      templateUrl: 'templates/filter.html',
      controller: 'filterCtrl'
    })

  .state('register', {
      url: '/register',
      templateUrl: 'templates/register.html',
      controller: 'RegisterCtrl'
    })
  .state('createGroup', {
      url: '/createGroup',
      templateUrl: 'templates/createGroup.html',
      controller: 'createGroupCtrl'
    })

  .state('tab.browse', {
    url: '/browse',
    views: {
      'tab-browse': {
        templateUrl: 'templates/tab-browse.html',
        controller: 'BrowseCtrl'
      }
    }
  })

  .state('tab.groupDetail', {
    url: '/browse/:grupID',
    views: {
      'tab-browse': {
        templateUrl: 'templates/groupDetail.html',
        controller: 'GroupDetailCtrl'
      }
    }
  })
	
	.state('tab.ViewAllGrups', {
      url: '/ViewAllGrups',
      views: {
        'tab-ViewAllGrups': {
          templateUrl: 'templates/tab-ViewAllGrups.html',
          controller: 'ViewAllGrupsCtrl'
        }
      }
    })

  .state('tab.profile', {
    url: '/profile',
    views: {
      'tab-profile': {
        templateUrl: 'templates/tab-profile.html',
        controller: 'ProfileCtrl'
      }
    }
  });

  // if none of the above states are matched, use this as the fallback
  $urlRouterProvider.otherwise('/login');

});
