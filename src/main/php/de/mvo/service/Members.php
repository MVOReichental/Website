<?php
namespace de\mvo\service;

use de\mvo\model\messages\Message;
use de\mvo\model\messages\Messages;
use de\mvo\model\permissions\GroupList;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;

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
            "vorstand" => array
            (
                "title" => "Vorstand"
            ),
            "sonderaufgaben" => array
            (
                "title" => "Sonderaufgaben"
            ),
            "dirigentin" => array
            (
                "title" => "Dirigentin"
            ),
            "musiker" => array
            (
                "title" => "Musiker"
            )
        );
    }

    public function getList()
    {
        $baseUrl = "internal/members";

        $users = new Users;

        if (isset($this->params->groups)) {
            $selectedGroups = array_filter(explode("+", $this->params->groups));
        } else {
            $selectedGroups = array();
        }

        $groups = self::getListGroups();

        foreach ($groups as $name => &$group) {
            $newSelectedGroups = $selectedGroups;

            $enabledGroupIndex = array_search($name, $newSelectedGroups);
            if ($enabledGroupIndex === false) {
                $newSelectedGroups[] = $name;
                $group["active"] = false;
            } else {
                unset($newSelectedGroups[$enabledGroupIndex]);
                $group["active"] = true;

                $permissionGroup = GroupList::load()->getGroupByPermission("group." . $name);
                if ($permissionGroup !== null) {
                    $users->addAll($permissionGroup->getAllUsers());
                }
            }

            sort($newSelectedGroups);

            $group["url"] = $baseUrl . "/" . $this->params->view . "/" . implode("+", array_unique($newSelectedGroups));
        }

        $users->makeUnique();

        if (empty($selectedGroups)) {
            $users = Users::getAll();
        }

        return TwigRenderer::render("members/list/page", array
        (
            "title" => self::getListViews()[$this->params->view]["title"],
            "groups" => array_values($groups),
            "view" => $this->params->view,
            "users" => $users
        ));
    }

    public function getDetails()
    {
        $currentUser = User::getCurrent();
        $user = User::getByUsername($this->params->username);

        if ($user === null) {
            throw new NotFoundException;
        }

        $messages = Messages::getBySender($user);

        $filteredMessages = new Messages;

        /**
         * @var $message Message
         */
        foreach ($messages as $message) {
            if ($message->sender->isEqualTo($currentUser) or $message->recipients->hasUser($currentUser) or $currentUser->hasPermission("messages.readAll")) {
                $filteredMessages->append($message);
            }
        }

        return TwigRenderer::render("members/details", array
        (
            "user" => $user,
            "messages" => $filteredMessages
        ));
    }
}