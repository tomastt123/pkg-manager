<?php

namespace App\Command;

use App\Message\ExtractDocumentEntities;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:extract-entities',
    description: 'Dispatches NLP extraction for a given document ID'
)]
class ExtractEntitiesCommand extends Command
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();
        $this->bus = $bus;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'The ID of the document to process')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (int) $input->getArgument('id');
        $this->bus->dispatch(new ExtractDocumentEntities($id));
        $output->writeln(sprintf('Dispatched ExtractDocumentEntities for document ID %d', $id));

        return Command::SUCCESS;
    }
}
