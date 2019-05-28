<?php
namespace Zero1\Indexes\Console;

use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use \Magento\Catalog\Model\ResourceModel\Category;
use \Magento\Framework\App\State;

class ReindexAllInvalid extends Command
{
    protected $reindexAllInvalid;

    public function __construct(
        \Magento\Indexer\Cron\ReindexAllInvalid $reindexAllInvalid
    ) {
        $this->reindexAllInvalid = $reindexAllInvalid;
        parent::__construct();
    }
    protected function configure()
    {
        $this->setName('indexer:reindex-all-invalid')
            ->setDescription('Blah Blah.');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->reindexAllInvalid->execute();
    }
}