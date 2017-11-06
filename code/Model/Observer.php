<?php

class MageProfis_SecureAdmin_Model_Observer
extends Mage_Core_Model_Abstract
{
    public function onLogin($event)
    {
        $action = $event->getControllerAction();
        /* @var $action Mage_Adminhtml_IndexController */
        $action->getResponse()->setHeader('X-Robots-Tag', 'noindex, nofollow', true);
    }
}