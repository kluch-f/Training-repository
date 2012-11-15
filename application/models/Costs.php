<?php

class Application_Model_Costs
{
    protected $_id;
    protected $_amount;
    protected $_place;
    protected $_comment;
    protected $_createdAt;

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid costs property');
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid costs property');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setComment($comment)
    {
        $this->_comment = $comment;
        return $this;
    }

    public function getComment()
    {
        return $this->_comment;
    }

    public function setAmount($amount)
    {
        $this->_amount = $amount;
        return $this;
    }

    public function getAmount()
    {
        return $this->_amount;
    }

    public function setPlace($place)
    {
        $this->_place = $place;
        return $this;
    }

    public function getPlace()
    {
        return $this->_place;
    }

    public function setCreatedAt($ts)
    {
        $this->_createdAt = $ts;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    /**
     * Retrieve instance options as array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'amount' => $this->getAmount(),
            'place' => $this->getPlace(),
            'comment' => $this->getComment(),
            'created_at' => $this->getCreatedAt(),
        );
    }

}
