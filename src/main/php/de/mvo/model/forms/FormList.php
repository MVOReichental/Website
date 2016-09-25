<?php
namespace de\mvo\model\forms;

use ArrayObject;
use de\mvo\Database;
use de\mvo\model\users\User;

class FormList extends ArrayObject
{
    public static function getFormsAccessibleForUser(User $user)
    {
        $forms = new self;

        $query = Database::query("SELECT * FROM `forms`");

        while ($form = $query->fetchObject(Form::class)) {
            if (!$user->hasPermission("forms.view." . $form->name)) {
                continue;
            }

            $forms->append($form);
        }

        return $forms;
    }
}