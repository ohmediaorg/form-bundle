<?php

namespace OHMedia\FormBundle\Service;

use OHMedia\AntispamBundle\Form\Type\CaptchaType;
use OHMedia\AntispamBundle\Validator\Constraints\NoForeignCharacters;
use OHMedia\FormBundle\Entity\Form;
use OHMedia\FormBundle\Entity\FormField;
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
            $this->addAgreementField($builder, $agreementText);
        }

        $builder->add('captcha', CaptchaType::class);

        $builder->add('submit', SubmitType::class);

        return $builder->getForm();
    }

    private function addAgreementField(
        FormBuilderInterface $builder,
        string $label,
    ): void {
        $truncate = 25;

        $truncated = strlen($label) > $truncate
            ? substr($label, 0, $truncate).'...'
            : $label;

        $builder->add('agreement', CheckboxType::class, [
            'label' => $label,
            'constraints' => [
                new Assert\IsTrue([
                    'message' => "You must agree to \"$truncated\".",
                ]),
            ],
        ]);
    }

    private function addField(
        FormBuilderInterface $builder,
        FormField $field,
    ): void {
        $name = $field->getName();
        $label = $field->getLabel();
        $required = $field->isRequired();

        $constraints = [];

        $attr = [];

        $isString = $field->isTypeText()
            || $field->isTypeEmail()
            || $field->isTypePhone()
            || $field->isTypeTextarea();

        if ($isString) {
            $constraints[] = new Assert\NoSuspiciousCharacters();

            $constraints[] = new NoForeignCharacters();
        }

        if ($required) {
            $constraints[] = new Assert\NotBlank([
                'message' => "\"$label\" should not be blank.",
            ]);
        }

        $maxLength = null;

        if ($field->isTypeText()) {
            $maxLength = 100;
        } elseif ($field->isTypeEmail()) {
            $maxLength = 180;

            $constraints[] = new Assert\Email(
                null,
                "\"$label\" is not a valid email address.",
            );
        } elseif ($field->isTypePhone()) {
            $constraints[] = new Phone(
                null,
                "\"$label\" does not match the suggested format.",
            );
        } elseif ($field->isTypeTextarea()) {
            $maxLength = 1000;
        }

        if ($maxLength) {
            $constraints[] = new Assert\Length([
                'max' => $maxLength,
                'maxMessage' => "\"$label\" should be {{ limit }} characters or less.",
            ]);

            $attr['maxlength'] = $maxLength;
        }

        $options = [
            'label' => $label,
            'required' => $required,
            'constraints' => $constraints,
            'attr' => $attr,
        ];

        if (!$field->isTypePhone()) {
            $options['help'] = nl2br(htmlspecialchars($field->getHelp()));
            $options['help_html'] = true;
        }

        if ($field->isTypeChoice()) {
            $fieldOptions = $field->getOptions();

            $options['choices'] = array_combine(
                $fieldOptions['choices'],
                $fieldOptions['choices'],
            );

            $options['multiple'] = $fieldOptions['multiple'];

            $options['expanded'] = count($fieldOptions['choices']) < 5;
        } elseif ($field->isTypeDate()) {
            $options['widget'] = 'single_text';
        } elseif ($field->isTypeNumber()) {
            $options['invalid_message'] = "\"$label\" is not a valid number.";
        }

        $builder->add($name, $field->getType(), $options);
    }
}
