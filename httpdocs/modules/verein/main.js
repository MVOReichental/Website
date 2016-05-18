angular.module("mvo.verein", ["ngRoute"])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/chronik", {
			templateUrl: "/modules/verein/chronik.html"
		});
		$routeProvider.when("/vereinsgeschichte", {
			templateUrl: "/modules/verein/vereinsgeschichte.html"
		});
		$routeProvider.when("/bisherige_erste_vorsitzende", {
			templateUrl: "/modules/verein/bisherige_erste_vorsitzende.html"
		});
		$routeProvider.when("/bisherige_dirigenten", {
			templateUrl: "/modules/verein/bisherige_dirigenten.html"
		});
		$routeProvider.when("/beitreten", {
			templateUrl: "/modules/verein/beitreten.html"
		});
		$routeProvider.when("/kontakt", {
			controller: "ContactController",
			templateUrl: "/modules/contact/main.html"
		});
	}])
	.controller("ContactController", function($scope)
	{
		$scope.address =
		{
			title: "Musikverein \"Orgelfels\" Reichental e.V.",
			details: "Birgit Gerweck\nNeuer Weg 13/1\n76593 Gernsbach - Reichental"
		};

		$scope.contacts = [
			{
				name: "Vorstand (Gleichberechtigt)",
				text: "Erhard Klumpp (07224 65 26 83)\nBirgit Gerweck (07224 99 69 822)",
				mailbox: "vorstand"
			},
			{
				name: "Musikervorstand",
				text: "Karl Fortenbacher\nDaniela Zapf",
				mailbox: "musikervorstand"
			},
			{
				name: "Kassenverwalterin",
				text: "Katrin H\u00f6rth",
				mailbox: "kassier"
			},
			{
				name: "Schriftf\u00fchrerin",
				text: "Heike Kast",
				mailbox: "schriftfuehrer"
			},
			{
				name: "Jugendleiterin",
				text: "Gisela Wieland",
				mailbox: "jugendleiter"
			},
			{
				name: "\u00d6ffentlichkeitsarbeit",
				text: "Edith Wieland",
				mailbox: "oeffentlichkeitsarbeit"
			},
			{
				name: "Webmaster",
				text: "Michael Wieland",
				mailbox: "webmaster"
			}
		];
	});