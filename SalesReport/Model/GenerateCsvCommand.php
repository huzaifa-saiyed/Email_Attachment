<?php

namespace Kitchen\SalesReport\Model;

use Magento\Framework\App\State;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCsvCommand extends Command
{
    protected $orderCollectionFactory;
    protected $state;
    protected $directory;
    protected $scopeConfig;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        State $state,
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->state = $state;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->scopeConfig = $scopeConfig;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('kitchen:ordercsv')
             ->setDescription('Export orders from the last week to a CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    
        try{

        $orderCollection = $this->orderCollectionFactory->create();

        $endDate = date('Y-m-d 23:59:59'); 
        // print_r($endDate);
        $startDate = date('Y-m-d 00:00:00', strtotime('-1 week')); 
        // print_r($startDate);die;


        $orderCollection->addAttributeToFilter('created_at', ['from' => $startDate, 'to' => $endDate]);

        $filename = date('Y-m-d') . '.csv';
        $filepath = 'order_exports/' . $filename;

        $this->directory->create('order_exports');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $stream->writeCsv(['Order ID', 'Customer', 'Order Date','Status', 'Order Total']);

        foreach ($orderCollection as $order) {
            $stream->writeCsv([
                $order->getIncrementId(),
                $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
                $order->getCreatedAt(),
                $order->getStatus(),
                $order->getGrandTotal()
            ]);
        }

        $output->writeln('Weekly orders have been exported to ' . $filepath);

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
    catch (\Exception $e) {
        $output->writeln($e->getMessage());
    }
 }
}
