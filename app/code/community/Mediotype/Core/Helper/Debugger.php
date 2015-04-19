<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Helper_Debugger
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Helper_Debugger extends Mage_Core_Helper_Abstract
{
    /**
     * @var
     */
    static protected $logFilePath;
    /**
     * @var
     */
    static protected $IOHandler;

    /**
     * @var Bool    Debug Flag
     */
    static protected $enabled = false;

    /**
     * @var Bool    Debug Flag
     */
    static protected $recursionOutputEnabled = false;

    /**
     * @param $depth
     * @param $string
     */
    public function logRecursion($depth, $string)
    {
        if ($this->getRecursionLogEnabled()) {
            $marker = "";
            for ($i = 0; $i < ($depth * 2); $i++) {
                $marker = $marker . "*";
            }
            $this->Write("$depth ]$marker " . $string . "<br/>\r");
        }
    }

    /**
     * @param string $data
     */
    static public function log($data = '')
    {
        $output = array();
        if ($data instanceof Varien_Object) {
            $output['class'] = get_class($data);
            $output['data'] = array();
            foreach ($data->getData() as $index => $value) {
                $output['data'][$index]['type'] = gettype($value);
                is_object($value) ? $output['data'][$index]['class'] = get_class($value) : null;
                $output['data'][$index]['size'] = sizeof($value);
                !is_object($value) && !is_array($value) ? $output['data'][$index]['value'] = $value : null;
            }
        } elseif (is_object($data)) {
            $output['class'] = get_class($data);
            $output['data'] = array();
            foreach ($data as $index => $value) {
                $output['data'][$index]['type'] = gettype($value);
                is_object($value) ? $output['data'][$index]['class'] = get_class($value) : null;
                $output['data'][$index]['size'] = sizeof($value);
                !is_object($value) && !is_array($value) ? $output['data'][$index]['value'] = $value : null;
            }
        } elseif (is_array($data)) {
            $output['class'] = "Array";
            $output['data'] = array();
            foreach ($data as $index => $value) {
                $output['data'][$index]['type'] = gettype($value);
                is_object($value) ? $output['data'][$index]['class'] = get_class($value) : null;
                $output['data'][$index]['size'] = sizeof($value);
                !is_object($value) && !is_array($value) ? $output['data'][$index]['value'] = $value : null;
            }
        } else {
            $output = print_r($data, true);
        }

        $datetime = new DateTime("now", new DateTimeZone('America/Denver'));
        $stamp = "[T:" . $datetime->format('Y-m-d h:i:s A') . "]";
        try {
            $debug_backtrace = debug_backtrace();
            $first = array_shift($debug_backtrace);
            if (array_key_exists('file', $first)) {
                $stamp .= "[F:{$first['file']}]";
            }

            if (array_key_exists('line', $first)) {
                $stamp .= "[L:{$first['line']}]";
            }

        } catch (Exception $e) {

        }

        self::Write($stamp . print_r($output, true));
    }

    /**
     * @param $string
     */
    static public function Write($string)
    {
        if (self::getEnabled()) {
            self::_openStream();
            fseek(self::$IOHandler, 0, SEEK_END);
            fwrite(self::$IOHandler, "\n" . $string);
            self::_closeStream();
        }
    }

    /**
     *
     */
    static protected function _openStream()
    {
        if (self::$logFilePath == '') {
            self::$logFilePath = Mage::getBaseDir('var') . DS . 'log' . DS . 'Mediotype.log';
        }
        self::$IOHandler = file_exists(self::$logFilePath) ? fopen(self::$logFilePath, 'a') : fopen(self::$logFilePath, 'w+');
//        self::$_IOHandler = fopen(self::$_logFilePath, '+a');
    }

    /**
     *
     */
    static protected function _closeStream()
    {
        if (self::$IOHandler) {
            fclose(self::$IOHandler);
        }
    }

    /**
     * @return bool
     */
    static public function getEnabled()
    {
        return true;
        if (is_null(self::$enabled)) {
            return (bool)Mage::getStoreConfig('mediotype_general/debug_settings/logging_enabled');
        }

        return self::$enabled;
    }

    static public function getDebugLevel()
    {
        return (int)Mage::getStoreConfig('mediotype_general/debug_settings/logging_level');
    }

    /**
     * @return bool
     */
    static public function getRecursionLogEnabled()
    {
        if (is_null(self::$enabled)) {
            return (bool)Mage::getStoreConfig('mediotype_general/debug_settings/recursion_logging_enabled');
        }

        return self::$enabled;
    }

    static public function setEnabled($value){
        self::$enabled = $value;
    }



}
