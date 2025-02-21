<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Services;

use App\DTO\Core\EmailComposerDTO;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Twig\Environment;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Event\MailEventInterface;
use WebEtDesign\MailerBundle\Exception\MailTransportException;
use WebEtDesign\MailerBundle\Util\ObjectConverter;

readonly class EmailBuilder
{
    public function __construct(
        private Environment     $twig,
        private LoggerInterface $wdMailerLogger,
    )
    {
    }

    public function getEmail(Mail $mail, MailEventInterface $event, array $values, string $locale): Email
    {
        if (method_exists($event, 'getMailDto') && $event->getMailDto() !== null) {
            return $this->getEmailFromDto($mail, $event);
        }

        $email = new Email();
        $email->subject($this->parseAndReplaceTitleVars($mail->translate($locale)->getTitle(), $event))
            ->from(new Address($mail->getFrom(), $mail->getFromName() ?: ''))
            ->html($this->emailHtml($mail, $values, $locale))
            ->text($this->emailText($mail, $values, $locale));

        foreach ($this->getRecipients($mail, $values) as $recipient) {
            $email->addTo($recipient);
        }

        if (!empty($event->getReplyTo()) || !empty($mail->getReplyTo())) {
            $email->replyTo(new Address(!empty($mail->getReplyTo()) ? $mail->getReplyTo() : $event->getReplyTo()));
        }

        foreach ($event->getAttachements() as $attachment) {
            if ($attachment instanceof UploadedFile) {
                $email->attachFromPath($attachment->getRealPath(), $attachment->getClientOriginalName());
            } else {
                $email->attachFromPath($attachment->getRealPath(), $attachment->getFileName());
            }
        }

        return $email;
    }

    protected function getEmailFromDto(Mail $mail, MailEventInterface $event): Email
    {
        /** @var EmailComposerDTO $mailDto */
        $mailDto = $event->getMailDto();

        $email = new Email();

        $email
            ->from(new Address($mail->getFrom(), $mail->getFromName() ?: ''))
            ->subject($mailDto->getSubject())
            ->html($mailDto->getBody());

        foreach ($mailDto->getRecipient()->getAddresses() as $address) {
            $email->addTo($address);
        }

        foreach ($mailDto->getRecipientCC()->getAddresses() as $address) {
            $email->addCc($address);
        }

        if (!empty($event->getReplyTo()) || !empty($mail->getReplyTo())) {
            $email->replyTo(new Address(!empty($mail->getReplyTo()) ? $mail->getReplyTo() : $event->getReplyTo()));
        }

        foreach ($event->getAttachements() as $attachment) {
            if ($attachment instanceof UploadedFile) {
                $email->attachFromPath($attachment->getRealPath(), $attachment->getClientOriginalName());
            } else {
                $email->attachFromPath($attachment->getRealPath(), $attachment->getFileName());
            }
        }

        return $email;
    }

    public function emailHtml(Mail $mail, array $values, string $locale, bool $preview = false): ?string
    {
        if (empty($mail->translate($locale)->getContentHtml())) {
            return null;
        }

        if ($preview) {
            $this->twig->disableStrictVariables();
        }

        $tpl = $this->twig->createTemplate($mail->translate($locale)->getContentHtml());

        try {
            $content = $tpl->render($values);
        } catch (Exception $error) {
            $this->wdMailerLogger->error('WD_MAILER', (array)$error);

            dump($error);
            die();

            return null;
        }

        return $content;
    }

    public function emailText(Mail $mail, array $values, string $locale, bool $preview = false): ?string
    {
        if (empty($mail->translate($locale)->getContentTxt())) {
            return null;
        }

        if ($preview) {
            $this->twig->disableStrictVariables();
        }

        $tpl = $this->twig->createTemplate($mail->translate($locale)->getContentTxt());

        try {
            $content = $tpl->render($values);
        } catch (Exception $error) {
            $this->wdMailerLogger->error('WD_MAILER', (array)$error);

            return null;
        }

        return $content;
    }

    /**
     * @param Mail $mail
     * @param $values
     * @return array
     * @throws MailTransportException
     */
    private function getRecipients(Mail $mail, $values): array
    {
        $to = $mail->getToAsArray();
        if (!$to) {
            throw new MailTransportException('No destination found');
        }
        $to = !is_array($to) ? [$to] : $to;
        foreach ($to as $k => $item) {
            if (!preg_match('/^__(.*)__$/', $item, $matches)) {
                continue;
            }

            unset($to[$k]);

            $split = explode('.', $matches[1]);
            $dest  = $values[array_shift($split)] ?? [];

            foreach ($split as $split_item) {
                $method = 'get' . ucfirst($split_item);
                if (!method_exists($dest, $method)) {
                    $dest = null;
                    break;
                }
                $dest = $dest->$method();
            }

            if ($dest) {
                if (is_array($dest)) {
                    $to = [...$to, ...$dest];
                } else {
                    $to[] = $dest;
                }
            }
        }

        return array_map(fn($item) => new Address($item), $to);
    }

    private function parseAndReplaceTitleVars($title, $values): string
    {
        preg_match_all('/__.+?__/', $title, $matches);

        $accessor = new PropertyAccessor();

        $vars = [];

        foreach ($matches[0] ?? [] as $match) {
            $var = substr($match, 2);
            $var = substr($var, 0, -2);

            if ($accessor->isReadable($values, $var)) {
                $vars[$match] = $accessor->getValue($values, $var);
            }
        }

        return str_replace(array_keys($vars), array_values($vars), $title);
    }

    public function getEmailDto(Mail $mail, MailEventInterface $event): EmailComposerDTO
    {
        $locale = $event->getLocale() ?? 'fr';

        $dto = new EmailComposerDTO();

        $values = ObjectConverter::convertToArray($event);
        $dto->setSubject($this->parseAndReplaceTitleVars($mail->translate($locale)->getTitle(), $event));
        $dto->setBody($this->emailHtml($mail, $values, $locale));

        return $dto;
    }
}
