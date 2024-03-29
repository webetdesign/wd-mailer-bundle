<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\MailerBundle\Entity\MailTranslation;

class MailContentTextTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contentTxt', TplLiveEditorType::class, [
                'label' => false,
                //                'width'  => '100%',
                //                'height' => '1000px',
                //                'mode'   => 'ace/mode/twig',
                //                'theme'  => 'ace/theme/monokai',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MailTranslation::class,
        ]);
    }
}
