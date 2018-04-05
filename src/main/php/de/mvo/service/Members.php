<?php
namespace de\mvo\service;

use de\mvo\model\messages\Message;
use de\mvo\model\messages\Messages;
use de\mvo\model\permissions\GroupList;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;
use Twig_Error;

class Members extends AbstractService
{
    public static function getListViews()
    {
        return array
        (
            "addresslist" => array
            (
                "title" => "Adressenliste"
            ),
            "birthdays" => array
            (
                "title" => "Geburtstage"
            )
        );
    }

    public static function getListGroups()
    {
        return array
        (
            "vorstand" => "Vorstand",
            "sonderaufgaben" => "Sonderaufgaben",
            "dirigentin" => "Dirigentin",
            "musiker" => "Musiker"
        );
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function getList()
    {
        $users = new Users;

        if (isset($_GET["groups"])) {
            $selectedGroups = array_filter(explode(" ", $_GET["groups"]));
        } else {
            $selectedGroups = array();
        }

        $groups = array();

        foreach (self::getListGroups() as $group => $title) {
            $active = (empty($selectedGroups) or in_array($group, $selectedGroups));

            $groups[$group] = array
            (
                "title" => $title,
                "active" => $active
            );

            if ($active) {
                $permissionGroup = GroupList::load()->getGroupByPermission("group." . $group);
                if ($permissionGroup !== null) {
                    $users->addAll($permissionGroup->getAllUsers());
                }
            }
        }

        $users->makeUnique();

        if (empty($selectedGroups)) {
            $users = Users::getAll();
        }

        $users->sortByLastNameAndFirstName();

        return TwigRenderer::render("members/list/page", array
        (
            "title" => self::getListViews()[$this->params->view]["title"],
            "groups" => $groups,
            "view" => $this->params->view,
            "users" => $users->enabledUsers()
        ));
    }

    /**
     * @return string
     * @throws NotFoundException
     * @throws Twig_Error
     */
    public function getDetails()
    {
        $currentUser = User::getCurrent();
        $user = User::getByUsername($this->params->username);

        if ($user === null) {
            throw new NotFoundException;
        }

        $filteredMessages = new Messages;

        $messages = Messages::getBySender($user);

        /**
         * @var $message Message
         */
        foreach ($messages as $message) {
            if ($message->recipients->hasUser($currentUser)) {
                $filteredMessages->append($message);
            }
        }

        $messages = Messages::getByRecipient($user);

        /**
         * @var $message Message
         */
        foreach ($messages as $message) {
            // Prevent duplicate messages if sender is also in recipients list
            if ($messages->hasMessage($message)) {
                continue;
            }

            if ($message->sender->isEqualTo($currentUser)) {
                $filteredMessages->append($message);
            }
        }

        $filteredMessages->makeUnique();

        return TwigRenderer::render("members/details", array
        (
            "user" => $user,
            "messages" => $filteredMessages
        ));
    }
}