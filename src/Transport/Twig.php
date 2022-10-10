<?php

namespace WebEtDesign\MailerBundle\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Entity\MailError;
use WebEtDesign\MailerBundle\Entity\MailOnline;
use WebEtDesign\MailerBundle\Event\MailEventInterface;
use WebEtDesign\MailerBundle\Exception\MailTransportException;

class Twig implements MailTransportInterface
{
    private Environment  $twig;
    private Serializer $serializer;
    private RouterInterface $router;
    private EntityManagerInterface $em;
    private ParameterBagInterface $parameterBag;
    private MailerInterface $mailer;


    public function __construct(
        Environment $twig,
        MailerInterface $mailer,
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

    /**
     * @throws SyntaxError
     * @throws LoaderError
     * @throws MailTransportException
     */
    public function send(Mail $mail, MailEventInterface $event, $locale = null, $values = null, $to = null, $debug = false): ?int
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

        $tpl = $this->twig->createTemplate($mail->getContentHtml());

        try {
            $content = $tpl->render($values ?? []);
        } catch (Error $error) {
            if(!$debug) {
                try {
                    $this->alertAdministrators($mail, $values, $error->getRawMessage());
                } catch (TransportExceptionInterface | LoaderError | RuntimeError | SyntaxError | Throwable $e) {
                }
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

        $message = (new Email())
            ->subject($mail->getTitle())
            ->from($mail->getFrom())
            ->html(
                $content
            );

        if ($event->getReplyTo() !== null) {
            $message->replyTo($event->getReplyTo());
        }

        if (is_array($to)) {
            foreach ($to as $adress) {
                $message->addTo($adress);
            }
        } else {
            $message->addTo($to);
        }

        if (isset($contentTxt)) {
            $message->text($contentTxt);
        }

        foreach($this->getAttachments($mail, $values) as $attachment) {
            if ($attachment instanceof UploadedFile) {
                $message->attachFromPath($attachment->getRealPath(), $attachment->getClientOriginalName());
            }else{
                $message->attachFromPath($attachment->getRealPath(), $attachment->getFileName());
            }
        }

        try {
            $this->mailer->send($message);
            return 1;
        } catch (TransportExceptionInterface $e) {
            return 0;
        }
    }

    /**
     * @throws Throwable
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function alertAdministrators(Mail $mail, $values, $error) {
        $mailError = new MailError();
        $mailError
            ->setMail($mail)
            ->setObject($this->serializer->serialize($values, 'json'));
        $this->em->persist($mailError);
        $this->em->flush();

        $project = $this->router->getContext()->getHost();
        $url = $this->router->getContext()->getPathInfo();
        $template = $mail->getName();
        $tpl     = $this->twig->loadTemplate('@WDMailer/admin/mail/error.html.twig');
        $content = $tpl->render(['project' => $project, 'template' => $template, 'error' => $error, 'url' => $url, 'mailErrorId' => $mailError->getId()]);

        $message = (new Email())
            ->subject('Erreur lors de la soumission du mail')
            ->from($mail->getFrom())
            ->to($_ENV['REPORT_ADDRESS'] ?? 'equipe@webetdesign.com')
            ->html(
                $content,
                'text/html'
            );
        $this->mailer->send($message);
    }

    /**
    * @param Mail $mail
    * @param $values
    * @return array
     */
    private function getAttachments(Mail $mail, $values): array
    {
        $attachments = $mail->getAttachementsAsArray();

        $attachments = !is_array($attachments) ? [$attachments] : $attachments;
        foreach ($attachments as $k => $item) {
            if (!preg_match('/^__(.*)__$/', $item, $matches)) {
                continue;
            }

            unset($attachments[$k]);

            $split = explode('.', $matches[1]);
            $attachment  = $values[array_shift($split)] ?? [];


            foreach ($split as $slip_item) {
                $method = 'get' . ucfirst($slip_item);
                if (!method_exists($attachment, $method)) {
                    $attachment = null;
                    break;
                }
                $attachment = $attachment->$method();
            }

            if ($attachment) {
                if (is_array($attachment)) {
                    $attachments = [...$attachments, ...$attachment];
                } else {
                    $attachments[] = $attachment;
                }
            }
        }
        return $attachments;
    }

    public function getMailer(): MailerInterface
    {
        return $this->mailer;
    }

    public function setMailer(MailerInterface $mailer): void
    {
        $this->mailer = $mailer;
    }

}
