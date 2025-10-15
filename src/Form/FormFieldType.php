<?php

namespace OHMedia\FormBundle\Form;

use OHMedia\FormBundle\Entity\FormField;
use OHMedia\UtilityBundle\Form\OnePerLineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formField = $options['data'];

        $builder->add('label');

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
            'choices' => FormField::TYPE_CHOICES,
        ]);

        // TODO: hide for type=phone
        $builder->add('help', TextType::class, [
            'label' => 'Help Text',
            'required' => false,
            'help' => 'Shown below the form field.',
        ]);

        $options = $formField->getOptions();

        // TODO: for type=email, add checkboxes for
        // - send copy to this email
        // - use this email as the Reply-To header

        // TODO: form event to make this field required if type = choice
        $builder->add('choices', OnePerLineType::class, [
            'mapped' => false,
            'data' => $options['choices'] ?? null,
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
            'data' => $options['multiple'] ?? null,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormField::class,
        ]);
    }
}
