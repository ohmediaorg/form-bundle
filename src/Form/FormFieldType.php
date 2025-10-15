<?php

namespace OHMedia\FormBundle\Form;

use OHMedia\FormBundle\Entity\FormField;
use OHMedia\UtilityBundle\Form\OnePerLineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

        $data = $formField->getData();

        // TODO: for type=email, add checkboxes for
        // - send copy to this email
        // - use this email as the Reply-To header

        // TODO: form event to make this field required if type = choice
        $builder->add('choices', OnePerLineType::class, [
            'mapped' => false,
            'data' => $data['choices'] ?? null,
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
            'data' => $data['multiple'] ?? null,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormField::class,
        ]);
    }
}
