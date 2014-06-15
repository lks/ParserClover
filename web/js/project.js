var parserApp = angular.module('parserApp', ['components','ui.bootstrap','ui.bootstrap.alert','ngRoute', 'ngResource'])
.directive('barsChart', function ($parse) {
     //explicitly creating a directive definition variable
     //this may look verbose but is good for clarification purposes
     //in real life you'd want to simply return the object {...}
     var directiveDefinitionObject = {
         //We restrict its use to an element
         //as usually  <bars-chart> is semantically
         //more understandable
         restrict: 'E',
         //this is important,
         //we don't want to overwrite our directive declaration
         //in the HTML mark-up
         replace: false,
         //our data source would be an array
         //passed thru chart-data attribute
         scope: {dataTest: '=chartData'},
         link: function (scope, element, attrs) {
           //in D3, any selection[0] contains the group
           //selection[0][0] is the DOM node
           //but we won't need that this time
           var chart = d3.select(element[0]);
           //to our original directive markup bars-chart
           //we add a div with out chart stling and bind each
           //data entry to the chart
           console.log(dataTest);
            chart.append("div").attr("class", "chart")
             .selectAll('div')
             .data(scope.dataTest).enter().append("div")
             .transition().ease("elastic")
             .style("width", function(d) { return d + "%"; })
             .text(function(d) { return d + "%"; });
           //a little of magic: setting it's width based
           //on the data value (d)
           //and text all with a smooth transition
         }
      };
      return directiveDefinitionObject;
   });

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
  $scope.myData = [10,20,30,40,60];
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
