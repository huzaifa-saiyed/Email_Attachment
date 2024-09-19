<?php

namespace Kitchen\SalesReport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Kitchen\SalesReport\Model\Mail\TransportBuilder;


class EmailSender extends AbstractHelper
{
    protected $inlineTranslation;
    protected $_transportBuilder;

    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        \Magento\Framework\Filesystem\Driver\File $reader

    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->reader = $reader;
        parent::__construct($context);
    }

    public function sendReportEmail()
    {

        $this->inlineTranslation->suspend();
        $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1];
        $templateVars = [];

        $filePath = '/var/www/html/procraft-phoenix/var/order_exports/2024-09-18.csv';
        $fileName = 'Order Summary.csv';


        $pdfContent = $this->reader->fileGetContents($filePath);
        

        if ($this->reader->isExists($filePath) && $this->reader->isReadable($filePath) && $pdfContent) {

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('kitchen_sales_report_report_group_daily_sales_report_template')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom([
                    'name' => 'Test',
                    'email' => 'mohammed.uzaifa@kitchen365.com'
                ])
                ->addTo('Mohammed.yamin@kitchen365.com')
                ->addAttachment($pdfContent, $fileName)
                ->getTransport();

            $transport->sendMessage();
        } else {

            throw new \Magento\Framework\Exception\LocalizedException(
                __('The attachment file is missing or not readable.')
            );
        }

        $this->inlineTranslation->resume();

        return;
    }



}
