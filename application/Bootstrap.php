<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * @var Zend_Log
     */
    protected $_logger;

    /**
     * Setup the logging
     */
    protected function _initLogging()
    {
        $this->bootstrap('frontController');
        $logger = new Zend_Log();

        $writer = 'production' == $this->getEnvironment() ?
            new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../data/logs/app.log') :
            new Zend_Log_Writer_Firebug();
        $logger->addWriter($writer);

        if ('production' == $this->getEnvironment()) {
            $filter = new Zend_Log_Filter_Priority(Zend_Log::CRIT);
            $logger->addFilter($filter);
        }

        $this->_logger = $logger;
        Zend_Registry::set('log', $logger);
    }

    /**
     * Setup locale
     */
    protected function _initLocale()
    {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        $locale = new Zend_Locale('en_US');
        Zend_Registry::set('Zend_Locale', $locale);
    }

    /**
     * Setup the database profiling
     */
    protected function _initDbProfiler()
    {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        if ('production' !== $this->getEnvironment()) {
            $this->bootstrap('db');
            $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
            $profiler->setEnabled('production' != $this->getEnvironment());
            $this->getPluginResource('db')->getDbAdapter()->setProfiler($profiler);
        }
    }

    /**
     * Add the config to the registry
     */
    protected function _initConfig()
    {
        $this->_logger->info('Bootstrap ' . __METHOD__);
        Zend_Registry::set('config', $this->getOptions());
    }

    /**
     * Setup the view
     */
    protected function _initViewSettings()
    {
        $this->_logger->info('Bootstrap ' . __METHOD__);

        $this->bootstrap('view');

        $this->_view = $this->getResource('view');

        // add global helpers
        $this->_view->addHelperPath(APPLICATION_PATH . '/views/helpers', 'Zend_View_Helper');

        // set encoding and doctype
        $this->_view->setEncoding('UTF-8');
        $this->_view->doctype(Zend_View_Helper_Doctype::XHTML1_TRANSITIONAL);

        // set the content type and language
        $this->_view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
        $this->_view->headMeta()->appendHttpEquiv('Content-Language', 'en-US');

        // set css links
        $this->_view->headLink()->appendStylesheet('/media/css/style.css');
        $this->_view->headLink()->appendStylesheet('/media/css/jquery.jgrowl.css');
        $this->_view->headLink(array(
            'rel' => 'stylesheet',
            'href' => 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/ui-darkness/jquery-ui.css'
        ));

        $this->view->headScript()->appendFile('/media/js/jquery-1.8.0.min.js');
        $this->view->headScript()->appendFile('/media/js/jquery.jgrowl.js');
        $this->view->headScript()->appendFile('/media/js/jquery-ui-1.8.23.custom.min.js');

        // setting the site in the title
        $this->_view->headTitle('Katzchen Wallet');

        // setting a separator string for segments:
        $this->_view->headTitle()->setSeparator(' - ');
    }

    /**
     * Init the db metadata and paginator caches
     */
    protected function _initDbCaches()
    {
        $this->_logger->info('Bootstrap ' . __METHOD__);
        if ('production' == $this->getEnvironment()) {
            // Metadata cache for Zend_Db_Table
            $frontendOptions = array(
                'automatic_serialization' => true
            );

            $cache = Zend_Cache::factory('Core',
                'Apc',
                $frontendOptions
            );
            Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
        }
    }
}
