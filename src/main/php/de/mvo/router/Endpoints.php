<?php
namespace de\mvo\router;

use ArrayObject;
use de\mvo\service\Account;
use de\mvo\service\Dates;
use de\mvo\service\File;
use de\mvo\service\Forms;
use de\mvo\service\GroupMembers;
use de\mvo\service\InternalHome;
use de\mvo\service\JsonView;
use de\mvo\service\Members;
use de\mvo\service\Messages;
use de\mvo\service\News;
use de\mvo\service\NoteDirectory;
use de\mvo\service\Pictures;
use de\mvo\service\ProfilePicture;
use de\mvo\service\Protocols;
use de\mvo\service\Redirect;
use de\mvo\service\RoomOccupancyPlan;
use de\mvo\service\StaticView;
use de\mvo\service\Uploads;

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
        $this->append(new Endpoint(HttpMethod::GET, "/internal/dates", Target::create()->className(Dates::class)->method("getHtml")->arguments(true)->requireLogin()));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/dates/autocompletion", Target::create()->className(Dates::class)->method("getAutoCompletionList")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/dates/create", Target::create()->className(Dates::class)->method("showCreateEntryForm")->arguments(true)->permission("dates.edit")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/dates/?[:groups]?", Target::create()->className(Dates::class)->method("getHtml")->arguments(true)->requireLogin()));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/dates.ics", Target::create()->className(Dates::class)->method("getIcal")->arguments(true)->requireLogin()));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/dates/edit/[i:id]", Target::create()->className(Dates::class)->method("showEditEntryForm")->arguments(true)->permission("dates.edit")));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/dates", Target::create()->className(Dates::class)->method("saveEntry")->arguments(true)->permission("dates.edit")));
        $this->append(new Endpoint(HttpMethod::DELETE, "/internal/dates/[i:id]", Target::create()->className(Dates::class)->method("deleteEntry")->arguments(true)->permission("dates.edit")));

        // Pictures
        $this->append(new Endpoint(HttpMethod::GET, "/fotogalerie", Target::create()->className(Pictures::class)->method("getYears")));
        $this->append(new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]", Target::create()->className(Pictures::class)->method("getAlbums")));
        $this->append(new Endpoint(HttpMethod::GET, "/fotogalerie/[i:year]/[:album]", Target::create()->className(Pictures::class)->method("getAlbum")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/pictures", Target::create()->className(Pictures::class)->method("getYears")->arguments(true)));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/pictures/[i:year]", Target::create()->className(Pictures::class)->method("getAlbums")->arguments(true)));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/pictures/[i:year]/[:album]", Target::create()->className(Pictures::class)->method("getAlbum")->arguments(true)));

        $this->append(new Endpoint(HttpMethod::GET, "/users/[i:id]/profile-picture.jpg", Target::create()->className(ProfilePicture::class)->method("get")));
        $this->append(new Endpoint(HttpMethod::POST, "/users/[i:id]/profile-picture.jpg", Target::create()->className(ProfilePicture::class)->method("upload")->requireLogin()));

        $this->append(new Endpoint(HttpMethod::GET, "/internal", Target::create()->className(InternalHome::class)->method("get")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/login", Target::create()->className(Account::class)->method("login")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/logout", Target::create()->className(Account::class)->method("logout")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/reset-password", Target::create()->className(Account::class)->method("resetPassword")));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/reset-password", Target::create()->className(Account::class)->method("resetPassword")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/reset-password/confirm", Target::create()->className(Account::class)->method("confirmResetPassword")));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/reset-password/confirm", Target::create()->className(Account::class)->method("confirmResetPassword")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/change-email/confirm", Target::create()->className(Account::class)->method("confirmEmailChange")));

        $settingsPages = array_keys(Account::getSettingsPages());
        $this->append(new Endpoint(HttpMethod::GET, "/internal/settings", Target::create()->className(Redirect::class)->method("redirect")->arguments("/internal/settings/" . $settingsPages[0])->requireLogin()));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/settings/[" . implode("|", $settingsPages) . ":page]", Target::create()->className(Account::class)->method("showSettings")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/settings/[" . implode("|", $settingsPages) . ":page]", Target::create()->className(Account::class)->method("updateSettings")->requireLogin()));

        $this->append(new Endpoint(HttpMethod::POST, "/internal/settings/2fa/request", Target::create()->className(Account::class)->method("request2faKey")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/settings/2fa/enable", Target::create()->className(Account::class)->method("enable2fa")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/settings/2fa/disable", Target::create()->className(Account::class)->method("disable2fa")->requireLogin()));

        $membersListViews = array_keys(Members::getListViews());
        $this->append(new Endpoint(HttpMethod::GET, "/internal/members/[" . implode("|", $membersListViews) . ":view]/?[:groups]?", Target::create()->className(Members::class)->method("getList")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/members/[:username]", Target::create()->className(Members::class)->method("getDetails")->requireLogin()));

        $this->append(new Endpoint(HttpMethod::GET, "/internal/messages/sent", Target::create()->className(Messages::class)->method("getSentMessages")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/messages/received", Target::create()->className(Messages::class)->method("getReceivedMessages")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/messages/send", Target::create()->className(Messages::class)->method("sendMessage")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/messages/[i:id]/hide-for-user", Target::create()->className(Messages::class)->method("hideMessageForUser")->requireLogin()));

        $this->append(new Endpoint(HttpMethod::GET, "/internal/forms", Target::create()->className(Forms::class)->method("getList")->requireLogin()));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/forms/[*:filename]", Target::create()->className(Forms::class)->method("download")->requireLogin()));

        $this->append(new Endpoint(HttpMethod::GET, "/internal/notedirectory", Target::create()->className(NoteDirectory::class)->method("redirectToLatestProgram")->permission("notedirectory.view")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/notedirectory/programs/[i:year]/[*:name]", Target::create()->className(NoteDirectory::class)->method("getProgram")->permission("notedirectory.view")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/notedirectory/titles", Target::create()->className(NoteDirectory::class)->method("getAllTitles")->permission("notedirectory.view")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/notedirectory/titles/[i:id]", Target::create()->className(NoteDirectory::class)->method("getTitleDetails")->permission("notedirectory.view")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/notedirectory/categories/[i:id]", Target::create()->className(NoteDirectory::class)->method("getTitlesWithCategory")->permission("notedirectory.view")));

        $this->append(new Endpoint(HttpMethod::GET, "/internal/protocols", Target::create()->className(Protocols::class)->method("getList")->permission("protocols.view.*")));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/protocols", Target::create()->className(Protocols::class)->method("upload")->permission("protocols.upload.*")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/protocols/upload", Target::create()->className(Protocols::class)->method("showUploadForm")->permission("protocols.upload.*")));

        $this->append(new Endpoint(HttpMethod::GET, "/internal/roomoccupancyplan", Target::create()->className(RoomOccupancyPlan::class)->method("getCalendar")->permission("roomoccupancyplan.view")));
        $this->append(new Endpoint(HttpMethod::GET, "/internal/roomoccupancyplan/entries.json", Target::create()->className(RoomOccupancyPlan::class)->method("getEntries")->permission("roomoccupancyplan.view")));
        $this->append(new Endpoint(HttpMethod::POST, "/internal/roomoccupancyplan/entries/[i:id]", Target::create()->className(RoomOccupancyPlan::class)->method("editEntry")->permission("roomoccupancyplan.edit")));

        $this->append(new Endpoint(HttpMethod::GET, "/internal/uploads/[i:id]/[:key]/[*:filename]", Target::create()->className(Uploads::class)->method("get")->requireLogin()));

        // Required for session keep alive
        $this->append(new Endpoint(HttpMethod::GET, "/nop", Target::create()->className(StaticView::class)->method("getEmpty")));
    }
}