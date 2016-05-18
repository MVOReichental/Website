angular.module("mvo.home", ["ngRoute"])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/home", {
			templateUrl: "modules/home/main.html"
		});
	}]);