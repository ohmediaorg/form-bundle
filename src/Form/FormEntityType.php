<?php

namespace OHMedia\FormBundle\Form;

use OHMedia\FormBundle\Entity\Form;
use OHMedia\TimezoneBundle\Form\Type\DateTimeType;
use OHMedia\UtilityBundle\Form\OneEmailPerLineType;
use OHMedia\WysiwygBundle\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $form = $options['data'];

        $builder->add('name');

        $builder->add('description', WysiwygType::class);

        $builder->add('recipients', OneEmailPerLineType::class);

        $builder->add('published_at', DateTimeType::class, [
            'label' => 'Published Date/Time',
            'required' => false,
            'help' => 'The form will only be shown if this value is populated and in the past.',
            'widget' => 'single_text',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Form::class,
        ]);
    }
}
