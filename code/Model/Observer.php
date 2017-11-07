<?php

class MageProfis_SecureAdmin_Model_Observer
extends Mage_Core_Model_Abstract
{
    public function onLogin($event)
    {
        $action = $event->getControllerAction();
        /* @var $action Mage_Adminhtml_IndexController */
        $ua = Mage::helper('core/http')->getHttpUserAgent();
        if (MageProfis_SecureAdmin_Zend_Http_UserAgent_Bot::match($ua, $_SERVER))
        {
            $action->getResponse()->clearBody();
            $action->getResponse()->clearHeaders();
            $action->getResponse()->setHttpResponseCode(404);
            $action->getResponse()->setHeader('X-Robots-Tag', 'noindex, nofollow', true);
            echo 'Not Found';
            $action->getResponse()->sendResponse();
            exit;
        }
        $action->getResponse()->setHeader('X-Robots-Tag', 'noindex, nofollow', true);
    }

    public function onHtmlReplace($event)
    {
        $actions = array(
            'adminhtml_index_login',
            'adminhtml_index_forgotpassword',
            'adminhtml_index_resetPassword',
        );
        $block = $event->getBlock();
        if (in_array($this->_getFullActionName(), $actions) && $block->getNameInLayout() == 'root')
        {
            $transport = $event->getTransport();
            $html = $transport->getHtml();
            $html = str_replace('<head>', '<head>'."\n".'    <meta name="robots" content="noindex, nofollow" />', $html);
            $transport->setHtml($html);
        }
    }
    
    protected function _getFullActionName()
    {
        return Mage::app()->getRequest()->getRouteName() . '_' .
            Mage::app()->getRequest()->getControllerName() . '_'.
            Mage::app()->getRequest()->getActionName()
        ;
    }
}
