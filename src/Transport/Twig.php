<?php

namespace WebEtDesign\MailerBundle\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PropertyAccess\PropertyAccessor;
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
    public function __construct(
        private Environment            $twig,
        private MailerInterface        $mailer,
        private RouterInterface        $router,
        private EntityManagerInterface $em,
        private ParameterBagInterface  $parameterBag,
        private SerializerInterface    $serializer,
        private LoggerInterface        $mailerLogger,
    )
    {
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

        $hash = hash('sha256',
            $mail->getId() . $mail->getFrom() . $mail->getTitle() . time());

        $online_link = $this->router->generate('wd_mailer_mail_view', ['hash' => $hash],
            UrlGeneratorInterface::ABSOLUTE_URL);

        if ($mail->isOnline()) {
            $values['ONLINE_LINK'] = $online_link;
        }

        $tpl = $this->twig->createTemplate($mail->getContentHtml());

        try {
            $content = $tpl->render($values ?? []);
        } catch (Exception $error) {
            $this->mailerLogger->error('WD_MAILER', (array)$error);

            return 1;
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
            ->subject($this->parseAndReplaceTitleVars($mail->getTitle(), $event))
            ->from(new Address($mail->getFrom(), $mail->getFromName()))
            ->html(
                $content
            );

        if (!empty($event->getReplyTo()) || !empty($mail->getReplyTo())) {
            $message->replyTo(new Address(!empty($mail->getReplyTo()) ? $mail->getReplyTo() : $event->getReplyTo()));
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

        foreach ($this->getAttachments($mail, $values) as $attachment) {
            if ($attachment instanceof UploadedFile) {
                $message->attachFromPath($attachment->getRealPath(), $attachment->getClientOriginalName());
            } else {
                $message->attachFromPath($attachment->getRealPath(), $attachment->getFileName());
            }
        }

        try {
            $this->mailer->send($message);

            return 0;
        } catch (TransportExceptionInterface $e) {
            $this->mailerLogger->error('WD_MAILER', (array)$e);

            return 1;
        }
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

            $split      = explode('.', $matches[1]);
            $attachment = $values[array_shift($split)] ?? [];

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

    private function parseAndReplaceTitleVars($title, MailEventInterface $event): string
    {
        preg_match_all('/__.+?__/', $title, $matches);

        $accessor = new PropertyAccessor();

        $vars = [];

        foreach ($matches[0] ?? [] as $match) {
            $var = substr($match, 2);
            $var = substr($var, 0, -2);

            if ($accessor->isReadable($event, $var)) {
                $vars[$match] = $accessor->getValue($event, $var);
            }
        }

        return str_replace(array_keys($vars), array_values($vars), $title);
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
