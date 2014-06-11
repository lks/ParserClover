angular.module('components', [])
	.directive('list', function() {
		return {
			restrict: 'E',
			scope: {},
			controller: function($scope, $http, $attrs) {
				url = "http://192.168.56.101/all";
				if($attrs.type != null) {
					url = "http://192.168.56.101/type/" + $attrs.type;
				}
				$http({
		            url: url,
		            method: "get"
		        }).success(function (data, status, headers, config) {
		        	if($attrs.type != null) {
						$scope.list = data;
					} else {
						$scope.list = data.rows;
					}
		            
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