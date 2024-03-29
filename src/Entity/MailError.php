<?php


namespace WebEtDesign\MailerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: "mailer__mail_error")]
class MailError
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING)]
    protected ?string $mail = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $object = null;

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
    public function getMail(): ?string
    {
        return $this->mail;
    }

    /**
     * @param string|null $mail
     * @return MailError
     */
    public function setMail(?string $mail): MailError
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getObject(): ?string
    {
        return $this->object;
    }

    /**
     * @param string|null $object
     * @return MailError
     */
    public function setObject(?string $object): MailError
    {
        $this->object = $object;
        return $this;
    }

}
