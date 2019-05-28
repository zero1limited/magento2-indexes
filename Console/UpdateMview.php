<?php
namespace Zero1\Indexes\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateMview extends Command
{
    protected $updateMview;

    public function __construct(
        \Magento\Indexer\Cron\UpdateMview $updateMview
    ) {
        $this->updateMview = $updateMview;
        parent::__construct();
    }
    protected function configure()
    {
        $this->setName('indexer:update-mview')
            ->setDescription('Blah Blah.');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$start = time();
        $this->updateMview->execute();
        
        echo 'Run time: '.(time() - $start).' seconds'.PHP_EOL;
        echo 'peak memory usage: '.(memory_get_peak_usage(true)/1024/1024).' MiB'.PHP_EOL;
    }
}