parserApp.service('barGraph', function () {
    /**
     * Init the param for my given class
     *
     * @param  data       Stat generated to display
     * @param  transition Type of the transition
     * @param  classCss   Class used for my template
     * @param  element    Element we work on
     * @return none
     */
    this.generate = function (data, transition, classCss, element) {

        var chart = d3.select(element);

        //to our original directive markup bars-chart
        //we add a div with out chart stling and bind each
        //data entry to the chart
        chart.append("div").attr("class", classCss)
            .selectAll('div')
            .data(data).enter().append("div")
            .transition().ease(transition)
            .style("width", function (d) {
                return d + "%";
            })
            .text(function (d) {
                return d + "%";
            });
    }
    this.remove = function (classCss, element) {
        var chart = d3.select(element);
        chart.selectAll("." + classCss).remove();
    }
});

angular.module('components', [])
/**
 * List directive will get all files metrics and will display it by two ways:
 * - Raw listing,
 * - Bar Graph format.
 */
    .directive('list', function () {
        return {
            restrict: 'E',
            scope: {},
            controller: function ($scope, $http, $attrs, $element, barGraph) {
                url = "http://192.168.56.101/all";
                $http({
                    url: url,
                    method: "get"
                }).success(function (data, status, headers, config) {
                    $scope.list = data.data.rows;
                    $scope.stat = data.stat;

                    //init my graph object and generate it
                    barGraph.remove("chart", "#graph");
                    barGraph.generate($scope.stat, "elastic", "chart", "#graph");

                }).error(function (data, status, headers, config) {
                    $scope.data = data;
                    $scope.status = status;
                });
            },
            templateUrl: 'web/partials/templates/list.html'
        };
    })
/**
 * listType directive will display for the given type of class the metrics associated.
 * As for the list directive, we will display it by two ways (listing and bar graph).
 */
    .directive('listType', function () {
        return {
            restrict: 'E',
            scope: {},
            controller: function ($scope, $http, $attrs, $element, barGraph) {

                $scope.$watch(function () {
                    return $attrs.type;
                }, function (newValue, oldValue) {
                    if ($attrs.type != null) {
                        url = "http://192.168.56.101/type/" + $attrs.type;
                        $http({
                            url: url,
                            method: "get"
                        }).success(function (data, status, headers, config) {
                            $scope.list = data.data;
                            $scope.stat = data.stat;

                            //init my graph object and generate it
                            barGraph.remove("chart", "#graph-custom");
                            barGraph.generate($scope.stat, "elastic", "chart", "#graph-custom");
                        }).error(function (data, status, headers, config) {
                            $scope.data = data;
                            $scope.status = status;
                        });
                    }
                });
            },
            templateUrl: 'web/partials/templates/listcustom.html'
        };
    })
/**
 * listType directive will display for the given bundle the metrics associated.
 * As for the list directive, we will display it by two ways (listing and bar graph).
 */
    .directive('listbundle', function () {
        return {
            restrict: 'E',
            scope: {},
            controller: function ($scope, $http, $attrs) {
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
