<?php

declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Admin;

use Norzechowicz\AceEditorBundle\Form\Extension\AceEditor\Type\AceEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class MailAdmin extends AbstractAdmin
{

    private $mailEvents;

    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->add('test', 'test/{id}');
    }

    /**
     * @inheritDoc
     */
    public function configureActionButtons($action, $object = null): array
    {
        $list = parent::configureActionButtons($action, $object);

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
            ->add('_action', null, [
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

        $formMapper
            ->tab('Général')
            ->with('#', ['class' => 'col-md-6', 'box_class' => 'box box-primary box-no-header'])
            ->add('name')
            ->add('event', ChoiceType::class, [
                'choices' => $this->getMailEventsChoices()
            ])
            ->add('online', CheckboxType::class, [
                'help' => 'Permets d’enregistrer les mails pour le visualiser en ligne. <br>' .
                    'Ajouter dans le template HTML un lien avec comme href : <code>{{ ONLINE_LINK }}</code>',
                'required' => false,
            ])
            ->end()
            ->with('', ['class' => 'col-md-6', 'box_class' => 'box box-primary box-no-header'])
            ->add('from', null, ['label' => 'De'])
            ->add('to', null, [
                'label' => 'Destinataire(s)',
                'help'  => 'Un ou plusieurs emails séparés par des virgules ou des retours ligne.<br>' .
                    'Les variables sont également acceptées sous cette syntaxe : __email__ ou __user.email__.<br>',
            ])
            ->add('title', null, ['label' => 'Objet'])
            ->end()
            ->end()
            ->tab('HTML')
            ->with('', ['class' => 'col-md-12', 'box_class' => 'box box-primary box-no-header'])
            ->add('contentHtml', AceEditorType::class, [
                'label'  => 'Contenu HTML',
                'width'  => '100%',
                'height' => '1000px',
                'mode'   => 'ace/mode/twig',
                'theme'  => 'ace/theme/monokai',
            ])
            ->end()
            ->end()
            ->tab('Texte')
            ->with('', ['class' => 'col-md-12', 'box_class' => 'box box-primary box-no-header'])
            ->add('contentTxt', AceEditorType::class, [
                'label'  => 'Contenu Text',
                'width'  => '100%',
                'height' => '1000px',
                'mode'   => 'ace/mode/twig',
                'theme'  => 'ace/theme/monokai',
            ])
            ->end()
            ->end()//            ->add('attachments')
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
        $events = $this->getMailEvents();
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
