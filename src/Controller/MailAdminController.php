<?php

declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Form\TestForm;
use WebEtDesign\MailerBundle\Transport\TransportChain;

final class MailAdminController extends CRUDController
{
    private TransportChain $transportChain;

    public function __construct(TransportChain $transportChain)
    {
        $this->transportChain = $transportChain;
    }

    public function testAction(Request $request, Mail $mail): Response
    {
        $form = $this->createForm(TestForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transport = $this->transportChain->get('twig');
            if ($transport) {
                $transport->send($mail, $form->getData()['locale'], null, $form->getData()['email']);
            }
        }

        return $this->renderWithExtraParams('@WDMailer/admin/mail/test.html.twig', [
            'form'   => $form->createView(),
            'object' => $mail,
            'action' => 'test',
        ]);
    }
}
