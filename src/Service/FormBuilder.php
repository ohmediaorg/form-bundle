<?php

namespace OHMedia\FormBundle\Service;

use OHMedia\AntispamBundle\Form\Type\CaptchaType;
use OHMedia\AntispamBundle\Validator\Constraints\NoForeignCharacters;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FormBuilder
{
    private const NAME_LENGTH = 100;
    private const EMAIL_LENGTH = 180;
    private const PHONE_LENGTH = 20;
    private const MESSAGE_LENGTH = 1000;

    public function __construct(
        private FormFactoryInterface $formFactory,
    ) {
    }

    public function buildForm(Form $formEntity): ?FormInterface
    {
        $formBuilder = $this->formFactory->createBuilder(FormType::class);

        $fields = $formEntity->getFields();

        foreach ($fields as $field) {
            $this->addField($field);
        }

        $formBuilder->add('name', TextType::class, [
            'attr' => [
                'maxlength' => self::NAME_LENGTH,
            ],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Please fill out your name.',
                ]),
                new Assert\Length([
                    'max' => self::NAME_LENGTH,
                    'maxMessage' => 'Your name must be {{ limit }} characters or less.',
                ]),
                new Assert\NoSuspiciousCharacters(),
                new NoForeignCharacters(),
            ],
        ]);

        $formBuilder->add('email', EmailType::class, [
            'attr' => [
                'maxlength' => self::EMAIL_LENGTH,
            ],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Please fill out your email.',
                ]),
                new Assert\Email(
                    null,
                    'Please enter a valid email address.'
                ),
                new Assert\Length([
                    'max' => self::EMAIL_LENGTH,
                    'maxMessage' => 'Your email address must be {{ limit }} characters or less.',
                ]),
                new Assert\NoSuspiciousCharacters(),
                new NoForeignCharacters(),
            ],
        ]);

        $formBuilder->add('phone', TelType::class, [
            'attr' => [
                'maxlength' => self::PHONE_LENGTH,
            ],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Please fill out your phone number.',
                ]),
                new Assert\Length([
                    'max' => self::PHONE_LENGTH,
                    'maxMessage' => 'Your phone number must be {{ limit }} characters or less.',
                ]),
                new Assert\NoSuspiciousCharacters(),
                new NoForeignCharacters(),
            ],
        ]);

        $formBuilder->add('message', TextareaType::class, [
            'attr' => [
                'maxlength' => self::MESSAGE_LENGTH,
                'rows' => 5,
            ],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Please enter a message.',
                ]),
                new Assert\Length([
                    'max' => self::MESSAGE_LENGTH,
                    'maxMessage' => 'Please enter a message of {{ limit }} characters or less.',
                ]),
                new Assert\NoSuspiciousCharacters(),
                new NoForeignCharacters(),
            ],
        ]);

        $formBuilder->add('captcha', CaptchaType::class);

        $formBuilder->add('submit', SubmitType::class);

        return $formBuilder->getForm();
    }

    private function addField(
        FormBuilderInterface $builder,
        FormField $field,
    ): void {
        $name = $field->getName();

        $options = [
            'label' => $field->getLabel(),
            'required' => $field->getRequired(),
            'help' => $field->getHelp(),
        ];

        if ($field->isTypeChoice()) {
            $fieldOptions = $field->getOptions();

            $options['choices'] = array_combine(
                $fieldOptions['choices'],
                $fieldOptions['choices'],
            );

            $options['multiple'] = $fieldOptions['multiple'];
        }

        $constraints = [
            new Assert\NoSuspiciousCharacters(),
            new NoForeignCharacters(),
        ];

        if ($field->getRequired()) {
            $constraints[] = new Assert\NotBlank([
                'message' => 'Please fill out '.$field->getLabel(),
            ]);
        }

        $builder->add($name, $field->getType(), $options);
    }
}
