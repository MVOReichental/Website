angular.module("mvo.foerderverein", ["ngRoute"])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/foerderverein/warum_foerderverein", {
			templateUrl: "/modules/foerderverein/warum_foerderverein.html"
		});
		$routeProvider.when("/foerderverein/vorstand", {
			templateUrl: "/modules/foerderverein/vorstand.html"
		});
		$routeProvider.when("/foerderverein/kontakt", {
			controller: "mvo.foerderverein.ContactController",
			templateUrl: "/modules/contact/main.html"
		});
	}])
	.controller("mvo.foerderverein.ContactController", function($scope)
	{
		$scope.address =
		{
			title: "Förderverein Musikverein \"Orgelfels\" Reichental e.V.",
			details: "Guido Wieland\nS\u00fcdhangstr. 25\n76593 Gernsbach - Reichental"
		};

		$scope.contacts = [
			{
				name: "Vorstand (Gleichberechtigt)",
				text: "Ulrike Brasseur (07224 67274)\nGuido Wieland (07224 40749)",
				mailbox: "vorstand-fv"
			},
			{
				name: "Kassenverwalter",
				text: "Florian Wieland",
				mailbox: "kassier-fv"
			},
			{
				name: "Schriftf\u00fchrer",
				text: "Patrick Wieland",
				mailbox: "schriftfuehrer-fv"
			}
		];
	});