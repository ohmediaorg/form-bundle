<?php

namespace OHMedia\FormBundle\Form;

use OHMedia\FormBundle\Entity\FormField;
use OHMedia\UtilityBundle\Form\OnePerLineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formField = $options['data'];

        $builder->add('label', TextType::class, [
            'help' => 'Keep this as short and descriptive as possible.',
        ]);

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
            'choices' => FormField::getTypeChoices(),
        ]);

        $builder->add('help', TextareaType::class, [
            'label' => 'Help Text',
            'required' => false,
            'help' => 'Shown below the form field. Use this to provide more instruction that does not fit in the label.',
        ]);

        $data = $builder->create('data', FormType::class, [
            'label' => false,
            'row_attr' => [
                'class' => 'fieldset-nostyle',
            ],
        ]);

        $builder->add($data);

        $data->add('choices', OnePerLineType::class, [
            'mapped' => false,
        ]);

        $data->add('multiple', ChoiceType::class, [
            'label' => 'Allow multiple choices',
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'expanded' => true,
            'row_attr' => [
                'class' => 'fieldset-nostyle mb-3',
            ],
        ]);

        $data->add('copy', ChoiceType::class, [
            'label' => 'Should a copy of the submission be sent to the email address entered into this field?',
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'expanded' => true,
            'row_attr' => [
                'class' => 'fieldset-nostyle mb-3',
            ],
        ]);

        $data->add('reply', ChoiceType::class, [
            'label' => 'Should the internal email be set to reply to the email address entered into this field?',
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'expanded' => true,
            'row_attr' => [
                'class' => 'fieldset-nostyle mb-3',
            ],
        ]);

        $data->add('autocomplete', ChoiceType::class, [
            'label' => 'Browser Hint',
            'help' => 'Helps the browser know how to autocomplete this field in case the label is not descriptive enough. If you are not sure, select "None".',
            'choices' => [
                'Full Name' => 'name',
                'First Name' => 'given-name',
                'Last Name' => 'family-name',
                'Address' => 'address-line1',
                'City/Town' => 'address-level2',
                'Province/State' => 'address-level1',
                'Postal Code/ZIP' => 'postal-code',
            ],
            'required' => false,
            'placeholder' => 'None',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormField::class,
        ]);
    }
}
