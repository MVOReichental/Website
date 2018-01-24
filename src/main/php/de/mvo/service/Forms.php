<?php
namespace de\mvo\service;

use de\mvo\model\forms\Form;
use de\mvo\model\forms\FormList;
use de\mvo\model\users\User;
use de\mvo\service\exception\NotFoundException;
use de\mvo\service\exception\PermissionViolationException;
use de\mvo\TwigRenderer;
use Twig_Error;

class Forms extends AbstractService
{
    /**
     * @return string
     * @throws Twig_Error
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