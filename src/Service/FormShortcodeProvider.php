<?php

namespace OHMedia\FormBundle\Service;

use OHMedia\FormBundle\Repository\FormRepository;
use OHMedia\WysiwygBundle\Shortcodes\AbstractShortcodeProvider;
use OHMedia\WysiwygBundle\Shortcodes\Shortcode;

class FormShortcodeProvider extends AbstractShortcodeProvider
{
    public function __construct(private FormRepository $formRepository)
    {
    }

    public function getTitle(): string
    {
        return 'Forms';
    }

    public function buildShortcodes(): void
    {
        $forms = $this->formRepository->createQueryBuilder('f')
            ->orderBy('f.name', 'asc')
            ->getQuery()
            ->getResult();

        foreach ($forms as $form) {
            $id = $form->getId();

            $this->addShortcode(new Shortcode(
                sprintf('%s (ID:%s)', $form, $id),
                'form_builder('.$id.')'
            ));
        }
    }
}
