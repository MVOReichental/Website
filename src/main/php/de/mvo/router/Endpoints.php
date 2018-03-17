<?php
namespace de\mvo\router;

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
use de\mvo\service\NoteDirectoryEditor;
use de\mvo\service\Pictures;
use de\mvo\service\ProfilePicture;
use de\mvo\service\Protocols;
use de\mvo\service\Redirect;
use de\mvo\service\RoomOccupancyPlan;
use de\mvo\service\StaticView;
use de\mvo\service\Uploads;
use de\mvo\service\UserManagement;
use de\mvo\service\Videos;
use de\mvo\service\Visits;

class Endpoints
{
    private static function mapPublicEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/")
            ->target()
            ->className(StaticView::class)
            ->method("get")
            ->arguments("home");

        Endpoint::create(HttpMethod::GET, "/aktuell")
            ->target()
            ->className(News::class)
            ->method("get");

        Endpoint::create(HttpMethod::GET, "/impressum")
            ->target()
            ->className(StaticView::class)
            ->method("get")
            ->arguments("imprint");

        Endpoint::create(HttpMethod::GET, "/beitreten")
            ->target()
            ->className(StaticView::class)
            ->method("get")
            ->arguments("verein/beitreten");

        Endpoint::create(HttpMethod::GET, "/beitreten/beitrittserklaerung.pdf")
            ->target()
            ->className(File::class)
            ->method("get")
            ->arguments(RESOURCES_ROOT . "/beitrittserklaerung.pdf", "application/pdf");

        Endpoint::create(HttpMethod::GET, "/bisherige_dirigenten")
            ->target()
            ->className(StaticView::class)
            ->method("get")
            ->arguments("verein/bisherige_dirigenten");

        Endpoint::create(HttpMethod::GET, "/bisherige_erste_vorsitzende")
            ->target()
            ->className(StaticView::class)
            ->method("get")
            ->arguments("verein/bisherige_erste_vorsitzende");

        Endpoint::create(HttpMethod::GET, "/chronik")
            ->target()
            ->className(StaticView::class)
            ->method("get")
            ->arguments("verein/chronik");

        Endpoint::create(HttpMethod::GET, "/vereinsgeschichte")
            ->target()
            ->className(StaticView::class)
            ->method("get")
            ->arguments("verein/vereinsgeschichte");

        Endpoint::create(HttpMethod::GET, "/vorstand")
            ->target()
            ->className(GroupMembers::class)
            ->method("get")
            ->arguments("Vorstand", "vorstandschaft");

        Endpoint::create(HttpMethod::GET, "/musiker")
            ->target()
            ->className(GroupMembers::class)
            ->method("get")
            ->arguments("Musiker", "musicians");

        Endpoint::create(HttpMethod::GET, "/kontakt")
            ->target()
            ->className(JsonView::class)
            ->method("get")
            ->arguments("contact", "contact");

        Endpoint::create(HttpMethod::GET, "/jugendausbildung/ausbildung_im_verein")
            ->target()
            ->className(StaticView::class)
            ->method("get")
            ->arguments("jugendausbildung/ausbildung_im_verein");

        Endpoint::create(HttpMethod::GET, "/jugendausbildung/ausbildungsgruppen")
            ->target()
            ->className(JsonView::class)
            ->method("get")
            ->arguments("jugendausbildung/ausbildungsgruppen", "jugendausbildung/ausbildungsgruppen");

        Endpoint::create(HttpMethod::GET, "/foerderverein/warum_foerderverein")
            ->target()
            ->className(StaticView::class)
            ->method("get")
            ->arguments("foerderverein/warum_foerderverein");

        Endpoint::create(HttpMethod::GET, "/foerderverein/vorstand")
            ->target()
            ->className(GroupMembers::class)
            ->method("get")
            ->arguments("Der Vorstand des F&ouml;rdervereins", "foerderverein");

        Endpoint::create(HttpMethod::GET, "/foerderverein/kontakt")
            ->target()
            ->className(JsonView::class)
            ->method("get")
            ->arguments("contact", "foerderverein/contact");

        // Dates
        Endpoint::create(HttpMethod::GET, "/termine")
            ->target()
            ->className(Dates::class)
            ->method("getHtml");

        Endpoint::create(HttpMethod::GET, "/termine.ics")
            ->target()
            ->className(Dates::class)
            ->method("getIcal");

        Endpoint::create(HttpMethod::GET, "/fotogalerie")
            ->target()
            ->className(Pictures::class)
            ->method("getYears");

        // Pictures
        Endpoint::create(HttpMethod::GET, "/fotogalerie/[i:year]")
            ->target()
            ->className(Pictures::class)
            ->method("getAlbums");

        Endpoint::create(HttpMethod::GET, "/fotogalerie/[i:year]/[:album]")
            ->target()
            ->className(Pictures::class)
            ->method("getAlbum");

        // Profile pictures
        Endpoint::create(HttpMethod::GET, "/users/[i:id]/profile-picture.jpg")
            ->target()
            ->className(ProfilePicture::class)
            ->method("get");
    }

    private static function mapDatesEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/internal/dates")
            ->target()
            ->className(Dates::class)
            ->method("getHtml")
            ->arguments(true)
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/dates/autocompletion")
            ->target()
            ->className(Dates::class)
            ->method("getAutoCompletionList")
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/dates/create")
            ->target()
            ->className(Dates::class)
            ->method("showCreateEntryForm")
            ->arguments(true)
            ->permission("dates.edit");

        Endpoint::create(HttpMethod::GET, "/internal/dates.ics")
            ->target()
            ->className(Dates::class)
            ->method("getIcal")
            ->arguments(true)
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/dates/edit/[i:id]")
            ->target()
            ->className(Dates::class)
            ->method("showEditEntryForm")
            ->arguments(true)
            ->permission("dates.edit");

        Endpoint::create(HttpMethod::POST, "/internal/dates")
            ->target()
            ->className(Dates::class)
            ->method("saveEntry")
            ->arguments(true)
            ->permission("dates.edit");

        Endpoint::create(HttpMethod::DELETE, "/internal/dates/[i:id]")
            ->target()
            ->className(Dates::class)
            ->method("deleteEntry")
            ->arguments(true)
            ->permission("dates.edit");
    }

    private static function mapPicturesEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/internal/pictures")
            ->target()
            ->className(Pictures::class)
            ->method("getYears")
            ->arguments(true)
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/pictures/[i:year]")
            ->target()
            ->className(Pictures::class)
            ->method("getAlbums")
            ->arguments(true)
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/pictures/[i:year]/[:album]")
            ->target()
            ->className(Pictures::class)
            ->method("getAlbum")
            ->arguments(true)
            ->requireLogin();
    }

    private static function mapAccountEndpoints()
    {
        Endpoint::create(HttpMethod::POST, "/internal/login")
            ->target()
            ->className(Account::class)
            ->method("login");

        Endpoint::create(HttpMethod::GET, "/internal/logout")
            ->target()
            ->className(Account::class)
            ->method("logout")
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/reset-password")
            ->target()
            ->className(Account::class)
            ->method("resetPassword");

        Endpoint::create(HttpMethod::POST, "/internal/reset-password")
            ->target()
            ->className(Account::class)
            ->method("resetPassword");

        Endpoint::create(HttpMethod::GET, "/internal/reset-password/confirm")
            ->target()
            ->className(Account::class)
            ->method("confirmResetPassword");

        Endpoint::create(HttpMethod::POST, "/internal/reset-password/confirm")
            ->target()
            ->className(Account::class)
            ->method("confirmResetPassword");

        Endpoint::create(HttpMethod::GET, "/internal/change-email/confirm")
            ->target()
            ->className(Account::class)
            ->method("confirmEmailChange");

        $settingsPages = array_keys(Account::getSettingsPages());

        Endpoint::create(HttpMethod::GET, "/internal/settings")
            ->target()
            ->className(Redirect::class)
            ->method("redirect")
            ->arguments("/internal/settings/" . $settingsPages[0])
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/settings/[" . implode("|", $settingsPages) . ":page]")
            ->target()
            ->className(Account::class)
            ->method("showSettings")
            ->requireLogin();

        Endpoint::create(HttpMethod::POST, "/internal/settings/[" . implode("|", $settingsPages) . ":page]")
            ->target()
            ->className(Account::class)
            ->method("updateSettings")
            ->requireLogin();

        Endpoint::create(HttpMethod::POST, "/internal/settings/2fa/request")
            ->target()
            ->className(Account::class)
            ->method("request2faKey")
            ->requireLogin();

        Endpoint::create(HttpMethod::POST, "/internal/settings/2fa/enable")
            ->target()
            ->className(Account::class)
            ->method("enable2fa")
            ->requireLogin();

        Endpoint::create(HttpMethod::POST, "/internal/settings/2fa/disable")
            ->target()
            ->className(Account::class)
            ->method("disable2fa")
            ->requireLogin();

        Endpoint::create(HttpMethod::POST, "/users/[i:id]/profile-picture.jpg")
            ->target()
            ->className(ProfilePicture::class)
            ->method("upload")
            ->requireLogin();
    }

    private static function mapMembersEndpoints()
    {
        $membersListViews = array_keys(Members::getListViews());

        Endpoint::create(HttpMethod::GET, "/internal/members/[" . implode("|", $membersListViews) . ":view]")
            ->target()
            ->className(Members::class)
            ->method("getList")
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/members/[:username]")
            ->target()
            ->className(Members::class)
            ->method("getDetails")
            ->requireLogin();
    }

    private static function mapMessagesEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/internal/messages/sent")
            ->target()
            ->className(Messages::class)
            ->method("getSentMessages")
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/messages/received")
            ->target()
            ->className(Messages::class)
            ->method("getReceivedMessages")
            ->requireLogin();

        Endpoint::create(HttpMethod::POST, "/internal/messages/send")
            ->target()
            ->className(Messages::class)
            ->method("sendMessage")
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/messages/[i:id]")
            ->target()
            ->className(Messages::class)
            ->method("showMessage")
            ->requireLogin();

        Endpoint::create(HttpMethod::POST, "/internal/messages/[i:id]/hide-for-user")
            ->target()
            ->className(Messages::class)
            ->method("hideMessageForUser")
            ->requireLogin();
    }

    private static function mapFormsEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/internal/forms")
            ->target()
            ->className(Forms::class)
            ->method("getList")
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/forms/[*:filename]")
            ->target()
            ->className(Forms::class)
            ->method("download")
            ->requireLogin();
    }

    private static function mapNoteDirectoryEndpoints()
    {
        $baseUrl = "/internal/notedirectory";
        $permission = "notedirectory.view";

        Endpoint::create(HttpMethod::GET, $baseUrl)
            ->target()
            ->className(NoteDirectory::class)
            ->method("redirectToLatestDefaultProgram")
            ->permission($permission);

        Endpoint::create(HttpMethod::GET, $baseUrl . "/programs/[i:year]/[*:name]")
            ->target()
            ->className(NoteDirectory::class)
            ->method("getProgram")
            ->permission($permission);

        Endpoint::create(HttpMethod::GET, $baseUrl . "/titles")
            ->target()
            ->className(NoteDirectory::class)
            ->method("getAllTitles")
            ->permission($permission);

        Endpoint::create(HttpMethod::GET, $baseUrl . "/titles/[i:id]")
            ->target()
            ->className(NoteDirectory::class)
            ->method("getTitleDetails")
            ->permission($permission);
    }

    private static function mapNoteDirectoryEditorEndpoints()
    {
        $baseUrl = "/internal/notedirectory/editor";
        $permission = "notedirectory.edit";

        Endpoint::create(HttpMethod::GET, $baseUrl)
            ->target()
            ->className(Redirect::class)
            ->method("redirect")
            ->arguments("/internal/notedirectory/editor/programs")
            ->permission($permission);

        # Programs

        Endpoint::create(HttpMethod::GET, $baseUrl . "/programs")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("getProgramsPage")
            ->permission($permission);

        Endpoint::create(HttpMethod::GET, $baseUrl . "/programs/[i:id]")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("getProgramEditPage")
            ->permission($permission);

        Endpoint::create(HttpMethod::GET, $baseUrl . "/programs/[i:id]/copy")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("getProgramEditPage")
            ->arguments(true)
            ->permission($permission);

        Endpoint::create(HttpMethod::GET, $baseUrl . "/programs/new")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("getCreateProgramPage")
            ->permission($permission);

        Endpoint::create(HttpMethod::POST, $baseUrl . "/programs/[i:id]")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("editProgram")
            ->permission($permission);

        Endpoint::create(HttpMethod::POST, $baseUrl . "/programs/new")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("createProgram")
            ->permission($permission);

        Endpoint::create(HttpMethod::DELETE, $baseUrl . "/programs/[i:id]")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("deleteProgram")
            ->permission($permission);

        # Titles

        Endpoint::create(HttpMethod::GET, $baseUrl . "/titles")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("getTitlesPage")
            ->permission($permission);

        Endpoint::create(HttpMethod::GET, $baseUrl . "/titles/[i:id]")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("getTitleEditPage")
            ->permission($permission);

        Endpoint::create(HttpMethod::GET, $baseUrl . "/titles/new")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("getCreateTitlePage")
            ->permission($permission);

        Endpoint::create(HttpMethod::POST, $baseUrl . "/titles/[i:id]")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("editTitle")
            ->permission($permission);

        Endpoint::create(HttpMethod::POST, $baseUrl . "/titles/new")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("createTitle")
            ->permission($permission);

        Endpoint::create(HttpMethod::DELETE, $baseUrl . "/titles/[i:id]")
            ->target()
            ->className(NoteDirectoryEditor::class)
            ->method("deleteTitle")
            ->permission($permission);
    }

    private static function mapProtocolsEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/internal/protocols")
            ->target()
            ->className(Protocols::class)
            ->method("getList")
            ->permission("protocols.view.*");

        Endpoint::create(HttpMethod::POST, "/internal/protocols")
            ->target()
            ->className(Protocols::class)
            ->method("upload")
            ->permission("protocols.upload.*");

        Endpoint::create(HttpMethod::GET, "/internal/protocols/upload")
            ->target()
            ->className(Protocols::class)
            ->method("showUploadForm")
            ->permission("protocols.upload.*");
    }

    private static function mapRoomOccupancyPlanEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/internal/roomoccupancyplan")
            ->target()
            ->className(RoomOccupancyPlan::class)
            ->method("getCalendar")
            ->permission("roomoccupancyplan.view");

        Endpoint::create(HttpMethod::GET, "/internal/roomoccupancyplan/entries.json")
            ->target()
            ->className(RoomOccupancyPlan::class)
            ->method("getEntries")
            ->permission("roomoccupancyplan.view");

        Endpoint::create(HttpMethod::POST, "/internal/roomoccupancyplan/entries/[i:id]/move-resize")
            ->target()
            ->className(RoomOccupancyPlan::class)
            ->method("moveResizeEntry")
            ->permission("roomoccupancyplan.edit");

        Endpoint::create(HttpMethod::POST, "/internal/roomoccupancyplan/entries/[i:id]")
            ->target()
            ->className(RoomOccupancyPlan::class)
            ->method("editEntry")
            ->permission("roomoccupancyplan.edit");

        Endpoint::create(HttpMethod::POST, "/internal/roomoccupancyplan/entries")
            ->target()
            ->className(RoomOccupancyPlan::class)
            ->method("createEntry")
            ->permission("roomoccupancyplan.edit");
    }

    private static function mapUploadEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/uploads/[i:id]/[:key]/[*:filename]")
            ->target()
            ->className(Uploads::class)
            ->method("get");

        Endpoint::create(HttpMethod::POST, "/internal/upload")
            ->target()
            ->className(Uploads::class)
            ->method("upload")
            ->requireLogin();
    }

    private static function mapVideosEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/internal/videos")
            ->target()
            ->className(Videos::class)
            ->method("getList")
            ->requireLogin();
    }

    private static function mapAdminEndpoints()
    {
        Endpoint::create(HttpMethod::GET, "/internal/admin/visits")
            ->target()
            ->className(Visits::class)
            ->method("getPage")
            ->permission("admin.visits");

        Endpoint::create(HttpMethod::GET, "/internal/admin/visits/chart.json")
            ->target()
            ->className(Visits::class)
            ->method("getChartData")
            ->permission("admin.visits");

        Endpoint::create(HttpMethod::GET, "/internal/admin/usermanagement")
            ->target()
            ->className(UserManagement::class)
            ->method("getPage")
            ->permission("admin.userManagement");

        Endpoint::create(HttpMethod::GET, "/internal/admin/usermanagement/permission-groups")
            ->target()
            ->className(UserManagement::class)
            ->method("getPermissionGroupsTree")
            ->permission("admin.userManagement");

        Endpoint::create(HttpMethod::GET, "/internal/admin/usermanagement/user")
            ->target()
            ->className(UserManagement::class)
            ->method("getEditPage")
            ->permission("admin.userManagement");

        Endpoint::create(HttpMethod::GET, "/internal/admin/usermanagement/user/[i:id]")
            ->target()
            ->className(UserManagement::class)
            ->method("getEditPage")
            ->permission("admin.userManagement");

        Endpoint::create(HttpMethod::GET, "/internal/admin/usermanagement/user/[i:id]/profile-picture")
            ->target()
            ->className(UserManagement::class)
            ->method("getProfilePicturePage")
            ->permission("admin.userManagement");

        Endpoint::create(HttpMethod::POST, "/internal/admin/usermanagement/user")
            ->target()
            ->className(UserManagement::class)
            ->method("createUser")
            ->permission("admin.userManagement");

        Endpoint::create(HttpMethod::POST, "/internal/admin/usermanagement/user/[i:id]")
            ->target()
            ->className(UserManagement::class)
            ->method("updateUser")
            ->permission("admin.userManagement");

        Endpoint::create(HttpMethod::GET, "/internal/admin/newseditor")
            ->target()
            ->className(News::class)
            ->method("get")
            ->arguments(true)
            ->permission("admin.newsEditor");

        Endpoint::create(HttpMethod::POST, "/internal/admin/newseditor")
            ->target()
            ->className(News::class)
            ->method("save")
            ->permission("admin.newsEditor");
    }

    private static function mapInternalEndpoints()
    {
        self::mapDatesEndpoints();
        self::mapPicturesEndpoints();
        self::mapAccountEndpoints();
        self::mapMembersEndpoints();
        self::mapMessagesEndpoints();
        self::mapFormsEndpoints();
        self::mapNoteDirectoryEndpoints();
        self::mapNoteDirectoryEditorEndpoints();
        self::mapProtocolsEndpoints();
        self::mapRoomOccupancyPlanEndpoints();
        self::mapUploadEndpoints();
        self::mapVideosEndpoints();
        self::mapAdminEndpoints();

        Endpoint::create(HttpMethod::GET, "/internal")
            ->target()
            ->className(InternalHome::class)
            ->method("get")
            ->requireLogin();
    }

    public static function map()
    {
        self::mapPublicEndpoints();
        self::mapInternalEndpoints();

        // Required for session keep alive
        Endpoint::create(HttpMethod::GET, "/nop")
            ->target()
            ->className(StaticView::class)
            ->method("getEmpty");
    }

    /**
     * @var Endpoint[]
     */
    private static $endpoints = array();

    public static function add(Endpoint $endpoint)
    {
        self::$endpoints[] = $endpoint;
    }

    /**
     * @return Endpoint[]
     */
    public static function get()
    {
        return self::$endpoints;
    }
}