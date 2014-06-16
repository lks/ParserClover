var parserApp = angular.module('parserApp', ['components','ui.bootstrap','ui.bootstrap.alert','ngRoute', 'ngResource']);

parserApp.config(function ($httpProvider) {
  $httpProvider.responseInterceptors.push('myHttpInterceptor');

  var spinnerFunction = function spinnerFunction(data, headersGetter) {
    $("#spinner").show();
    return data;
  };

  $httpProvider.defaults.transformRequest.push(spinnerFunction);
});

parserApp.factory('myHttpInterceptor', function ($q, $window) {
  return function (promise) {
    return promise.then(function (response) {
      $("#spinner").hide();
      return response;
    }, function (response) {
      $("#spinner").hide();
      return $q.reject(response);
    });
  };
});


var PARTIALS_PATH = '/web/partials/';

// configure our routes
parserApp.config(function($routeProvider) {
  $routeProvider

    // route for the home page
    .when('/', {
      templateUrl : PARTIALS_PATH + 'home.html',
      controller  : 'mainController'
    })

    // route for the about page
    .when('/load', {
      templateUrl : PARTIALS_PATH + 'load.html',
      controller  : 'LoadCtrl'
    })

    .when('/all', {
      templateUrl : PARTIALS_PATH + 'list.html',
      controller  : 'SearchAllCtrl'
    })
});

// create the controller and inject Angular's $scope
parserApp.controller('mainController', function($scope) {
  $scope.type = "Controller";
});


