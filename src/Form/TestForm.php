<?php

namespace WebEtDesign\MailerBundle\Form;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class TestForm extends AbstractType
{
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * @inheritDoc
     */
    public function __construct(ParameterBagInterface $parameterBag) {
        $this->parameterBag = $parameterBag;
    }


    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->parameterBag->get('wd_mailer.locales');

        $choices = [];
        foreach ($locales as $locale) {
            $choices[$locale] = $locale;
        }

        $builder->add('email', EmailType::class);
        $builder->add('locale', ChoiceType::class, [
            'choices' => $choices
        ]);
    }
}
