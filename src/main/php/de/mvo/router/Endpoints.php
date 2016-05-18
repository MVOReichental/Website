<?php
namespace de\mvo\router;

use de\mvo\service\Dates;
use de\mvo\service\Pictures;

class Endpoints
{
	public static function get()
	{
		return array
		(
			new Endpoint(HttpMethod::GET, "/dates/years", new Target(Dates::class, "getYears")),
			new Endpoint(HttpMethod::GET, "/dates/current", new Target(Dates::class, "getCurrentDates")),
			new Endpoint(HttpMethod::GET, "/dates/[i:year]", new Target(Dates::class, "getDatesForYear")),

			new Endpoint(HttpMethod::GET, "/pictures", new Target(Pictures::class, "getYears")),
			new Endpoint(HttpMethod::GET, "/pictures/[i:year]", new Target(Pictures::class, "getAlbums")),
			new Endpoint(HttpMethod::GET, "/pictures/[i:year]/[i:album]", new Target(Pictures::class, "getAlbumDetails"))
		);
	}
}