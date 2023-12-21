<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TplLiveEditorType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'wd_mailer_twig_live_editor';
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('mode');

        $resolver->addAllowedValues('mode', ['html', 'text']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['mode'] = $options['mode'];
    }

}
