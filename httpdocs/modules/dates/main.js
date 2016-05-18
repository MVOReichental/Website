angular.module("mvo.dates", ["ngRoute"])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/termine", {
			redirectTo: "/termine/aktuell"
		});
		$routeProvider.when("/termine/aktuell", {
			controller: "mvo.dates.DatesController as datesController",
			templateUrl: "/modules/dates/list.html",
			resolve : {
				data: function($http)
				{
					return $http.get("service/dates/current").then(function(response) {
						return response.data;
					});
				}
			}
		});
		$routeProvider.when("/termine/:year", {
			controller: "mvo.dates.DatesController as datesController",
			templateUrl: "/modules/dates/list.html",
			resolve : {
				data: function($http, $route)
				{
					return $http.get("service/dates/" + $route.current.params.year).then(function(response) {
						return response.data;
					});
				}
			}
		});
	}])
	.controller("mvo.dates.DatesController", function($scope, $route, data)
	{
		$scope.sortType = "startDate";
		$scope.sortReverse = false;

		this.year = $route.current.params.year;

		for (var index = 0; index < data.length; index++)
		{
			var date = data[index];

			date.startDate = moment(date.startDate);
			date.endDate = (date.endDate === null ? null : moment(date.endDate));
		}

		this.dates = data;
	})
	.controller("mvo.dates.YearsController", function($http)
	{
		var self = this;

		$http.get("service/dates/years").then(function(response) {
			self.years = response.data;
		});
	});