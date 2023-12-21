<?php

declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use JetBrains\PhpStorm\Pure;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Form\Admin\MailContentsTranslationType;
use WebEtDesign\MailerBundle\Services\MailEventManager;
use WebEtDesign\MailerBundle\Services\MailHelper;
use WebEtDesign\MailerBundle\Util\ObjectConverter;

final class MailAdmin extends AbstractAdmin
{
    private array $mailEvents;

    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly MailEventManager      $mailEventManager,
        private readonly Security              $security,
        private readonly MailHelper            $mailHelper,
    )
    {
        $this->mailEvents = $this->mailEventManager->getEvents();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setTranslationDomain('wd_mailer');
        $this->setLabelTranslatorStrategy(new UnderscoreLabelTranslatorStrategy());
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('test', 'test/{id}');
        $collection->add('live_preview', '{id}/live_preview/{mode}/{locale}');
    }

    /**
     * @inheritDoc
     */
    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        if (in_array($action, ['show', 'edit'], true)
            && $this->hasRoute('test')
        ) {
            $buttonList['test'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_test'),
            ];
        }

        return $buttonList;
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
            ->add('name')
            ->add('event')
            ->add('to')
            ->add('from')
            ->add('fromName')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    //                    'show'   => [],
                    'edit'   => [],
                    'delete' => [],
//                    'test'   => [
//                        'template' => '@WDMailer/admin/mail/list__action_test.html.twig',
//                    ],
                ],
            ]);
    }

    protected function alterNewInstance(object $object): void
    {
        $locales = $this->parameterBag->get('wd_mailer.locales');
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $locales = $this->parameterBag->get('wd_mailer.locales');
        $locale  = $this->parameterBag->get('wd_mailer.default_locale');

        $this->setFormTheme(array_merge($this->getFormTheme(), [
            '@WDMailer/admin/form/form_layout.html.twig',
            '@WDMailer/admin/form/mail_admin_contents_layout.html.twig',
        ]));

        /** @var Mail $subject */
        $subject        = $this->getSubject();

        if ($subject->getEvent()) {
            $event       = $this->getMailEvents()[$subject->getEvent()]['class'] ?? null;
            $eventParams = ObjectConverter::getAvailableMethods($event);
        }

        if (!empty($subject->getEvent())) {
            $this->mailHelper->initTranslationObjects($subject);
        }

        if (empty($subject->getId()) || empty($subject->getEvent())) {
            $formMapper
                ->tab('config')
                ->with('#', ['class' => 'col-md-6', 'box_class' => 'header_none'])
                ->add('event', ChoiceType::class, [
                    'label'   => 'Évènement',
                    'choices' => $this->getMailEventsChoices()
                ])
                ->end();
        } else {
            $formMapper
                ->tab('config')
                ->with('1', ['class' => 'col-md-6', 'box_class' => 'header_none']);

            if ($this->security->isGranted('ROLE_ADMIN_CMS')) {
                $formMapper->add('event', null, [
                    'label'    => 'Évènement',
                    'disabled' => 'disabled',
                ]);
            }

            $formMapper
                ->add('from', null, ['label' => 'Courriel de l\'émetteur', 'row_attr' => ['class' => 'col-md-6', 'style' => 'padding-left: 0px;']])
                ->add('fromName', null, ['label' => 'Nom de l\'émetteur', 'row_attr' => ['class' => 'col-md-6', 'style' => 'padding-right: 0px;']])
                ->add('replyTo', null, ['label' => 'Répondre à'])
                ->add('to', null, [
                    'label' => 'Destinataire(s)',
                    'help'  => 'Un ou plusieurs emails séparés par des virgules ou des retours ligne. ' .
                        'Les variables sont également acceptées sous cette syntaxe : __email__',
                ])
                ->end()
                ->end();

            $formMapper
                ->tab('content')
                ->with('Contenu', ['class' => 'col-md-12', 'box_class' => 'header_none'])
                ->add('translations', TranslationsFormsType::class, [
                    'label'            => false,
                    'locales'          => $locales,
                    'default_locale'   => [$locale],
                    'required_locales' => [$locale],
                    'form_type'        => MailContentsTranslationType::class
                ])
                ->end()
                ->end();
        }
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

    public function getMailEvents(): array
    {
        return $this->mailEvents;
    }

    #[Pure] public function getMailEventsChoices(): array
    {
        $events  = $this->getMailEvents();
        $choices = [];
        foreach ($events as $key => $event) {
            $choices[$key] = $event['label'] ?? $key;
        }

        return array_flip($choices);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PER_PAGE] = 500;
    }

    public function getPerPageOptions(): array
    {
        $perPageOptions   = parent::getPerPageOptions();
        $perPageOptions[] = 500;

        return $perPageOptions;
    }
}
