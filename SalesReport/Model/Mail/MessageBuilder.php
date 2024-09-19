<?php

declare(strict_types=1);

namespace Kitchen\SalesReport\Model\Mail;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Message\MessageInterface;

class MessageBuilder
{
    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;

    /**
     * @var EmailMessageInterface|MessageInterface
     */
    private $oldMessage;

    /**
     * @var array
     */
    private $messageParts = [];

    public function __construct(
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory
    ) {
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory;
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory;
    }

    /**
     * @return EmailMessageInterface|MessageInterface
     * @throws LocalizedException
     */
    public function build()
    {
        return $this->buildUsingEmailMessageInterfaceFactory();
    }

    /**
     * @return EmailMessageInterface
     * @throws LocalizedException
     */
    private function buildUsingEmailMessageInterfaceFactory()
    {
        $parts = $this->oldMessage->getBody()->getParts();
        $parts = array_merge($parts, $this->messageParts);
        $messageData = [
            'body' => $this->mimeMessageInterfaceFactory->create(
                ['parts' => $parts]
            ),
            'from' => $this->oldMessage->getFrom(),
            'to' => $this->oldMessage->getTo(),
            'cc' => $this->oldMessage->getCc(),
            'subject' => $this->oldMessage->getSubject()
        ];
        $message = $this->emailMessageInterfaceFactory->create($messageData);

        return $message;
    }


    public function setOldMessage($oldMessage): MessageBuilder
    {
        $this->oldMessage = $oldMessage;

        return $this;
    }

    public function setMessageParts(array $messageParts): MessageBuilder
    {
        $this->messageParts = $messageParts;

        return $this;
    }
}
