<?php

namespace Kitchen\SalesReport\Model;

use Magento\Framework\App\State;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend_Pdf;
use Zend_Pdf_Page;
use Zend_Pdf_Style;
use Zend_Pdf_Font;
use Zend_Pdf_Color_Rgb;

class GenerateDailyPdf extends Command
{
    protected $orderCollectionFactory;
    protected $fileFactory;
    protected $state;
    protected $directory;
  
    public function __construct(
        FileFactory $fileFactory,
        OrderCollectionFactory $orderCollectionFactory,
        State $state,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->fileFactory = $fileFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->state = $state;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('kitchen:orderpdf')
             ->setDescription('Export orders from the last 24 hours to a PDF file');
    }

    protected function drawHeader($page, $font, $style, $height, $width)
    {
        $style->setFont($font, 12);
        $page->setStyle($style);
        $title = __("ProCraft Phoenix");
        $page->drawText($title, ($width - 140) / 2, $height - 30, 'UTF-8'); 

        $style->setFont($font, 14);
        $page->setStyle($style);
        $subtitle = __("Sales Order Summary");
        $page->drawText($subtitle, ($width - 180) / 2, $height - 50, 'UTF-8'); 
    }
    protected function drawHeaderColumn($page, $font, $style, $x, $height, $width)
    {
        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->drawText(__('Order ID'), $x + 5, $this->y - 10, 'UTF-8');
        $page->drawText(__('Customer'), $x + 90, $this->y - 10, 'UTF-8');
        $page->drawText(__('Order Date'), $x + 200, $this->y - 10, 'UTF-8');
        $page->drawText(__('Status'), $x + 340, $this->y - 10, 'UTF-8');
        $page->drawText(__('Order Total'), $x + 450, $this->y - 10, 'UTF-8');

        $lineY = $height - 120; 
        $page->drawLine($x, $lineY, $width - $x, $lineY);

    }

    protected function drawBorder($page)
    {
        $page->drawRectangle(10, $this->y -730, $page->getWidth()-10, $this->y + 90, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
       
        $pdf = new Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0];
        $style = new Zend_Pdf_Style();
        $style->setLineColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 12);
        $page->setStyle($style);
        $width = $page->getWidth();
        $height = $page->getHeight();
        $x = 30;
        $this->y = $height - 100;

        $this->drawHeader($page, $font, $style, $height, $width);

        $style->setFont($font, 16);
        $page->setStyle($style);

        $this->drawHeaderColumn($page, $font, $style, $x, $height, $width);
        $this->drawBorder($page, $width, $height);

        // $endDate = date('Y-m-d H:i:s');
        // $startDate = date('Y-m-d H:i:s', strtotime('-1 day'));

        $startDate = date('Y-m-d 00:00:00'); 
        $endDate = date('Y-m-d 23:59:59'); 

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addAttributeToFilter('created_at', ['from' => $startDate, 'to' => $endDate]);
        $orderCollection->setPageSize(100); 
        $orderCollection->setCurPage(1);


    if ($orderCollection->getSize() == 0) {
        $style->setFont($font, 14);
        $page->setStyle($style);
        $noRecordMessage = __('No Record Found');
        $page->drawText($noRecordMessage, ($width - 100) / 2, $this->y - 50, 'UTF-8');
        // $style->setFont($font, 10);
        // $page->setStyle($style);
        // $this->y -= 10; 
    }
    else
    {
        $recordCount = 0;
        foreach ($orderCollection as $order) {
            if ($recordCount % 10 == 0 && $recordCount != 0) {
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                $pdf->pages[] = $page;
                $this->drawHeader($page, $font, $style, $height, $width);
                $page->setStyle($style);
                $this->y = $height - 100;
                $this->drawHeaderColumn($page, $font, $style, $x, $height, $width);
                $this->drawBorder($page);

            }

            $this->y -= 45;

            $page->drawText($order->getIncrementId(), $x + 5, $this->y, 'UTF-8');
            $page->drawText($order->getCustomerFirstname().' '.$order->getCustomerLastname(), $x + 90, $this->y, 'UTF-8');
            $page->drawText($order->getCreatedAt(), $x + 200, $this->y, 'UTF-8');
            $page->drawText($order->getStatus(), $x + 340, $this->y, 'UTF-8');
            $page->drawText($order->getGrandTotal(), $x + 450, $this->y, 'UTF-8');

            $recordCount++;
        }
    }

        $fileName = 'daily_orders_' . date('Ymd') . '.pdf';

        $this->fileFactory->create(
            $fileName,
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );


        $output->writeln('Daily orders have been exported to ' . $fileName);

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
