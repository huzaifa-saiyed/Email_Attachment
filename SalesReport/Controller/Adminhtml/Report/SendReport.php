<?php
namespace Kitchen\SalesReport\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class SendReport extends Action
{
    protected $emailSenderHelper;
    protected $customerRepository;

    public function __construct(
        Action\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Kitchen\SalesReport\Helper\EmailSender $emailSenderHelper
    ) {
        $this->emailSenderHelper = $emailSenderHelper;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Kitchen_SalesReport::send_sales_report');
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        
        try {
            $this->emailSenderHelper->sendReportEmail();
            $message = __('Email Sent Successfully');
            $this->messageManager->addSuccessMessage($message);
            
            return $resultJson->setData([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->messageManager->addErrorMessage($message);
            
            return $resultJson->setData([
                'success' => false,
                'message' => $message
            ]);
        }
    }
}
