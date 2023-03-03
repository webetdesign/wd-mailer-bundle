<?php
declare(strict_types=1);


namespace WebEtDesign\MailerBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TplParametersType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['params'] = $options['params'];
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['params']);
    }

    public function getBlockPrefix(): string
    {
        return 'wd_mailer_tpl_params';
    }


}
