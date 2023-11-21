<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\EventListener;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use ReflectionException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\EventDispatcher\Event;
use WebEtDesign\MailerBundle\Event\MailEventInterface;
use WebEtDesign\MailerBundle\Exception\MailTransportException;
use WebEtDesign\MailerBundle\Messenger\Message\EmailMessage;
use WebEtDesign\MailerBundle\Model\MailManagerInterface;
use WebEtDesign\MailerBundle\Services\EmailBuilder;
use WebEtDesign\MailerBundle\Services\MailEventManager;
use WebEtDesign\MailerBundle\Services\SymfonyMailerTransport;
use WebEtDesign\MailerBundle\Util\ObjectConverter;
use WebEtDesign\MailerBundle\Entity\Mail;

readonly class MailerListener
{

    protected const CACHE_KEY_DELAY               = 'wd_mailer.spool.cache_key_delay';
    protected const CACHE_KEY_QUEUE_MESSAGE_COUNT = 'wd_mailer.spool.cache_key_queue_message_count';
    protected const CACHE_TTL                     = 30;

    public function __construct(
        private EntityManagerInterface $em,
        private ParameterBagInterface  $parameterBag,
        private EmailBuilder           $emailBuilder,
        private MailManagerInterface   $mailManager,
        private MailEventManager       $eventManager,
        private SymfonyMailerTransport $mailerTransport,
        private MessageBusInterface    $bus,
        private CacheInterface         $cache,
    )
    {
    }

    /**
     * @throws ReflectionException
     * @throws MailTransportException
     */
    public function __invoke(Event $event, $name): void
    {
        $className = get_class($event);

        if (!array_key_exists(MailEventInterface::class, class_implements($className))) {
            return;
        }

        $eventConfig = $this->eventManager->getConfig($name);
        $adminConfig = $this->mailManager->findByEventName($name);
        if (empty($adminConfig) || empty($eventConfig)) {
            return;
        }

        $values = ObjectConverter::convertToArray($event);

        $locale = $event->getLocale() ?: $this->parameterBag->get('wd_mailer.default_locale');

        /** @var Mail $mail */
        foreach ($adminConfig as $mail) {
            $email = $this->emailBuilder->getEmail($mail, $event, $values, $locale);

            if ($eventConfig['spool']) {
                $message = new EmailMessage($email, $event, $name);
                $this->deferedToMessenger($message);
            } else {
                $this->mailerTransport->send($email, $event, $name);
            }
        }
    }

    private function deferedToMessenger($message): void
    {
        // Configuration de spool du bundle
        $batchSize = $this->parameterBag->get('wd_mailer.spool.batch_size');
        $batchIntervalSecond = $this->parameterBag->get('wd_mailer.spool.batch_interval_second');

        $con = $this->em->getConnection();

        // Valeur mise en cache
        $cachedAvailableAt = $this->cache->getItem(self::CACHE_KEY_DELAY);
        $cachedNbMessage   = $this->cache->getItem(self::CACHE_KEY_QUEUE_MESSAGE_COUNT);

        $cachedAvailableAt->expiresAfter(self::CACHE_TTL);
        $cachedNbMessage->expiresAfter(self::CACHE_TTL);

        // Si les valeurs mis en cache sont vide on les get de la BDD
        if ($cachedAvailableAt->get() === null || $cachedNbMessage->get() === null) {
            $stmt      = $con->prepare('SELECT * FROM mailer__message ORDER BY available_at DESC');
            $result    = $stmt->executeQuery([]);
            $messages  = $result->fetchAllAssociative();
            $nbMessage = count($messages);

            if ($nbMessage > 0) {
                $availableAt = DateTime::createFromFormat('Y-m-d H:i:s', $messages[0]['available_at']);
            } else {
                $availableAt = new DateTime('now');
            }

            $diff = $availableAt->getTimestamp() - (new DateTime('now'))->getTimestamp();
            if ($diff < 0) {
                $diff = 0;
            }

            $this->cache->delete(self::CACHE_KEY_DELAY);
            $this->cache->delete(self::CACHE_KEY_QUEUE_MESSAGE_COUNT);
        } else {
            $diff      = $cachedAvailableAt->get();
            $nbMessage = $cachedNbMessage->get();
        }

        // Calcule du batch
        if ($nbMessage === 0) {
            $diff = $batchIntervalSecond;
        } else {
            if ($diff < 0) {
                $diff = 0;
            }

            if ($nbMessage % $batchSize === 0) {
                $diff = $diff + $batchIntervalSecond;
            }
        }

        // Creation du message avec le delay
        $delay = new DelayStamp((int)$diff * 1000);
        $this->bus->dispatch($message, [$delay]);

        // Set des nouvelles valeurs dans le cache
        $cachedNbMessage->set($nbMessage + 1);
        $cachedAvailableAt->set($diff);
        $this->cache->save($cachedAvailableAt);
        $this->cache->save($cachedNbMessage);
    }
}
