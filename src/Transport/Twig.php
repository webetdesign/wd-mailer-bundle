<?php

namespace WebEtDesign\MailerBundle\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;
use Twig\Error\Error;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Entity\MailError;
use WebEtDesign\MailerBundle\Entity\MailOnline;
use WebEtDesign\MailerBundle\Exception\MailTransportException;

class Twig implements MailTransportInterface
{
    private Environment  $twig;
    private Swift_Mailer $mailer;
    private Serializer $serializer;
    /**
     * @var RouterInterface
     */
    private RouterInterface $router;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;


    public function __construct(
        Environment $twig,
        Swift_Mailer $mailer,
        RouterInterface $router,
        EntityManagerInterface $em,
        ParameterBagInterface $parameterBag,
        SerializerInterface $serializer
    ) {
        $this->twig         = $twig;
        $this->mailer       = $mailer;
        $this->router       = $router;
        $this->em           = $em;
        $this->parameterBag = $parameterBag;
        $this->serializer = $serializer;
    }

    public function send(Mail $mail, $locale = null, $values = null, $to = null, $debug = false): ?int
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
        try {
            $content = $tpl->render($values ?? []);
        } catch (Error $error) {
            if(!$debug) {
                $this->alertAdministrators($mail, $values, $error->getRawMessage());
            }
            if($this->parameterBag->get("kernel.environment") === "dev") {
                throw new MailTransportException($error->getRawMessage());
            } else {
                return 0;
            }
        }

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

        foreach($this->getAttachements($mail, $values) as $attachment) {
            $message->attach(\Swift_Attachment::fromPath($attachment->getRealPath()));
        }

        return $this->mailer->send($message);
    }

    public function alertAdministrators(Mail $mail, $values, $error) {
        $mailError = new MailError();
        $mailError
            ->setMail($mail)
            ->setObject($this->serializer->serialize($values, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]));
        $this->em->persist($mailError);
        $this->em->flush();

        $project = $this->router->getContext()->getHost();
        $url = $this->router->getContext()->getPathInfo();
        $template = $mail->getName();
        $tpl     = $this->twig->loadTemplate('@WDMailer/admin/mail/error.html.twig');
        $content = $tpl->render(['project' => $project, 'template' => $template, 'error' => $error, 'url' => $url, 'mailErrorId' => $mailError->getId()]);

        $message = (new Swift_Message('Erreur lors de la soumission du mail'))
            ->setFrom($mail->getFrom())
            ->setTo($_ENV['REPORT_ADDRESS'] ?? 'equipe@webetdesign.com')
            ->setBody(
                $content,
                'text/html'
            );
        $this->mailer->send($message);
    }

    /**
    * @param Mail $mail
    * @param $values
    * @return array
    * @throws MailTransportException
    */
    private function getAttachements(Mail $mail, $values)
    {
        $attachements = $mail->getAttachementsAsArray();

        $attachements = !is_array($attachements) ? [$attachements] : $attachements;
        foreach ($attachements as $k => $item) {
            if (!preg_match('/^__(.*)__$/', $item, $matches)) {
                continue;
            }

            unset($attachements[$k]);

            $split = explode('.', $matches[1]);
            $attachement  = $values[array_shift($split)] ?? [];


            foreach ($split as $item) {
                $method = 'get' . ucfirst($item);
                if (!method_exists($attachement, $method)) {
                    $attachement = null;
                    break;
                }
                $attachement = $attachement->$method();
            }

            if ($attachement) {
                if (is_array($attachement)) {
                    $attachements = [...$attachements, ...$attachement];
                } else {
                    $attachements[] = $attachement;
                }
            }
        }

        return $attachements;
    }

    /**
     * @return Swift_Mailer
     */
    public function getMailer(): Swift_Mailer
    {
        return $this->mailer;
    }

    /**
     * @param Swift_Mailer $mailer
     */
    public function setMailer(Swift_Mailer $mailer): void
    {
        $this->mailer = $mailer;
    }

}
