<?php
namespace App\Entity\forms;

use ArrayObject;
use App\Database;
use App\Entity\users\User;

class FormList extends ArrayObject
{
    public static function getFormsAccessibleForUser(User $user)
    {
        $forms = new self;

        $query = Database::query("SELECT * FROM `forms` ORDER BY `name`");

        while ($form = $query->fetchObject(Form::class)) {
            if (!$user->hasPermission("forms.view." . $form->name)) {
                continue;
            }

            $forms->append($form);
        }

        return $forms;
    }
}