moment.locale("de");

angular.module("mvo", [
	"ngRoute",
	"mvo.home",
	"mvo.dates",
	"mvo.pictures",
	"mvo.verein"
])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/", {
			redirectTo: "/home"
		});

		$routeProvider.otherwise(
			{
				templateUrl: "modules/not-found/main.html"
			});
	}])
	.controller("AppController", function()
	{
		this.date = new Date();
	});