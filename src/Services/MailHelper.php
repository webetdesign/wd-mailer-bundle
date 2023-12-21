<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Entity\MailTranslation;

readonly class MailHelper
{

    public function __construct(
        private MailEventManager      $mailEventManager,
        private Environment           $twig,
        private ParameterBagInterface $parameterBag)
    {
    }

    public function initTranslationObjects(Mail $mail, bool $override = false): void
    {
        $locales = $this->parameterBag->get('wd_mailer.locales');

        $define_locales = array_map(fn($item) => $item->getLocale(), $mail->getTranslations()->toArray());

        if (!empty($mail->getEvent())) {
            $config = $this->mailEventManager->getConfig($mail->getEvent());
            foreach ($locales as $l) {
                if (!in_array($l, $define_locales) || $override) {
                    $subject = !empty($config['subject']) ? is_string($config['subject']) ? $config['subject'] : $config['subject'][$l] ?? null : null;

                    $html = $this->getTemplate($config['html'], $l);
                    $text = $this->getTemplate($config['text'], $l);

                    $trans = $mail->getTranslations()->toArray()[$l] ?? null;
                    if (!$override || !$trans) {
                        $trans = new MailTranslation();
                    }


                    $trans->setLocale($l);
                    $trans->setTitle($subject);
                    $trans->setContentHtml($html);
                    $trans->setContentTxt($text);
                    $mail->addTranslation($trans);
                }
            }
        }
    }

    private function getTemplate($path, $locale): ?string
    {
        if (empty($path)) {
            return null;
        }
        $path = preg_replace('/\{locale\}/', $locale, $path);

        try {
            $wrapper = $this->twig->load($path);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            return null;
        }

        return $wrapper->getSourceContext()->getCode();
    }

}
