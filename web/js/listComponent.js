angular.module('components', [])
	.directive('list', function() {
		return {
			restrict: 'E',
			scope: {},
			controller: function($scope, $http, $attrs, $element) {
				url = "http://192.168.56.101/all";
				$http({
		            url: url,
		            method: "get"
		        }).success(function (data, status, headers, config) {
								$scope.list = data.data.rows;
								$scope.stat = data.stat;
								//in D3, any selection[0] contains the group
								//selection[0][0] is the DOM node
								//but we won't need that this time
								var chart = d3.select('#graph');
								//to our original directive markup bars-chart
								//we add a div with out chart stling and bind each
								//data entry to the chart
								console.log($scope.stat);
								chart.append("div").attr("class", "chart")
									.selectAll('div')
									.data($scope.stat).enter().append("div")
									.transition().ease("elastic")
									.style("width", function(d) { return d + "%"; })
									.text(function(d) { return d + "%"; });
								//a little of magic: setting it's width based
								//on the data value (d)
								//and text all with a smooth transition
		        }).error(function (data, status, headers, config) {
		            $scope.data = data;
		            $scope.status = status;
		        });
			},
			templateUrl: 'web/partials/templates/list.html'
		};
	})
	.directive('listtype', function() {
		return {
			restrict: 'E',
			scope: {},
			controller: function($scope, $http, $attrs) {
				url = "http://192.168.56.101/type/" + $attrs.type;
				$http({
		            url: url,
		            method: "get"
		        }).success(function (data, status, headers, config) {
					$scope.list = data;
		        }).error(function (data, status, headers, config) {
		            $scope.data = data;
		            $scope.status = status;
		        });
			},
			templateUrl: 'web/partials/templates/listcustom.html'
		};
	})
	.directive('listbundle', function() {
		return {
			restrict: 'E',
			scope: {},
			controller: function($scope, $http, $attrs) {
				url = "http://192.168.56.101/bundle/" + $attrs.bundle;
				$http({
		            url: url,
		            method: "get"
		        }).success(function (data, status, headers, config) {
					$scope.list = data;
		        }).error(function (data, status, headers, config) {
		            $scope.data = data;
		            $scope.status = status;
		        });
			},
			templateUrl: 'web/partials/templates/listcustom.html'
		};
	});
