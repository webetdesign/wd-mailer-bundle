<?php

namespace WebEtDesign\MailerBundle\Doctrine;

use App\SoftDelete\Repository\SoftDeleteRepositoryInterface;
use App\SoftDelete\Repository\SoftDeleteRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WebEtDesign\MailerBundle\Entity\Mail;

/**
 * @method Mail|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mail|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mail[]    findAll()
 * @method Mail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailRepository extends ServiceEntityRepository implements SoftDeleteRepositoryInterface
{
    use SoftDeleteRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mail::class);
    }
}
