<?php

class FatModel
{
    protected $_model;
    
    protected $error;
    
    public function getError()
    {
        return $this->error;
    }
    
    public function __construct()
    {
        $this->_model = get_class($this);
        $this->error = '';
    }
    
    public function __destruct()
    {
    }
}
