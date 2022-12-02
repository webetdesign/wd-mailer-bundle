<?php

namespace WebEtDesign\MailerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Nette\Utils\Type;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class Mail
 * @package WebEtDesign\MailerBundle\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="mailer__mail")
 * @method string getTitle()
 * @method string getContentHtml()
 * @method null|string getContentTxt()
 */
#[ORM\Entity]
#[ORM\Table(name: "mailer__mail")]
class Mail implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=false, name="name")
     */
    #[ORM\Column(name: 'name', type: Types::STRING, nullable: false)]
    private string $name = '';

    /**
     * @ORM\Column(type="string", nullable=false, name="event")
     */
    #[ORM\Column(name: 'event', type: Types::STRING, nullable: false)]
    private string $event = '';

    /**
     * @ORM\Column(type="text", nullable=false, name="`to`")
     */
    #[ORM\Column(name: "`to`", type: Types::TEXT, nullable: false)]
    private string $to = '';

    /**
     * @ORM\Column(type="string", nullable=false, name="`from`")
     */
    #[ORM\Column(name: "`from`", type: Types::STRING, nullable: false)]
    private string $from = '';

    /**
     * @ORM\Column(type="string", nullable=true, name="attachments")
     */
    #[ORM\Column(name: 'attachments', type: Types::STRING, nullable: true)]
    private ?string $attachments = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $online;

    public function __toString()
    {
        return $this->getName();
    }

    public function __call($method, $arguments)
    {
        if ($method == '_action') {
            return null;
        }

        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(), $method);
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

    public function getAttachementsAsArray(): array
    {
        $attachements = preg_replace('/[\s\r\n]/', ',', $this->getAttachments());

        $attachements = explode(',', $attachements);

        return array_values(array_filter($attachements, static fn($value) => !is_null($value) && $value !== ''));
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

    public function getAttachments(): ?string
    {
        return $this->attachments;
    }

    public function setAttachments(?string $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * @param bool $online
     * @return Mail
     */
    public function setOnline(bool $online): Mail
    {
        $this->online = $online;
        return $this;
    }


    // Getter and setter for split input in few tabs in admin form

    public function getTranslationsTitle()
    {
        return $this->getTranslations();
    }

    public function getTranslationsContentHtml()
    {
        return $this->getTranslations();
    }

    public function getTranslationsContentText()
    {
        return $this->getTranslations();
    }

    public function setTranslationsTitle(iterable $translations): void
    {
        $this->ensureIsIterableOrCollection($translations);

        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }
    }

    public function setTranslationsContentHtml(iterable $translations): void
    {
        $this->ensureIsIterableOrCollection($translations);

        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }
    }

    public function setTranslationsContentText(iterable $translations): void
    {
        $this->ensureIsIterableOrCollection($translations);

        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }
    }
}
