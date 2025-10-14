<?php

namespace OHMedia\FormBundle\Form;

use OHMedia\FormBundle\Entity\FormField;
use OHMedia\UtilityBundle\Form\OnePerLineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formField = $options['data'];

        $builder->add('label');

        // TODO: make this true by default
        $builder->add('required', ChoiceType::class, [
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'expanded' => true,
            'row_attr' => [
                'class' => 'fieldset-nostyle mb-3',
            ],
        ]);

        $builder->add('type', ChoiceType::class, [
            'choices' => [
                'Text' => TextType::class,
                'Number' => NumberType::class,
                'Phone Number' => PhoneType::class,
                'Email' => EmailType::class,
                'Date' => DateType::class,
                'Textarea' => TextareaType::class,
                'Choice' => ChoiceType::class,
            ],
        ]);

        $builder->add('help', TextType::class, [
            'label' => 'Help Text',
            'required' => false,
            'help' => 'Shown below the form field.',
        ]);

        // TODO: form event to make this field required if type = choice
        $builder->add('choices', OnePerLineType::class, [
            'mapped' => false,
        ]);

        $builder->add('multiple', ChoiceType::class, [
            'label' => 'Allow multiple choices',
            'mapped' => false,
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'expanded' => true,
            'row_attr' => [
                'class' => 'fieldset-nostyle mb-3',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormField::class,
        ]);
    }
}
