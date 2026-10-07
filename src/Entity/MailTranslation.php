<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Entity;

use A2lix\TranslationFormBundle\Helper\OneLocaleInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[ORM\Entity]
#[ORM\Table(name: 'mailer__mail_translation')]
class MailTranslation implements TranslationInterface, OneLocaleInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'title', type: Types::STRING, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(name: 'content_html', type: Types::TEXT, nullable: true)]
    private ?string $contentHtml = null;

    #[ORM\Column(name: 'content_txt', type: Types::TEXT, nullable: true)]
    private ?string $contentTxt = null;

    /**
     * a2lix/translation-form-bundle 4 assigns the locale of a submitted
     * translation with `$translation->locale = $locale`, while the Knp trait
     * declares the property protected.
     */
    public function __set(string $name, mixed $value): void
    {
        if ('locale' !== $name) {
            throw new \LogicException(sprintf('Cannot set inaccessible property %s::$%s.', self::class, $name));
        }

        $this->setLocale($value);
    }

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
        $this->title = $title;
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
