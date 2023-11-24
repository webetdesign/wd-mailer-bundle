<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use WebEtDesign\MailerBundle\Entity\MailTranslation;

class MailContentsTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

        $builder
            ->add('contentHtml', TplLiveEditorType::class, [
                'label' => false,
                'mode' => 'html',
            ]);

        $builder
            ->add('contentTxt', TplLiveEditorType::class, [
                'label' => false,
                'mode' => 'text',
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'mail_admin_contents';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MailTranslation::class,
        ]);
    }
}
