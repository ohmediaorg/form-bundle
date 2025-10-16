<?php

namespace OHMedia\FormBundle\Service;

use OHMedia\AntispamBundle\Form\Type\CaptchaType;
use OHMedia\AntispamBundle\Validator\Constraints\NoForeignCharacters;
use OHMedia\FormBundle\Entity\Form;
use OHMedia\FormBundle\Entity\FormField;
use OHMedia\UtilityBundle\Form\PhoneType;
use OHMedia\UtilityBundle\Validator\Phone;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        if ($field->isTypeChoice()) {
            $type = ChoiceType::class;
        } elseif ($field->isTypeDate()) {
            $type = DateType::class;
        } elseif ($field->isTypeEmail()) {
            $type = EmailType::class;
        } elseif ($field->isTypeNumber()) {
            $type = NumberType::class;
        } elseif ($field->isTypePhone()) {
            $type = PhoneType::class;
        } elseif ($field->isTypeTextarea()) {
            $type = TextareaType::class;
        } else {
            $type = TextType::class;
        }

        $builder->add($field->getName(), $type, $this->getFieldOptions($field));
    }

    private function getFieldOptions(FormField $field): array
    {
        $label = $field->getLabel();

        $attr = [];

        $maxlength = $this->getFieldMaxlength($field);

        if ($maxlength) {
            $attr['maxlength'] = $maxlength;
        }

        $constraints = $this->getFieldConstraints($field, $maxlength);

        $options = [
            'label' => $label,
            'required' => $field->isRequired(),
            'constraints' => $constraints,
            'attr' => $attr,
        ];

        // OHMedia\UtilityBundle\Form\PhoneType has its own help text
        if (!$field->isTypePhone()) {
            $options['help'] = nl2br(htmlspecialchars($field->getHelp()));
            $options['help_html'] = true;
        }

        if ($field->isTypeChoice()) {
            $data = $field->getData();

            $options['choices'] = array_combine(
                $data['choices'],
                $data['choices'],
            );

            $options['multiple'] = $data['multiple'];

            $options['expanded'] = count($data['choices']) < 5;
        } elseif ($field->isTypeDate()) {
            $options['widget'] = 'single_text';
        } elseif ($field->isTypeNumber()) {
            $options['invalid_message'] = "\"$label\" is not a valid number.";
        }

        return $options;
    }

    private function getFieldConstraints(FormField $field, ?int $maxlength): array
    {
        $label = $field->getLabel();

        $constraints = [];

        $isString = $field->isTypeText()
            || $field->isTypeEmail()
            || $field->isTypePhone()
            || $field->isTypeTextarea();

        // requires a string value to compare
        if ($isString) {
            $constraints[] = new Assert\NoSuspiciousCharacters(
                null,
                "\"$label\" contains characters that are not allowed by the current restriction-level.",
                "\"$label\" contains invisible characters which is not allowed.",
                "\"$label\" is mixing numbers from different scripts which is not allowed.",
                "\"$label\" contains hidden overlay characters which is not allowed.",
            );

            $constraints[] = new NoForeignCharacters(
                "\"$label\" contains foreign characters that are not allowed.",
            );
        }

        if ($field->isRequired()) {
            $constraints[] = new Assert\NotBlank([
                'message' => "\"$label\" should not be blank.",
            ]);
        }

        if ($field->isTypePhone()) {
            $constraints[] = new Phone(
                null,
                "\"$label\" does not match the suggested format.",
            );
        } elseif ($field->isTypeEmail()) {
            $constraints[] = new Assert\Email(
                null,
                "\"$label\" is not a valid email address.",
            );
        }

        if ($maxlength) {
            $constraints[] = new Assert\Length([
                'max' => $maxlength,
                'maxMessage' => "\"$label\" should be {{ limit }} characters or less.",
            ]);
        }

        return $constraints;
    }

    private function getFieldMaxlength(FormField $field): ?int
    {
        $maxlength = null;

        // length doesn't apply to Choice, Date, or Number
        // Phone has regex

        if ($field->isTypeText()) {
            $maxlength = 100;
        } elseif ($field->isTypeEmail()) {
            $maxlength = 180;
        } elseif ($field->isTypeTextarea()) {
            $maxlength = 1000;
        }

        return $maxlength;
    }
}
