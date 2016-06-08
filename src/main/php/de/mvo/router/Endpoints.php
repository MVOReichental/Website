<?php
namespace de\mvo\router;

use ArrayObject;
use de\mvo\service\Account;
use de\mvo\service\Dates;
use de\mvo\service\File;
use de\mvo\service\Forms;
use de\mvo\service\InternHome;
use de\mvo\service\JsonView;
use de\mvo\service\GroupMembers;
use de\mvo\service\Members;
use de\mvo\service\Messages;
use de\mvo\service\News;
use de\mvo\service\Pictures;
use de\mvo\service\ProfilePicture;
use de\mvo\service\Redirect;
use de\mvo\service\StaticView;

class Endpoints extends ArrayObject
{
	public function __construct()
	{
		$this->append(new Endpoint(HttpMethod::GET, "/", Target::create()->className(StaticView::class)->method("get")->arguments("home")));
		$this->append(new Endpoint(HttpMethod::GET, "/aktuell", Target::create()->className(News::class)->method("get")));
		$this->append(new Endpoint(HttpMethod::GET, "/impressum", Target::create()->className(StaticView::class)->method("get")->arguments("imprint")));
		$this->append(new Endpoint(HttpMethod::GET, "/beitreten", Target::create()->className(StaticView::class)->method("get")->arguments("verein/beitreten")));
		$this->append(new Endpoint(HttpMethod::GET, "/beitreten/beitrittserklaerung.pdf", Target::create()->className(File::class)->method("get")->arguments(RESOURCES_ROOT . "/beitrittserklaerung.pdf", "application/pdf")));
		$this->append(new Endpoint(HttpMethod::GET, "/bisherige_dirigenten", Target::create()->className(StaticView::class)->method("get")->arguments("verein/bisherige_dirigenten")));
		$this->append(new Endpoint(HttpMethod::GET, "/bisherige_erste_vorsitzende", Target::create()->className(StaticView::class)->method("get")->arguments("verein/bisherige_erste_vorsitzende")));
		$this->append(new Endpoint(HttpMethod::GET, "/chronik", Target::create()->className(StaticView::class)->method("get")->arguments("verein/chronik")));
		$this->append(new Endpoint(HttpMethod::GET, "/vereinsgeschichte", Target::create()->className(StaticView::class)->method("get")->arguments("verein/vereinsgeschichte")));
		$this->append(new Endpoint(HttpMethod::GET, "/vorstand", Target::create()->className(GroupMembers::class)->method("get")->arguments("Vorstand", "vorstandschaft")));
		$this->append(new Endpoint(HttpMethod::GET, "/musiker", Target::create()->className(GroupMembers::class)->method("get")->arguments("Musiker", "musicians")));
		$this->append(new Endpoint(HttpMethod::GET, "/kontakt", Target::create()->className(JsonView::class)->method("get")->arguments("contact", "contact")));

		$this->append(new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildung_im_verein", Target::create()->className(StaticView::class)->method("get")->arguments("jugendausbildung/ausbildung_im_verein")));
		$this->append(new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildungsgruppen", Target::create()->className(JsonView::class)->method("get")->arguments("jugendausbildung/ausbildungsgruppen", "jugendausbildung/ausbildungsgruppen")));
		$this->append(new Endpoint(HttpMethod::GET, "/jugendausbildung/ausbildungsvereinbarung.pdf", Target::create()->className(File::class)->method("get")->arguments(RESOURCES_ROOT . "/ausbildungsvereinbarung.pdf", "application/pdf")));

		$this->append(new Endpoint(HttpMethod::GET, "/foerderverein/warum_foerderverein", Target::create()->className(StaticView::class)->method("get")->arguments("foerderverein/warum_foerderverein")));
		$this->append(new Endpoint(HttpMethod::GET, "/foerderverein/vorstand", Target::create()->className(GroupMembers::class)->method("get")->arguments("Der Vorstand des Fördervereins", "foerderverein")));
		$this->append(new Endpoint(HttpMethod::GET, "/foerderverein/kontakt", Target::create()->className(JsonView::class)->method("get")->arguments("contact", "foerderverein/contact")));

		// Dates
		$this->append(new Endpoint(HttpMethod::GET, "/termine", Target::create()->className(Dates::class)->method("getHtml")));
		$this->append(new Endpoint(HttpMethod::GET, "/termine.ics", Target::create()->className(Dates::class)->method("getIcal")));

		// Pictures
		$this->append(new Endpoint(HttpMethod::GET, "/fotogalerie", Target::create()->className(Pictures::class)->method("getYears")));
		$this->append(new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]", Target::create()->className(Pictures::class)->method("getAlbums")));
		$this->append(new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]/[:album]", Target::create()->className(Pictures::class)->method("getAlbum")));

		$this->append(new Endpoint(HttpMethod::GET, "/users/[i:id]/profile-picture.jpg", Target::create()->className(ProfilePicture::class)->method("get")));
		$this->append(new Endpoint(HttpMethod::POST, "/users/[i:id]/profile-picture.jpg", Target::create()->className(ProfilePicture::class)->method("upload")->requireLogin()));

		$this->append(new Endpoint(HttpMethod::GET, "/intern", Target::create()->className(InternHome::class)->method("get")->requireLogin()));
		$this->append(new Endpoint(HttpMethod::POST, "/intern/login", Target::create()->className(Account::class)->method("login")));
		$this->append(new Endpoint(HttpMethod::GET, "/intern/logout", Target::create()->className(Account::class)->method("logout")->requireLogin()));

		$settingsPages = array_keys(Account::getSettingsPages());
		$this->append(new Endpoint(HttpMethod::GET, "/intern/settings", Target::create()->className(Redirect::class)->method("redirect")->arguments("/intern/settings/" . $settingsPages[0])->requireLogin()));
		$this->append(new Endpoint(HttpMethod::GET, "/intern/settings/[" . implode("|", $settingsPages) . ":page]", Target::create()->className(Account::class)->method("showSettings")->requireLogin()));
		$this->append(new Endpoint(HttpMethod::POST, "/intern/settings/[" . implode("|", $settingsPages) . ":page]", Target::create()->className(Account::class)->method("updateSettings")->requireLogin()));

		$this->append(new Endpoint(HttpMethod::POST, "/intern/settings/2fa/request", Target::create()->className(Account::class)->method("request2faKey")->requireLogin()));
		$this->append(new Endpoint(HttpMethod::POST, "/intern/settings/2fa/enable", Target::create()->className(Account::class)->method("enable2fa")->requireLogin()));
		$this->append(new Endpoint(HttpMethod::POST, "/intern/settings/2fa/disable", Target::create()->className(Account::class)->method("disable2fa")->requireLogin()));

		$membersListViews = array_keys(Members::getListViews());
		$this->append(new Endpoint(HttpMethod::GET, "/intern/members/[" . implode("|", $membersListViews) . ":view]/?[:groups]?", Target::create()->className(Members::class)->method("getList")->requireLogin()));
		$this->append(new Endpoint(HttpMethod::GET, "/intern/members/[:username]", Target::create()->className(Members::class)->method("getDetails")->requireLogin()));

		$this->append(new Endpoint(HttpMethod::GET, "/intern/messages/sent", Target::create()->className(Messages::class)->method("getSentMessages")->requireLogin()));
		$this->append(new Endpoint(HttpMethod::GET, "/intern/messages/received", Target::create()->className(Messages::class)->method("getReceivedMessages")->requireLogin()));
		$this->append(new Endpoint(HttpMethod::POST, "/intern/messages/send", Target::create()->className(Messages::class)->method("sendMessage")->requireLogin()));

		$this->append(new Endpoint(HttpMethod::GET, "/intern/forms", Target::create()->className(Forms::class)->method("getList")->requireLogin()));
		$this->append(new Endpoint(HttpMethod::GET, "/intern/forms/[*:filename]", Target::create()->className(Forms::class)->method("download")->requireLogin()));
	}
}