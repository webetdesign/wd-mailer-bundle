<?php


namespace WebEtDesign\MailerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="mailer__mail_translation")
 */
class MailTranslation implements TranslationInterface
{

    use TranslationTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return MailTranslation
     */
    public function setTitle(?string $title = null): MailTranslation
    {
        $this->title = $title ? $title : 'trans_'.$this->getTranslatable()->getName();
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    /**
     * @param string|null $contentHtml
     * @return MailTranslation
     */
    public function setContentHtml(?string $contentHtml): MailTranslation
    {
        $this->contentHtml = $contentHtml;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContentTxt(): ?string
    {
        return $this->contentTxt;
    }

    /**
     * @param string|null $contentTxt
     * @return MailTranslation
     */
    public function setContentTxt(?string $contentTxt): MailTranslation
    {
        $this->contentTxt = $contentTxt;
        return $this;
    }
}
