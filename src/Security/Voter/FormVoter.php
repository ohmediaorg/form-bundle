<?php

namespace OHMedia\FormBundle\Security\Voter;

use OHMedia\FormBundle\Entity\Form;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;
use OHMedia\WysiwygBundle\Service\Wysiwyg;

class FormVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    public function __construct(private Wysiwyg $wysiwyg)
    {
    }

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return Form::class;
    }

    protected function canIndex(Form $form, User $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(Form $form, User $loggedIn): bool
    {
        return true;
    }

    protected function canView(Form $form, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(Form $form, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(Form $form, User $loggedIn): bool
    {
        $shortcode = sprintf('form_builder(%d)', $form->getId());

        return !$this->wysiwyg->shortcodesInUse($shortcode);
    }
}
