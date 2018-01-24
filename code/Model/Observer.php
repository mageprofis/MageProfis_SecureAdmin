<?php

class MageProfis_SecureAdmin_Model_Observer extends Mage_Core_Model_Abstract
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

        //IP Whitelist OR Auth
        $auth = $this->isAuthActive();
        if ($auth && isset($_SERVER['PHP_AUTH_USER']))
        {
            if ($this->checkAuth())
            {
                $auth = false;
            } else
            {
                $auth = true;
            }
        }

        //ip whitelist
        if ($this->isIpWhitelistActive())
        {
            if ($this->checkIpWhitelist())
            {
                $auth = false;
            }
        }

        //return auth
        if ($auth)
        {
            $action->getResponse()->setHeader('WWW-Authenticate', 'Basic realm="SecureAdmin"', true);
            $action->getResponse()->setHttpResponseCode(401);
            $action->getResponse()->sendResponse();
            echo 'Auth!';
            exit;
        }
    }

    public function isIpWhitelistActive()
    {
        return file_exists($this->getIpWhitelistFilePath());
    }

    public function getIpWhitelistFilePath()
    {
        return Mage::getBaseDir('base') . DS . 'secureadmin_ip.txt';
    }

    public function checkIpWhitelist()
    {
        $path = $this->getIpWhitelistFilePath();
        $content = file_get_contents($path);
        $ips = explode("\n", $content);
        
        foreach ($ips as $ip)
        {
            if (trim($ip) === $_SERVER['REMOTE_ADDR'])
            {
                return true;
            }
        }

        return false;
    }

    public function isAuthActive()
    {
        return file_exists($this->getAuthFilePath());
    }

    public function getAuthFilePath()
    {
        return Mage::getBaseDir('base') . DS . 'secureadmin_auth.txt';
    }

    public function checkAuth()
    {
        $path = $this->getAuthFilePath();
        $content = file_get_contents($path);
        $lines = explode("\n", $content);

        foreach ($lines as $line)
        {
            $data = false;
            $data = explode(":", trim($line));

            if ($_SERVER['PHP_AUTH_USER'] && $_SERVER['PHP_AUTH_PW'])
            {
                if ($data[0] === $_SERVER['PHP_AUTH_USER'] && $data[1] === $_SERVER['PHP_AUTH_PW'])
                {
                    return true;
                }
            }
        }

        return false;
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
            $html = str_replace('<head>', '<head>' . "\n" . '    <meta name="robots" content="noindex, nofollow" />', $html);
            $transport->setHtml($html);
        }
    }

    protected function _getFullActionName()
    {
        return Mage::app()->getRequest()->getRouteName() . '_' .
                Mage::app()->getRequest()->getControllerName() . '_' .
                Mage::app()->getRequest()->getActionName()
        ;
    }

}
