<?php
namespace Zero1\Indexes\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanChangelogs extends Command
{
    protected $changelog;

    public function __construct(
        \Magento\Indexer\Cron\ClearChangelog $changelog
    ) {
        $this->changelog = $changelog;
        parent::__construct();
    }
    protected function configure()
    {
        $this->setName('indexer:clean-changelogs')
            ->setDescription('Blah Blah.');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->changelog->execute();
    }
}