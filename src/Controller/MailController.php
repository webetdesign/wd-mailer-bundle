<?php


namespace WebEtDesign\MailerBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use WebEtDesign\MailerBundle\Entity\MailOnline;

class MailController extends AbstractController
{
    #[ParamConverter('mail', options: ['mapping' => ['hash' => 'hash']])]
    #[Route('/mail/{hash}', name: 'wd_mailer_mail_view')]
    public function __invoke(MailOnline $mail): Response
    {
        return new Response($mail->getHtml());
    }
}
