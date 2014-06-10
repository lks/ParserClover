angular.module('listComponent', [])

	.directive('listItems', function() {
		return {
			restrict: 'E',
			transclude: true,
			scope: {},
			controller: function($scope, $element) {
				//call the good WS in function of the type
				$scope.message ="Test";
			},
			template:
				'<ul>' +
				'<li>' +
				'{{message}}' +
				'</li>' +
				'</ul>',
			replace: true
		};
	});