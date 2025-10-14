<?php

namespace OHMedia\FormBundle\Service;

use OHMedia\FormBundle\Entity\Form;
use OHMedia\FormBundle\Entity\FormField;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;

class FormEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Forms';
    }

    public function getEntities(): array
    {
        return [
            Form::class,
            FormField::class,
        ];
    }
}
