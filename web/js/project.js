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
    .when('/pmd', {
      templateUrl : PARTIALS_PATH + 'templates/listPmd.html',
      controller  : 'PmdCtrl'
    })
});

// create the controller and inject Angular's $scope
parserApp.controller('mainController', function($scope) {
  $scope.type = "Controller";
});

// create the controller and inject Angular's $scope
parserApp.controller('PmdCtrl', function($scope,  $http) {
  url = "http://192.168.56.101/report";
        $http({
                url: url,
                method: "get"
            }).success(function (data, status, headers, config) {
          $scope.list = data;
        });
});



