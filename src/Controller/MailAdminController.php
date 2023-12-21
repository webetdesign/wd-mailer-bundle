<?php

declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Services\EmailBuilder;

final class MailAdminController extends CRUDController
{
    public function __construct(private readonly EmailBuilder $emailBuilder)
    {
    }

    public function livePreviewAction(Request $request, Mail $mail, string $mode, string $locale): Response
    {
        $content = !empty($request->getContent()) ? json_decode($request->getContent(), true) : null;

        $update = $content['update'] ?? null;

        switch ($mode) {
            case 'html':
                if ($update) {
                    $mail->translate($locale)->setContentHtml($update);
                }

                $email = $this->emailBuilder->emailHtml($mail, [], $locale, true);
                break;
            case 'text':
                if ($update) {
                    $mail->translate($locale)->setContentTxt($update);
                }

                $email = $this->emailBuilder->emailText($mail, [], $locale, true);
                break;
        }

        return new Response($email ?? null);
    }
}
