<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Model_Response
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Model_Response  {

    /**
     *
     */
    const OK = 1;
    /**
     *
     */
    const MESSAGE = 2;
    /**
     *
     */
    const WARNING = 3;
    /**
     *
     */
    const FATAL = 4;

    /**
     * @var
     */
    public $method;
    /**
     * @var string
     */
    public $description;
    /**
     * @var null
     */
    public $data;
    /**
     * @var int
     */
    public $errorCode;
    /**
     * @var int
     */
    public $disposition;

    /**
     * @var int
     */
    private $_startTime;
    /**
     * @var int
     */
    private $_stopTime;

    /**
     * @var
     */
    private $_startMemory;
    /**
     * @var
     */
    private $_stopMemory;

    /**
     * @param $method
     * @param null $data
     * @param int $disposition
     * @param string $description
     * @param int $errorCode
     */
    public function __construct($method, $data = NULL, $disposition = self::OK, $description = '', $errorCode = 0) {
        $this->method = $method;
        $this->data = $data;
        $this->disposition = $disposition;
        $this->description = $description;
        $this->errorCode = $errorCode;

        $this->_startTime = 0;
        $this->_stopTime = 0;
    }

    /**
     * @return float
     */
    public function getElapsedTime() {
        return round($this->_stopTime - $this->_startTime, 10);
    }

    /**
     * @return mixed
     */
    public function getMemoryDif(){
        return $this->_stopMemory - $this->_startMemory;
    }

    /**
     *
     */
    public function startTimer() {
        $this->_startTime = microtime(true);
        $this->_startMemory = memory_get_usage();
    }

    /**
     *
     */
    public function stopTimer() {
        $this->_stopTime = microtime(true);
        $this->_stopMemory = memory_get_usage();
    }

}