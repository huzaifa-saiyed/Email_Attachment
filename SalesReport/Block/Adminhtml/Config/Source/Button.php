<?php

namespace Kitchen\SalesReport\Block\Adminhtml\Config\Source;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * SendWelcomeEmailFromCSV
 */
class Button extends Field
{
    protected $_template = 'Kitchen_SalesReport::system/config/source/button.phtml';

    /**
     * @param Context $context,
     * @param array $data = []
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render
     */
    public function render(AbstractElement $element){
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * GetElementHtml
     */
    protected function _getElementHtml(AbstractElement $element){
        return $this->_toHtml();
    }

    /**
     * GetAjaxUrl
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('salesreport/report/sendreport');
    }
    
    /**
     * GetButtonHtml
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'send_report_btns',
                'label' => __('Send email'),
            ]
        );

        return $button->toHtml();
    }
}