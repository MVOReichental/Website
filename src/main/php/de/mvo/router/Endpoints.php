<?php
namespace de\mvo\router;

use de\mvo\service\Dates;
use de\mvo\service\File;
use de\mvo\service\JsonView;
use de\mvo\service\GroupMembers;
use de\mvo\service\News;
use de\mvo\service\Pictures;
use de\mvo\service\ProfilePicture;
use de\mvo\service\StaticView;

class Endpoints
{
	public static function get()
	{
		return array
		(
			new Endpoint(HttpMethod::GET, "/", new Target(StaticView::class, "get", ["home"])),
			new Endpoint(HttpMethod::GET, "/aktuell", new Target(News::class, "get")),
			new Endpoint(HttpMethod::GET, "/impressum", new Target(StaticView::class, "get", ["imprint"])),
			new Endpoint(HttpMethod::GET, "/beitreten", new Target(StaticView::class, "get", ["verein/beitreten"])),
			new Endpoint(HttpMethod::GET, "/beitreten/beitrittserklaerung.pdf", new Target(File::class, "get", [RESOURCES_ROOT . "/beitrittserklaerung.pdf", "application/pdf"])),
			new Endpoint(HttpMethod::GET, "/bisherige_dirigenten", new Target(StaticView::class, "get", ["verein/bisherige_dirigenten"])),
			new Endpoint(HttpMethod::GET, "/bisherige_erste_vorsitzende", new Target(StaticView::class, "get", ["verein/bisherige_erste_vorsitzende"])),
			new Endpoint(HttpMethod::GET, "/chronik", new Target(StaticView::class, "get", ["verein/chronik"])),
			new Endpoint(HttpMethod::GET, "/vereinsgeschichte", new Target(StaticView::class, "get", ["verein/vereinsgeschichte"])),
			new Endpoint(HttpMethod::GET, "/vorstand", New Target(GroupMembers::class, "get", ["Vorstand", "vorstandschaft"])),
			new Endpoint(HttpMethod::GET, "/musiker", New Target(GroupMembers::class, "get", ["Musiker", "musicians"])),
			new Endpoint(HttpMethod::GET, "/kontakt", new Target(JsonView::class, "get", ["contact", "contact"])),

			new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildung_im_verein", new Target(StaticView::class, "get", ["jugendausbildung/ausbildung_im_verein"])),
			new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildungsgruppen", new Target(JsonView::class, "get", ["jugendausbildung/ausbildungsgruppen", "jugendausbildung/ausbildungsgruppen"])),
			new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildungsvereinbarung.pdf", new Target(File::class, "get", [RESOURCES_ROOT . "/ausbildungsvereinbarung.pdf", "application/pdf"])),

			new Endpoint(HttpMethod::GET, "/foerderverein/warum_foerderverein", new Target(StaticView::class, "get", ["foerderverein/warum_foerderverein"])),
			new Endpoint(HttpMethod::GET, "/foerderverein/vorstand", New Target(GroupMembers::class, "get", ["Der Vorstand des Fördervereins", "foerderverein"])),
			new Endpoint(HttpMethod::GET, "/foerderverein/kontakt", new Target(JsonView::class, "get", ["contact", "foerderverein/contact"])),

			// Dates
			new Endpoint(HttpMethod::GET, "/termine", new Target(Dates::class, "getHtml")),
			new Endpoint(HttpMethod::GET, "/termine.ics", new Target(Dates::class, "getIcal")),

			// Pictures
			new Endpoint(HttpMethod::GET, "/fotogalerie", new Target(Pictures::class, "getYears")),
			new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]", new Target(Pictures::class, "getAlbums")),
			new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]/[:album]", new Target(Pictures::class, "getAlbum")),

			new Endpoint(HttpMethod::GET, "/users/[i:id]/profile-picture.jpg", new Target(ProfilePicture::class, "get"))
		);
	}
}