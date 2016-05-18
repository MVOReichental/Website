angular.module("mvo.verein", ["ngRoute"])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/chronik", {
			templateUrl: "/modules/verein/chronik.html"
		});
		$routeProvider.when("/vereinsgeschichte", {
			templateUrl: "/modules/verein/vereinsgeschichte.html"
		});
		$routeProvider.when("/kontakt", {
			templateUrl: "/modules/verein/kontakt.html"
		});
	}]);