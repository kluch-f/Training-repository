<?php

class IndexController extends Zend_Controller_Action
{
    /**
     * @var Application_Model_Costs
     */
    protected $_costs;

    /**
     * @var Application_Model_CostsMapper
     */
    protected $_costsMapper;

    /**
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Initialize costs instance by specified id
     *
     * @param int $id
     */
    protected function _initCosts($id)
    {
        $this->_costs = new Application_Model_Costs();
        $this->_costsMapper = new Application_Model_CostsMapper();

        $this->_costsMapper->find($id, $this->_costs);
    }

    /**
     * Initialize project configurations and client meta data
     */
    public function init()
    {
        if ($this->_getSession()->message) {
            $this->view->assign('message', $this->_getSession()->message);
            $this->_getSession()->message = null;
        }
    }

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

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $form = $this->_getForm();

        if ($id) {
            $this->_initCosts($id);

            if (!$this->_costs->getId()) {
                $this->_redirect('/');
            }

            $form = $this->_getForm($id);
            $form->populate($this->_costs->toArray());
        }

        $this->view->assign('form', $form);
    }

    public function saveAction()
    {
        $id = $this->getRequest()->getParam('id');

        if ($this->_getForm()->isValid($_POST)) {

            $costs = new Application_Model_Costs();
            $costsMapper = new Application_Model_CostsMapper();

            $costs
                ->setId($id)
                ->setPlace($this->getRequest()->getParam('place'))
                ->setAmount($this->getRequest()->getParam('amount'))
                ->setCreatedAt($this->getRequest()->getParam('created_at'))
                ->setComment($this->getRequest()->getParam('comment'));

            try {
                $costsMapper->save($costs);

                $this->_getSession()->message = $id
                    ? 'Record was successfully updated.'
                    : 'New record was successfully added.';
            } catch (Exception $e) {
                $this->_getSession()->message = 'Ooops. For some reasons we cannot perform SAVE action.';
            }


            $this->_redirect('/');
        } else {
            $this->_getSession()->message = 'some error occured.';
            $this->_redirect('index/edit' . ($id ? '/id/' . $id : ''));
        }
    }

    public function deleteAction()
    {
        $this->_initCosts($this->getRequest()->getParam('id'));

        if ($this->_costs->getId()) {
            if ($this->_costsMapper->delete($this->_costs)) {
                $this->_getSession()->message = 'Record was successfully deleted.';
            }
        }
        $this->_redirect('/');
    }

    /**
     * AJAX action for suggest
     */
    public function placesAction()
    {
        $costsMapper = new Application_Model_CostsMapper();
        echo $costsMapper
            ->getPlacesJson($this->getRequest()->getParam('term'));
        exit(0);
    }

    /**
     * Retrieve form instance for specified instance id
     *
     * @param null $id
     * @return Zend_Form
     */
    protected function _getForm($id = null)
    {
        $form = new Zend_Form;
        $dateValidator = new Zend_Validate_Date(array('format' => 'yyyy-MM-dd'));
        $formAction = (is_null($id)) ? '/index/save' : '/index/save/id/' . $id;

        return $form->setAction($formAction)
            ->setMethod('post')
            ->addElement('text', 'place', array(
                'id' => 'place',
                'label' => 'Place',
                'required' => true
            ))
            ->addElement('text', 'amount', array(
                'label' => 'Amount',
                'required' => true,
                //'validators' => array('digits')
            ))
            ->addElement('text', 'created_at', array(
                'id' => 'created_at',
                'label' => 'Date',
                'required' => true,
                'value' => Zend_Date::now()->toString('yyyy-MM-dd'),
                'validators' => array($dateValidator)
            ))
            ->addElement('textarea', 'comment', array('label' => 'Comment'))
            ->addElement('submit', 'save', array('label' => 'Save'));
    }

    /**
     * Retrieve session instance
     *
     * @return Zend_Session_Namespace
     */
    protected function _getSession()
    {
        if (is_null($this->_session)) {
            $this->_session = new Zend_Session_Namespace();
        }
        return $this->_session;
    }
}
