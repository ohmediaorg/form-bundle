<?php

namespace OHMedia\FormBundle\Form;

use OHMedia\FormBundle\Entity\Form;
use OHMedia\TimezoneBundle\Form\Type\DateTimeType;
use OHMedia\UtilityBundle\Form\OneEmailPerLineType;
use OHMedia\WysiwygBundle\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $form = $options['data'];

        $builder->add('name');

        $builder->add('description', WysiwygType::class, [
            'required' => false,
            'help' => 'Shown above the form.',
        ]);

        $builder->add('recipients', OneEmailPerLineType::class);

        $builder->add('agreement_text', TextareaType::class, [
            'label' => 'Agreement Text',
            'required' => false,
            'help' => 'If filled out, the user will be forced to agree to this text via checkbox in order to submit the form.',
            'attr' => [
                'rows' => 5,
            ],
        ]);

        $builder->add('success_message', TextareaType::class, [
            'label' => 'Success Message',
            'help' => 'This message is shown to the user on the website after the form is submitted.',
        ]);

        $builder->add('subject', TextType::class, [
            'label' => 'Email Subject',
        ]);

        $builder->add('email_message', TextareaType::class, [
            'label' => 'Email Message',
            'required' => false,
            'help' => 'If an email is sent to the user, this message will be included in that email.',
            'attr' => [
                'rows' => 5,
            ],
        ]);

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
