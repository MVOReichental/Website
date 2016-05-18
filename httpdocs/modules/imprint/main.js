angular.module("mvo.imprint", ["ngRoute"])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/impressum", {
			controller: "Controller",
			templateUrl: "modules/imprint/main.html"
		});
	}])
	.controller("Controller", function($scope)
	{
		$scope.webmasterMail = "webmaster@musikverein-reichental.de";
	});