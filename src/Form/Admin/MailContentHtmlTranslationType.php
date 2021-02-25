<?php

namespace WebEtDesign\MailerBundle\Form\Admin;

use Norzechowicz\AceEditorBundle\Form\Extension\AceEditor\Type\AceEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\MailerBundle\Entity\MailTranslation;

class MailContentHtmlTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contentHtml', AceEditorType::class, [
                'label'  => false,
                'width'  => '100%',
                'height' => '1000px',
                'mode'   => 'ace/mode/twig',
                'theme'  => 'ace/theme/monokai',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MailTranslation::class,
        ]);
    }
}
