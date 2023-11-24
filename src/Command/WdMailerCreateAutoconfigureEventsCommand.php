<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WebEtDesign\MailerBundle\Doctrine\MailManager;
use WebEtDesign\MailerBundle\Entity\Mail;
use WebEtDesign\MailerBundle\Services\MailEventManager;
use WebEtDesign\MailerBundle\Services\MailHelper;

#[AsCommand(
    name: 'wd_mailer:autoconfigure',
    description: 'Create mail in database',
)]
class WdMailerCreateAutoconfigureEventsCommand extends Command
{
    /**
     * @param MailEventManager $mailEventManager
     * @param MailManager $mailManager
     * @param ParameterBagInterface $parameterBag
     * @param MailHelper $mailHelper
     * @param EntityManagerInterface $em
     */
    public function __construct(
        private readonly MailEventManager       $mailEventManager,
        private readonly MailManager            $mailManager,
        private readonly ParameterBagInterface  $parameterBag,
        private readonly MailHelper             $mailHelper,
        private readonly EntityManagerInterface $em,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);

        $configs = $this->parameterBag->get('wd_mailer.auto_configure_events');


        foreach ($configs as $event => $config) {
            $eventConfig = $this->mailEventManager->getConfig($event);
            if ($eventConfig === null) {
                $io->error('No configuration found for ' . $event);
                continue;
            }

            $mail = $this->mailManager->findByEventName($event);

            if (!empty($mail)) {
                $io->comment('[Skipping] Mail configuration allready exist for ' . $event);
                continue;
            }

            $mail = new Mail();
            $mail->setEvent($event)
                ->setName($eventConfig['label'])
                ->setFrom($config['from'])
                ->setFromName($config['from_name'])
                ->setTo($config['to'])
                ->setReplyTo($config['reply_to']);

            $this->mailHelper->initTranslationObjects($mail);

            $io->success('New mail configuration was created for ' . $event);

            $this->em->persist($mail);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
