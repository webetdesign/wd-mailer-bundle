<?php

namespace WebEtDesign\MailerBundle\Doctrine;

use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Enum\CategoryEnum;
use WebEtDesign\MailerBundle\Model\AbstractMailManager;

class MailManager extends AbstractMailManager
{
    private MailRepository $repository;

    public function __construct(MailRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param $name
     * @return Mail[]
     */
    public function findByEventName($name): array
    {
        return $this->repository->findBy([
            'event' => $name,
        ]);
    }

    public function findByCategory(CategoryEnum $category): array
    {
        return $this->repository->findBy([
            'category' => $category
        ]);
    }
}
