<?php

namespace WebEtDesign\MailerBundle\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Entity\MailOnline;
use WebEtDesign\MailerBundle\Exception\MailTransportException;

class Twig implements MailTransportInterface
{
    private Environment  $twig;
    private Swift_Mailer $mailer;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    public function __construct(
        Environment $twig,
        Swift_Mailer $mailer,
        RouterInterface $router,
        EntityManagerInterface $em,
        ParameterBagInterface $parameterBag
    ) {
        $this->twig         = $twig;
        $this->mailer       = $mailer;
        $this->router       = $router;
        $this->em           = $em;
        $this->parameterBag = $parameterBag;
    }

    public function send(Mail $mail, $locale = null, $values = null, $to = null): ?int
    {
        if (!$values) {
            $this->twig->disableStrictVariables();
        }

        if (!$locale) {
            $locale = $this->parameterBag->get('wd_mailer.default_locale');
        }

        $mail->setCurrentLocale($locale);

        $hash        = hash('sha256',
            $mail->getId() . $mail->getFrom() . $mail->getTitle() . time());
        $online_link = $this->router->generate('wd_mailer_mail_view', ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL);

        if ($mail->isOnline()) {
            $values['ONLINE_LINK'] = $online_link;
        }


        $tpl     = $this->twig->createTemplate($mail->getContentHtml());
        $content = $tpl->render($values ?? []);

        if (!empty($mail->getContentTxt())) {
            $tpl        = $this->twig->createTemplate($mail->getContentTxt());
            $contentTxt = $tpl->render($values ?? []);
        }

        if ($mail->isOnline()) {
            $om = new MailOnline();
            $om->setHash($hash);
            $om->setHtml($content);

            $this->em->persist($om);
            $this->em->flush();
        }

        $message = (new Swift_Message($mail->getTitle()))
            ->setFrom($mail->getFrom())
            ->setTo($to)
            ->setBody(
                $content,
                'text/html'
            );

        if (isset($contentTxt)) {
            $message->addPart($contentTxt, 'text/plain');
        }

        return $this->mailer->send($message);
    }
}
