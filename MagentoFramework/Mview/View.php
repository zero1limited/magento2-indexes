<?php
namespace Zero1\Indexes\MagentoFramework\Mview;

use Magento\Framework\Mview\View\ChangelogTableNotExistsException;
use Magento\Framework\Mview\View\SubscriptionFactory;
use Psr\Log\LoggerInterface;

class View extends \Magento\Framework\Mview\View
{
    protected $logger;

    /**
     * Max versions to load from database at a time
     */
    public static $maxVersionQueryBatch = 100000;

    /**
     * @param \Magento\Framework\Mview\ConfigInterface $config
     * @param \Magento\Framework\Mview\ActionFactory $actionFactory
     * @param \Magento\Framework\Mview\View\StateInterface$state
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @param SubscriptionFactory $subscriptionFactory
     * @param LoggerInterface $logger
     * @param array $data
     * @param array $changelogBatchSize
     */
    public function __construct(
        \Magento\Framework\Mview\ConfigInterface $config,
        \Magento\Framework\Mview\ActionFactory $actionFactory,
        \Magento\Framework\Mview\View\StateInterface $state,
        \Magento\Framework\Mview\View\ChangelogInterface $changelog,
        SubscriptionFactory $subscriptionFactory,
        LoggerInterface $logger,
        array $data = [],
        array $changelogBatchSize = []
    ) {
        $this->config = $config;
        $this->actionFactory = $actionFactory;
        $this->state = $state;
        $this->changelog = $changelog;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->changelogBatchSize = $changelogBatchSize;
        $this->logger = $logger;
        parent::__construct(
            $config,
            $actionFactory,
            $state,
            $changelog,
            $subscriptionFactory,
            $data,
            $changelogBatchSize
        );
    }

    protected function log($messgae, $context = [])
    {
        if(php_sapi_name() == 'cli'){
            echo $messgae.' - '.\GuzzleHttp\json_encode($context).PHP_EOL;
        }
        $this->logger->debug($messgae, $context);
        return $this;
    }

    /**
     * Materialize view by IDs in changelog
     *
     * @return void
     * @throws \Exception
     */
    public function update()
    {
        if ($this->getState()->getStatus() == \Magento\Framework\Mview\View\StateInterface::STATUS_IDLE) {
            try {
                $currentVersionId = $this->getChangelog()->getVersion();
            } catch (ChangelogTableNotExistsException $e) {
                return;
            }
            $lastVersionId = (int) $this->getState()->getVersionId();
            $action = $this->actionFactory->get($this->getActionClass());

            try {
                $this->getState()->setStatus(\Magento\Framework\Mview\View\StateInterface::STATUS_WORKING)->save();

                $versionBatchSize = self::$maxVersionQueryBatch;
                $batchSize = isset($this->changelogBatchSize[$this->getChangelog()->getViewId()])
                    ? $this->changelogBatchSize[$this->getChangelog()->getViewId()]
                    : self::DEFAULT_BATCH_SIZE;

                $this->log('running mview', [
                    'view_id' => $this->getChangelog()->getViewId(),
                    'batch_size' => $batchSize,
                    'action' => get_class($action),
                ]);
                $start = time();
                $totalIdsProcessed = 0;

                for ($versionFrom = $lastVersionId; $versionFrom < $currentVersionId; $versionFrom += $versionBatchSize) {
                    // Don't go past the current version for atomicy.
                    $versionTo = min($currentVersionId, $versionFrom + $versionBatchSize);
                    $ids = $this->getChangelog()->getList($versionFrom, $versionTo);
                    $totalIdsProcessed += count($ids);

                    // We run the actual indexer in batches.  Chunked AFTER loading to avoid duplicates in separate chunks.
                    $chunks = array_chunk($ids, $batchSize);
                    foreach ($chunks as $ids) {
                        $action->execute($ids);
                    }
                }

                $this->getState()->loadByView($this->getId());
                $statusToRestore = $this->getState()->getStatus() == \Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED
                    ? \Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED
                    : \Magento\Framework\Mview\View\StateInterface::STATUS_IDLE;
                $this->getState()->setVersionId($currentVersionId)->setStatus($statusToRestore)->save();
            } catch (\Exception $exception) {
                $this->getState()->loadByView($this->getId());
                $statusToRestore = $this->getState()->getStatus() == \Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED
                    ? \Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED
                    : \Magento\Framework\Mview\View\StateInterface::STATUS_IDLE;
                $this->getState()->setStatus($statusToRestore)->save();
                throw $exception;
            }

            $this->log('finished mview', [
                'view_id' => $this->getChangelog()->getViewId(),
                'run_time' => (time() -$start),
                'entries_processed' => $totalIdsProcessed
            ]);
        }
    }
}