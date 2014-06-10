var parserApp = angular.module('parserApp', ['listComponent','ui.bootstrap','ui.bootstrap.alert','ngRoute', 'ngResource']);

// Spinner for all pages
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
  // create a message to display in our view
  $scope.message = 'Everyone come and see how good I look!';
});

parserApp.controller("WineIndexCtrl", function($scope, Wine, $modal, $log) {

  Wine.query(function(data) {
    $scope.wines = data;
  });

  $scope.closeAlert = function(index) {
    $scope.alerts.splice(index, 1);
  };

  $scope.open = function (wine, type) {
    var controller = "WineDeleteCtrl";
    var templateUrl = 'myModalContent.html';

    if(type == 'detail') {
      controller = "WineDetailCtrl";
      templateUrl = "myModalDetailContent.html";
    }

    var modalInstance = $modal.open({
      templateUrl: templateUrl,
      controller: controller,
      resolve: {
        wine: function() {
          $log.info('Modal dismissed at: ' + wine.name);
          return wine;
        }
      }
    });
  };
});
