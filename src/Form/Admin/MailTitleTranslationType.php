<?php

namespace WebEtDesign\MailerBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use WebEtDesign\MailerBundle\Entity\MailTranslation;

class MailTitleTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $constraints = [];

        if ($options['required']){
            $constraints = [
                new NotBlank(),
            ];
        }

        $builder
            ->add('title', TextType::class, [
                    'label'       => 'Objet',
                    'required'    => $options['required'],
                    'constraints' => $constraints
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MailTranslation::class,
        ]);
    }
}
