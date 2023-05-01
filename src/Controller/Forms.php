<?php
namespace App\Controller;

use App\Controller\exception\NotFoundException;
use App\Controller\exception\PermissionViolationException;
use App\Entity\forms\Form;
use App\Entity\forms\FormList;
use App\Entity\users\User;
use App\TwigRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Error\Error;

class Forms extends AbstractController
{
    /**
     * @return string
     * @throws Error
     */
    public function getList()
    {
        return TwigRenderer::render("forms", array
        (
            "forms" => FormList::getFormsAccessibleForUser(User::getCurrent())
        ));
    }

    /**
     * @return null
     * @throws NotFoundException
     * @throws PermissionViolationException
     */
    public function download()
    {
        $form = Form::getByFilename($this->params->filename);
        if ($form === null) {
            throw new NotFoundException;
        }

        if (!User::getCurrent()->hasPermission("forms.view." . $form->name)) {
            throw new PermissionViolationException;
        }

        if (!$form->stream()) {
            throw new NotFoundException;
        }

        return null;
    }
}