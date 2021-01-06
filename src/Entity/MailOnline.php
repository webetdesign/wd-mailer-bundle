<?php


namespace WebEtDesign\MailerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Mail
 * @package WebEtDesign\MailerBundle\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="mailer__mail_online")
 */
class MailOnline
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected string $hash = '';

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected string $html = '';

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $created_at = null;
    
    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $updated_at = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return MailOnline
     */
    public function setId(?int $id): MailOnline
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return MailOnline
     */
    public function setHash(string $hash): MailOnline
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return MailOnline
     */
    public function setHtml(string $html): MailOnline
    {
        $this->html = $html;
        return $this;
    }

    /**
     * @return null
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param null $created_at
     * @return MailOnline
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return null
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param null $updated_at
     * @return MailOnline
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }
}
