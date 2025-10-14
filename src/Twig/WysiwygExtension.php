<?php

namespace OHMedia\FormBundle\Twig;

use OHMedia\FormBundle\Repository\FormRepository;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Twig\Environment;
use Twig\TwigFunction;

class WysiwygExtension extends AbstractWysiwygExtension
{
    private array $forms = [];

    public function __construct(
        private FormRepository $formRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('form_builder', [$this, 'form'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    public function form(Environment $twig, ?int $id = null)
    {
        if (isset($this->forms[$id])) {
            // prevent infinite recursion
            return;
        }

        $this->forms[$id] = true;

        $formEntity = $id ? $this->formRepository->find($id) : null;

        if (!$formEntity || !$formEntity->isPublished()) {
            return '';
        }

        $fields = $formEntity->getFields();

        if (!count($fields)) {
            return '';
        }

        $form = $formBuilder->buildForm($formEntity);

        return $twig->render('@OHMediaForm/form_builder.html.twig', [
            'form' => $form,
        ]);
    }
}
