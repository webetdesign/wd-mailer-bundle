<?php

namespace WebEtDesign\MailerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use WebEtDesign\MailerBundle\Entity\MailOnline;

class MailController extends AbstractController
{

    /**
     * @Route("/mail/{hash}", name="wd_mailer_mail_view")
     * @ParamConverter("mail", options={"mapping": {"hash": "hash"}})
     * @param MailOnline $mail
     * @return Response
     */
    public function __invoke(MailOnline $mail)
    {
        if (!$mail) {
            throw new NotFoundHttpException();
        }

        return new Response($mail->getHtml());
    }
}
