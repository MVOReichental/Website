<?php
namespace de\mvo\service;

use de\mvo\model\forms\Form;
use de\mvo\model\forms\FormList;
use de\mvo\model\users\User;
use de\mvo\MustacheRenderer;
use de\mvo\service\exception\NotFoundException;
use de\mvo\service\exception\PermissionViolationException;

class Forms extends AbstractService
{
	public function getList()
	{
		return MustacheRenderer::render("forms", array
		(
			"forms" => FormList::getFormsAccessibleForUser(User::getCurrent())
		));
	}

	public function download()
	{
		$form = Form::getByFilename($this->params->filename);
		if ($form === null)
		{
			throw new NotFoundException;
		}

		if (!User::getCurrent()->hasPermission("forms." . $form->name))
		{
			throw new PermissionViolationException;
		}

		if (!$form->stream())
		{
			throw new NotFoundException;
		}

		return null;
	}
}