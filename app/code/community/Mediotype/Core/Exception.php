<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Exception
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Exception extends Mage_Core_Exception
{

    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        /** @var $debugger Mediotype_Core_Helper_Debugger */
        $debugger = Mage::helper('mediotype_core/debugger');

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

        $debugger->Write($stamp . " Mediotype_Core_Exception Thrown. ('" . $this->getMessage() . "')");
    }
}