<?php

namespace OHMedia\FormBundle\Service\EntityChoice;

use OHMedia\FormBundle\Entity\Form;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;

class FormEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Forms';
    }

    public function getEntities(): array
    {
        return [Form::class];
    }
}
