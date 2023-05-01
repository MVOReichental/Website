<?php
namespace App\router;

use App\Controller\Account;
use App\Controller\Forms;
use App\Controller\Members;
use App\Controller\Messages;
use App\Controller\News;
use App\Controller\NoteDirectory;
use App\Controller\NoteDirectoryEditor;
use App\Controller\Protocols;
use App\Controller\RoomOccupancyPlan;
use App\Controller\Uploads;
use App\Controller\UserManagement;
use App\Controller\Videos;
use App\Controller\Visits;

class Endpoints
{
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
    }

    private static function mapMembersEndpoints()
    {
        $membersListViews = array_keys(Members::getListViews());

        Endpoint::create(HttpMethod::GET, "/internal/members/[" . implode("|", $membersListViews) . ":view]")
            ->target()
            ->className(Members::class)
            ->method("getList")
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/members/[*:username]")
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

        Endpoint::create(HttpMethod::GET, "/internal/messages/all")
            ->target()
            ->className(Messages::class)
            ->method("getAllMessages")
            ->permission("messages.all");

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

        Endpoint::create(HttpMethod::GET, $baseUrl . "/search")
            ->target()
            ->className(NoteDirectory::class)
            ->method("search")
            ->permission($permission);
    }

    private static function mapNoteDirectoryEditorEndpoints()
    {
        $baseUrl = "/internal/notedirectory/editor";
        $permission = "notedirectory.edit";

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

        Endpoint::create(HttpMethod::POST, $baseUrl . "/programs/[i:id]/copy")
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

        Endpoint::create(HttpMethod::POST, "/internal/admin/newseditor/content.html")
            ->target()
            ->className(News::class)
            ->method("save")
            ->arguments(News::NEWS_FILE)
            ->permission("admin.newsEditor");

        Endpoint::create(HttpMethod::DELETE, "/internal/admin/newseditor/content.html")
            ->target()
            ->className(News::class)
            ->method("delete")
            ->arguments(News::NEWS_FILE)
            ->permission("admin.newsEditor");

        Endpoint::create(HttpMethod::GET, "/internal/switch-user")
            ->target()
            ->className(Account::class)
            ->method("switchUserToOrigin")
            ->requireLogin();

        Endpoint::create(HttpMethod::GET, "/internal/switch-user/[i:id]")
            ->target()
            ->className(Account::class)
            ->method("switchUser")
            ->permission("admin.switchUser");
    }

    private static function map()
    {
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