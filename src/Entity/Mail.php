<?php

namespace WebEtDesign\MailerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Mail
 * @package WebEtDesign\MailerBundle\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="mailer__mail")
 */
class Mail
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=false, name="name")
     */
    private string $name = '';

    /**
     * @ORM\Column(type="string", nullable=false, name="event")
     */
    private string $event = '';

    /**
     * @ORM\Column(type="text", nullable=false, name="`to`")
     */
    private string $to = '';

    /**
     * @ORM\Column(type="string", nullable=false, name="`from`")
     */
    private string $from = '';

    /**
     * @ORM\Column(type="string", nullable=false, name="title")
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="text", nullable=true, name="content_html")
     */
    private ?string $contentHtml = null;

    /**
     * @ORM\Column(type="text", nullable=true, name="content_txt")
     */
    private ?string $contentTxt = null;

    /**
     * @ORM\Column(type="string", nullable=true, name="attachments")
     */
    private ?string $attachments = null;

    public function __toString()
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToAsArray(): array
    {
        $emails = preg_replace('/[\s\r\n]/', ',', $this->getTo());

        $emails = explode(',', $emails);

        return array_values(array_filter($emails, static fn($value) => !is_null($value) && $value !== ''));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(?string $contentHtml): self
    {
        $this->contentHtml = $contentHtml;

        return $this;
    }

    public function getContentTxt(): ?string
    {
        return $this->contentTxt;
    }

    public function setContentTxt(?string $contentTxt): self
    {
        $this->contentTxt = $contentTxt;

        return $this;
    }

    public function getAttachments(): ?string
    {
        return $this->attachments;
    }

    public function setAttachments(?string $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }
}
