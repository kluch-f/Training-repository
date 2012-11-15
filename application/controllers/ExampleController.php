<?php

class ExampleController extends Zend_Controller_Action
{
//    public function indexAction()
//    {
//
//        $id = $this->getRequest()->getParam('id');
//        $this->view->id = $id;
//        $this->view->data = "Hello world";
//    }

    /**
     * Retrieve data for index.phtml
     */
    public function indexAction()
    {
        $costsMapper = new Application_Model_CostsMapper();
        $date = $this->getRequest()->getParam('date')
            ? new Zend_Date($this->getRequest()->getParam('date'), 'yyyy-MM')
            : Zend_Date::now();

        $this->view->data = $costsMapper->fetchAll($date);
        $this->view->months = $costsMapper->getAllMonths($date);
        $this->view->total = sprintf("%01.2f", $costsMapper->getMonthTotal($this->view->data));
    }

}
