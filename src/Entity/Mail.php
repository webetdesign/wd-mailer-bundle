<?php

namespace WebEtDesign\MailerBundle\Entity;

use App\Entity\Client\Client;
use App\SoftDelete\Entity\SoftDeleteEntityInterface;
use App\SoftDelete\Entity\SoftDeleteEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DocumentManager\Entity\Document;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use WebEtDesign\MailerBundle\Enum\CategoryEnum;

/**
 * @method string getTitle()
 * @method string getContentHtml()
 * @method null|string getContentTxt()
 */
#[ORM\Entity]
#[ORM\Table(name: 'mailer__mail')]
class Mail implements TranslatableInterface, SoftDeleteEntityInterface
{
    use TranslatableTrait;
    use SoftDeleteEntityTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, nullable: false)]
    private string $name = '';

    #[ORM\Column(name: 'event', type: Types::STRING, nullable: false)]
    private string $event = '';

    #[ORM\Column(name: '`to`', type: Types::TEXT, nullable: false)]
    private string $to = '';

    #[ORM\Column(name: '`from`', type: Types::STRING, nullable: false)]
    private string $from = '';

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $fromName = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $replyTo = null;

    #[ORM\Column(nullable: true)]
    private ?CategoryEnum $category = CategoryEnum::TRANSACTIONAL_EMAIL;

    #[ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'mails')]
    #[ORM\JoinTable(name: 'mailer__mail_has_document')]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

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

    /**
     * @return string|null
     */
    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    /**
     * @param string|null $fromName
     * @return Mail
     */
    public function setFromName(?string $fromName): Mail
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    /**
     * @param string|null $replyTo
     * @return Mail
     */
    public function setReplyTo(?string $replyTo): Mail
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getCategory(): ?CategoryEnum
    {
        return $this->category;
    }

    public function setCategory(?CategoryEnum $category): Mail
    {
        $this->category = $category;

        return $this;
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        $this->documents->removeElement($document);

        return $this;
    }
}
