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
use WebEtDesign\MailerBundle\Doctrine\MailRepository;
use WebEtDesign\MailerBundle\Services\MailEventManager;
use WebEtDesign\MailerBundle\Services\MailHelper;

#[AsCommand(
    name: 'wd_mailer:refresh-mail-config',
    description: 'Create mail in database',
)]
class WdMailerRefreshConfigCommand extends Command
{
    public function __construct(
        private readonly MailEventManager       $mailEventManager,
        private readonly MailRepository         $mailRepository,
        private readonly MailHelper             $mailHelper,
        private readonly EntityManagerInterface $em,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('event', InputArgument::OPTIONAL, 'Event')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $event = $input->getArgument('event');

        if (empty($event)) {
            $events = $this->getMailEventsChoices();
            $event  = $io->choice('Event ', $events);
        }

        $config = $this->mailEventManager->getConfig($event);

        if ($config === null) {
            $io->error('Event not found !');

            return Command::FAILURE;
        }

        $mail = $this->mailRepository->findOneByEvent($event);

        if (empty($mail)) {
            $io->error('Mail configuration not found !');

            return Command::FAILURE;
        }

        $this->mailHelper->initTranslationObjects($mail, true);

        $this->em->persist($mail);
        $this->em->flush();

        $io->success('Mail has been updated !');

        return Command::SUCCESS;
    }

    private function getMailEventsChoices(): array
    {
        $events  = $this->mailEventManager->getEvents();
        $choices = [];
        foreach ($events as $key => $event) {
            $choices[$key] = $event['label'] ?? $key;
        }

        return $choices;
    }
}
