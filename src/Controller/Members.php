<?php
namespace App\Controller;

use App\Controller\exception\NotFoundException;
use App\Entity\messages\Message;
use App\Entity\messages\Messages;
use App\Entity\permissions\GroupList;
use App\Entity\users\Groups;
use App\Entity\users\User;
use App\Entity\users\Users;
use App\TwigRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Error\Error;

class Members extends AbstractController
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

    /**
     * @return string
     * @throws Error
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

        foreach (Groups::getAll() as $group => $title) {
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
     * @throws Error
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

        return TwigRenderer::render("members/details", array
        (
            "user" => $user,
            "messages" => $filteredMessages
        ));
    }
}