moment.locale("de");

angular.module("mvo", [
	"ngRoute",
	"mvo.home",
	"mvo.imprint",
	"mvo.dates",
	"mvo.pictures",
	"mvo.foerderverein",
	"mvo.verein"
])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/", {
			redirectTo: "/home"
		});

		$routeProvider.otherwise(
			{
				templateUrl: "modules/app/not-found.html"
			});
	}])
	.controller("mvo.AppController", function($scope)
	{
		$scope.date = new Date();
	});