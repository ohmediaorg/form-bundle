<?php

namespace OHMedia\FormBundle\Service;

use OHMedia\AntispamBundle\Form\Type\CaptchaType;
use OHMedia\AntispamBundle\Validator\Constraints\NoForeignCharacters;
use OHMedia\UtilityBundle\Validator\Phone;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FormBuilder
{
    public function __construct(
        private FormFactoryInterface $formFactory,
    ) {
    }

    public function buildForm(Form $formEntity): ?FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class);

        $fields = $formEntity->getFields();

        foreach ($fields as $field) {
            $this->addField($builder, $field);
        }

        if ($agreementText = $formEntity->getAgreementText()) {
            $builder->add('agreement', CheckboxType::class, [
                'label' => $agreementText,
            ]);
        }

        $builder->add('captcha', CaptchaType::class);

        $builder->add('submit', SubmitType::class);

        return $builder->getForm();
    }

    private function addField(
        FormBuilderInterface $builder,
        FormField $field,
    ): void {
        $name = $field->getName();
        $label = $field->getLabel();

        $constraints = [
            new Assert\NoSuspiciousCharacters(),
            new NoForeignCharacters(),
        ];

        if ($field->getRequired()) {
            $constraints[] = new Assert\NotBlank([
                'message' => "$label: should not be blank.",
            ]);
        }

        $maxLength = null;

        if ($field->isTypeText()) {
            $maxLength = 100;
        } elseif ($field->isTypeEmail()) {
            $maxLength = 180;

            $constraints[] = new Assert\Email(
                null,
                "$label: is not a valid email address."
            );
        } elseif ($field->isTypePhone()) {
            $constraints[] = new Phone();
        } elseif ($field->isTypeText()) {
            $maxLength = 1000;
        }

        if ($maxLength) {
            $constraints[] = new Assert\Length([
                'max' => $maxLength,
                'maxMessage' => "$label: should be {{ limit }} characters or less.",
            ]);
        }

        $options = [
            'label' => $label,
            'required' => $field->getRequired(),
            'help' => $field->getHelp(),
            'constraints' => $constraints,
        ];

        if ($field->isTypeChoice()) {
            $fieldOptions = $field->getOptions();

            $options['choices'] = array_combine(
                $fieldOptions['choices'],
                $fieldOptions['choices'],
            );

            $options['multiple'] = $fieldOptions['multiple'];
        }

        $builder->add($name, $field->getType(), $options);
    }
}
