<?php
namespace de\mvo\router;

use de\mvo\renderer\DatesRenderer;
use de\mvo\renderer\JsonRenderer;
use de\mvo\renderer\PicturesRenderer;
use de\mvo\renderer\StaticRenderer;

class Endpoints
{
	public static function get()
	{
		return array
		(
			new Endpoint(HttpMethod::GET, "/", new StaticRenderer("home")),
			new Endpoint(HttpMethod::GET, "/impressum", new StaticRenderer("imprint")),
			new Endpoint(HttpMethod::GET, "/beitreten", new StaticRenderer("verein/beitreten")),
			new Endpoint(HttpMethod::GET, "/bisherige_dirigenten", new StaticRenderer("verein/bisherige_dirigenten")),
			new Endpoint(HttpMethod::GET, "/bisherige_erste_vorsitzende", new StaticRenderer("verein/bisherige_erste_vorsitzende")),
			new Endpoint(HttpMethod::GET, "/chronik", new StaticRenderer("verein/chronik")),
			new Endpoint(HttpMethod::GET, "/vereinsgeschichte", new StaticRenderer("verein/vereinsgeschichte")),
			new Endpoint(HttpMethod::GET, "/kontakt", new JsonRenderer("contact", "contact")),

			new Endpoint(HttpMethod::GET, "/foerderverein/kontakt", new JsonRenderer("contact", "foerderverein/contact")),

			// Dates
			new Endpoint(HttpMethod::GET, "/termine", new DatesRenderer(DatesRenderer::TYPE_HTML)),
			new Endpoint(HttpMethod::GET, "/termine.ics", new DatesRenderer(DatesRenderer::TYPE_ICAL)),
			new Endpoint(HttpMethod::GET, "/termine.pdf", new DatesRenderer(DatesRenderer::TYPE_PDF)),

			// Pictures
			new Endpoint(HttpMethod::GET, "/fotogalerie", new PicturesRenderer),
			new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]", new PicturesRenderer),
			new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]/[i:album]", new PicturesRenderer)
		);
	}
}