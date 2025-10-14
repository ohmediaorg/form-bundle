<?php

namespace OHMedia\FormBundle\Security\Voter;

use OHMedia\FormBundle\Entity\FormField;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class FormFieldVoter extends AbstractEntityVoter
{
    public const CREATE = 'create';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::CREATE,
            self::EDIT,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return FormField::class;
    }

    protected function canCreate(FormField $formField, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(FormField $formField, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(FormField $formField, User $loggedIn): bool
    {
        return true;
    }
}
