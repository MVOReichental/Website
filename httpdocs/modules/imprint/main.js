angular.module("mvo.imprint", ["ngRoute"])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/impressum", {
			controller: "mvo.imprint.Controller",
			templateUrl: "modules/imprint/main.html"
		});
	}])
	.controller("mvo.imprint.Controller", function($scope)
	{
		$scope.webmasterMail = "webmaster@musikverein-reichental.de";
	});