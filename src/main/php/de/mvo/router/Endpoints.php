<?php
namespace de\mvo\router;

use de\mvo\service\Account;
use de\mvo\service\Dates;
use de\mvo\service\File;
use de\mvo\service\InternHome;
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
			new Endpoint(HttpMethod::GET, "/", Target::create()->className(StaticView::class)->method("get")->arguments("home")),
			new Endpoint(HttpMethod::GET, "/aktuell", Target::create()->className(News::class)->method("get")),
			new Endpoint(HttpMethod::GET, "/impressum", Target::create()->className(StaticView::class)->method("get")->arguments("imprint")),
			new Endpoint(HttpMethod::GET, "/beitreten", Target::create()->className(StaticView::class)->method("get")->arguments("verein/beitreten")),
			new Endpoint(HttpMethod::GET, "/beitreten/beitrittserklaerung.pdf", Target::create()->className(File::class)->method("get")->arguments(RESOURCES_ROOT . "/beitrittserklaerung.pdf", "application/pdf")),
			new Endpoint(HttpMethod::GET, "/bisherige_dirigenten", Target::create()->className(StaticView::class)->method("get")->arguments("verein/bisherige_dirigenten")),
			new Endpoint(HttpMethod::GET, "/bisherige_erste_vorsitzende", Target::create()->className(StaticView::class)->method("get")->arguments("verein/bisherige_erste_vorsitzende")),
			new Endpoint(HttpMethod::GET, "/chronik", Target::create()->className(StaticView::class)->method("get")->arguments("verein/chronik")),
			new Endpoint(HttpMethod::GET, "/vereinsgeschichte", Target::create()->className(StaticView::class)->method("get")->arguments("verein/vereinsgeschichte")),
			new Endpoint(HttpMethod::GET, "/vorstand", Target::create()->className(GroupMembers::class)->method("get")->arguments("Vorstand", "vorstandschaft")),
			new Endpoint(HttpMethod::GET, "/musiker", Target::create()->className(GroupMembers::class)->method("get")->arguments("Musiker", "musicians")),
			new Endpoint(HttpMethod::GET, "/kontakt", Target::create()->className(JsonView::class)->method("get")->arguments("contact", "contact")),

			new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildung_im_verein", Target::create()->className(StaticView::class)->method("get")->arguments("jugendausbildung/ausbildung_im_verein")),
			new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildungsgruppen", Target::create()->className(JsonView::class)->method("get")->arguments("jugendausbildung/ausbildungsgruppen", "jugendausbildung/ausbildungsgruppen")),
			new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildungsvereinbarung.pdf", Target::create()->className(File::class)->method("get")->arguments(RESOURCES_ROOT . "/ausbildungsvereinbarung.pdf", "application/pdf")),

			new Endpoint(HttpMethod::GET, "/foerderverein/warum_foerderverein", Target::create()->className(StaticView::class)->method("get")->arguments("foerderverein/warum_foerderverein")),
			new Endpoint(HttpMethod::GET, "/foerderverein/vorstand", Target::create()->className(GroupMembers::class)->method("get")->arguments("Der Vorstand des Fördervereins", "foerderverein")),
			new Endpoint(HttpMethod::GET, "/foerderverein/kontakt", Target::create()->className(JsonView::class)->method("get")->arguments("contact", "foerderverein/contact")),

			// Dates
			new Endpoint(HttpMethod::GET, "/termine", Target::create()->className(Dates::class)->method("getHtml")),
			new Endpoint(HttpMethod::GET, "/termine.ics", Target::create()->className(Dates::class)->method("getIcal")),

			// Pictures
			new Endpoint(HttpMethod::GET, "/fotogalerie", Target::create()->className(Pictures::class)->method("getYears")),
			new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]", Target::create()->className(Pictures::class)->method("getAlbums")),
			new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]/[:album]", Target::create()->className(Pictures::class)->method("getAlbum")),

			new Endpoint(HttpMethod::GET, "/users/[i:id]/profile-picture.jpg", Target::create()->className(ProfilePicture::class)->method("get")),

			new Endpoint(HttpMethod::GET, "/intern", Target::create()->className(InternHome::class)->method("get")->requireLogin()),
			new Endpoint(HttpMethod::POST, "/intern/login", Target::create()->className(Account::class)->method("login")),
			new Endpoint(HttpMethod::GET, "/intern/logout", Target::create()->className(Account::class)->method("logout")->requireLogin()),
			new Endpoint(HttpMethod::GET, "/intern/profil/einstellungen", Target::create()->className(Account::class)->method("showSettings")->requireLogin()),
			new Endpoint(HttpMethod::POST, "/intern/profil/einstellungen", Target::create()->className(Account::class)->method("updateSettings")->requireLogin())
		);
	}
}