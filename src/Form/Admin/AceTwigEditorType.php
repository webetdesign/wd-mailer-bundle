<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AceTwigEditorType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'wd_mailer_twig_ace_editor';
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }


}
