<?php
namespace de\mvo\router;

use de\mvo\renderer\DatesRenderer;
use de\mvo\renderer\FileRenderer;
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
			new Endpoint(HttpMethod::GET, "/beitreten/beitrittserklaerung.pdf", new FileRenderer(RESOURCES_ROOT . "/beitrittserklaerung.pdf", "application/pdf")),
			new Endpoint(HttpMethod::GET, "/bisherige_dirigenten", new StaticRenderer("verein/bisherige_dirigenten")),
			new Endpoint(HttpMethod::GET, "/bisherige_erste_vorsitzende", new StaticRenderer("verein/bisherige_erste_vorsitzende")),
			new Endpoint(HttpMethod::GET, "/chronik", new StaticRenderer("verein/chronik")),
			new Endpoint(HttpMethod::GET, "/vereinsgeschichte", new StaticRenderer("verein/vereinsgeschichte")),
			new Endpoint(HttpMethod::GET, "/vorstand", new StaticRenderer("verein/vorstand")),
			new Endpoint(HttpMethod::GET, "/kontakt", new JsonRenderer("contact", "contact")),

			new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildung_im_verein", new StaticRenderer("jugendausbildung/ausbildung_im_verein")),
			new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildungsgruppen", new JsonRenderer("jugendausbildung/ausbildungsgruppen", "jugendausbildung/ausbildungsgruppen")),
			new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildungsvereinbarung.pdf", new FileRenderer(RESOURCES_ROOT . "/ausbildungsvereinbarung.pdf", "application/pdf")),

			new Endpoint(HttpMethod::GET, "/foerderverein/warum_foerderverein", new StaticRenderer("foerderverein/warum_foerderverein")),
			new Endpoint(HttpMethod::GET, "/foerderverein/vorstand", new StaticRenderer("foerderverein/vorstand")),
			new Endpoint(HttpMethod::GET, "/foerderverein/kontakt", new JsonRenderer("contact", "foerderverein/contact")),

			// Dates
			new Endpoint(HttpMethod::GET, "/termine", new DatesRenderer(DatesRenderer::TYPE_HTML)),
			new Endpoint(HttpMethod::GET, "/termine.ics", new DatesRenderer(DatesRenderer::TYPE_ICAL)),

			// Pictures
			new Endpoint(HttpMethod::GET, "/fotogalerie", new PicturesRenderer),
			new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]", new PicturesRenderer),
			new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]/[:album]", new PicturesRenderer)
		);
	}
}