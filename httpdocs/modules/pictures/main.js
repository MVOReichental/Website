angular.module("mvo.pictures", ["ngRoute"])
	.config(["$routeProvider", function($routeProvider) {
		$routeProvider.when("/fotogalerie", {
			controller: "YearsController as yearsController",
			templateUrl: "/modules/pictures/years.html",
			resolve : {
				data: function($http)
				{
					return $http.get("service/pictures").then(function(response) {
						return response.data;
					});
				}
			}
		});
		$routeProvider.when("/fotogalerie/:year", {
			controller: "AlbumsController as albumsController",
			templateUrl: "/modules/pictures/albums.html",
			resolve : {
				data: function($http, $route)
				{
					return $http.get("service/pictures/" + $route.current.params.year).then(function(response) {
						return response.data;
					});
				}
			}
		});
		$routeProvider.when("/fotogalerie/:year/:album", {
			controller: "AlbumController as albumController",
			templateUrl: "/modules/pictures/album.html",
			resolve : {
				data: function($http, $route)
				{
					return $http.get("service/pictures/" + $route.current.params.year + "/" + $route.current.params.album).then(function(response) {
						return response.data;
					});
				}
			}
		});
		$routeProvider.when("/fotogalerie/:year/:album/:picture", {
			templateUrl: "/modules/pictures/picture.html"
		});
	}])
	.controller("YearsController", function(data)
	{
		this.years = data;
	})
	.controller("AlbumsController", function(data)
	{
		this.year = data.year;
		this.albums = data.albums;
	})
	.controller("AlbumController", function(data)
	{
		this.album = data;
	});