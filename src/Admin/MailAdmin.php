<?php

declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Form\Admin\MailContentHtmlTranslationType;
use WebEtDesign\MailerBundle\Form\Admin\MailContentTextTranslationType;
use WebEtDesign\MailerBundle\Form\Admin\MailTitleTranslationType;
use WebEtDesign\MailerBundle\Form\TplParametersType;
use WebEtDesign\MailerBundle\Util\ObjectConverter;

final class MailAdmin extends AbstractAdmin
{
    private ParameterBagInterface $parameterBag;

    /**
     * @inheritDoc
     */
    public function __construct(
        $code,
        $class,
        $baseControllerName,
        ParameterBagInterface $parameterBag
    ) {
        parent::__construct($code, $class, $baseControllerName);
        $this->parameterBag = $parameterBag;
    }


    private $mailEvents;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('test', 'test/{id}');
    }

    /**
     * @inheritDoc
     */
    public function configureActionButtons(array $list, string $action, ?object $object = null): array
    {
        if (in_array($action, ['show', 'edit'], true)
            && $this->hasRoute('test')
        ) {
            $list['test'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_test'),
            ];
        }

        return $list;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('name')
            ->add('event')
            ->add('to')
            ->add('from');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->add('name')
            ->add('event')
            ->add('to')
            ->add('from')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    //                    'show'   => [],
                    'edit'   => [],
                    'delete' => [],
                    'test'   => [
                        'template' => '@WDMailer/admin/mail/list__action_test.html.twig',
                    ],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $locales = $this->parameterBag->get('wd_mailer.locales');
        $locale  = $this->parameterBag->get('wd_mailer.default_locale');

        $this->setFormTheme(array_merge($this->getFormTheme(), [
            '@WDMailer/admin/form/wd_mailer_tpl_params.html.twig'
        ]));

        /** @var Mail $subject */
        $subject = $this->getSubject();
        if ($subject && $subject->getEvent()) {
            $event       = $this->getMailEvents()[$subject->getEvent()]['class'];
            $eventParams = ObjectConverter::getAvailableMethods($event);
        }

        $formMapper
            ->tab('Général')
            ->with('#', ['class' => 'col-md-6', 'box_class' => 'box box-primary box-no-header'])
            ->add('name')
            ->add('event', ChoiceType::class, [
                'choices' => $this->getMailEventsChoices()
            ])
            ->add('online', CheckboxType::class, [
                'help'     => 'Permets d’enregistrer les mails pour le visualiser en ligne. <br>' .
                    'Ajouter dans le template HTML un lien avec comme href : <code>{{ ONLINE_LINK }}</code>',
                'required' => false,
            ])
            ->end();

        $formMapper
            ->with('', ['class' => 'col-md-6', 'box_class' => 'box box-primary box-no-header'])
            ->add('from', null, ['label' => 'De'])
            ->add('to', null, [
                'label' => 'Destinataire(s)',
                'help'  => 'Un ou plusieurs emails séparés par des virgules ou des retours ligne.<br>' .
                    'Les variables sont également acceptées sous cette syntaxe : __email__ ou __user.email__.<br>',
            ])
            ->add('attachments', null, [
                'required' => false,
                'label' => 'Fichiers joints',
                'help'  =>'variables de type File acceptées sous cette syntaxe : __fichier__ ou __objet.fichier__.',
            ])
        ;


        $formMapper
            //            ->add('title', null, ['label' => 'Objet'])
            ->add('translationsTitle', TranslationsFormsType::class, [
                'label'            => false,
                'locales'          => $locales,
                'default_locale'   => [$locale],
                'required_locales' => [$locale],
                'form_type'        => MailTitleTranslationType::class

            ])
            ->end()
            ->end();

        $formMapper
            ->tab('HTML')
            ->with('Contenu HTML',
                ['class' => 'col-md-8', 'box_class' => 'box box-primary box-no-header'])
            ->add('translationsContentHtml', TranslationsFormsType::class, [
                'label'          => false,
                'locales'        => $locales,
                'default_locale' => [$locale],
                'form_type'      => MailContentHtmlTranslationType::class
            ])
            ->end()
            ->with('Paramètres',
                ['class' => 'col-md-4', 'box_class' => 'box box-primary box-no-header'])
            ->add('paramsHtml', TplParametersType::class, [
                'mapped' => false,
                'label'  => false,
                'params' => $eventParams ?? [],
            ])
            ->end()
            ->end();

        $formMapper
            ->tab('Texte')
            ->with('', ['class' => 'col-md-8', 'box_class' => 'box box-primary box-no-header'])
            ->add('translationsContentText', TranslationsFormsType::class, [
                'label'          => false,
                'locales'        => $locales,
                'default_locale' => [$locale],
                'form_type'      => MailContentTextTranslationType::class
            ])
            ->end()
            ->with('Paramètres',
                ['class' => 'col-md-4', 'box_class' => 'box box-primary box-no-header'])
            ->add('paramsTxt', TplParametersType::class, [
                'mapped' => false,
                'label'  => false,
                'params' => $eventParams ?? [],
            ])
            ->end()
            ->end()//
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('event')
            ->add('to')
            ->add('from')
            ->add('title')
            ->add('contentHtml')
            ->add('contentTxt')
            ->add('attachments');
    }

    /**
     * @return mixed
     */
    public function getMailEvents()
    {
        return $this->mailEvents;
    }

    /**
     * @return mixed
     */
    public function getMailEventsChoices()
    {
        $events  = $this->getMailEvents();
        $choices = [];
        foreach ($events as $key => $event) {
            $choices[$key] = $event['label'] ?? $key;
        }

        return array_flip($choices);
    }

    /**
     * @param mixed $mailEvents
     * @return MailAdmin
     */
    public function setMailEvents($mailEvents)
    {
        $this->mailEvents = $mailEvents;
        return $this;
    }
}
