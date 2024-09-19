<?php

namespace Kitchen\SalesReport\Model\Mail;

use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Kitchen\SalesReport\Model\Mail\MessageBuilderFactory;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    private $parts = [];
    private $messageBuilderFactory;

    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageBuilderFactory $messageBuilderFactory
    ) {
        $this->messageBuilderFactory = $messageBuilderFactory;
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory
        );
    }

    public function addAttachment(
        $body,
        $fileName,
        $mimeType = Mime::TYPE_OCTETSTREAM,
        $disposition = Mime::DISPOSITION_ATTACHMENT,
        $encoding = Mime::ENCODING_BASE64
    ) {
     
            $attachment = new Part($body);
            $attachment->encoding = $encoding;
            $attachment->type = $mimeType;
            $attachment->disposition = $disposition;
            $attachment->filename = $fileName;
            // echo "<pre>";
            // print_r($attachment);die;
            $this->parts[] = $attachment;
    

        return $this;
    }

    protected function prepareMessage()
    {
        parent::prepareMessage();

        $messageBuilder = $this->messageBuilderFactory->create();
        $this->message = $messageBuilder
            ->setOldMessage($this->message)
            ->setMessageParts($this->parts)
            ->build();

        return $this;
    }
}
