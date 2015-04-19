<?php

/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Helper_Mschema_Validator_Parseavp
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Helper_Mschema_Validator_Download extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{
    /**
     *  Note, this validator can optionally take a Colon after the path string, with a value of 'nodispersion'
     *
     * @example:    "image/storage/path:nodispersion" which will not add dispersion to the download location
     *
     * @param $validationObject
     * @param $data
     * @return bool|Mediotype_Core_Model_Response
     *
     */
    public function Validate($validationObject, &$data)
    {
        $response = new Mediotype_Core_Model_Response(__METHOD__);
        if ($this->CanRead($validationObject)) {

            if (is_null($data) || trim($data) == "") {
                $response->disposition = Mediotype_Core_Model_Response::OK;
                $response->description = "DOWNLOAD FIELD HAS NO CONTENTS, OK";
                return $response;
            }

                $targetPath = $this->getVOData($validationObject);
                $destinationPath =  Mage::getBaseDir('media') . DS . $targetPath;
                /*
                 * Check for config string after download path in the form of
                 * ":nodispersion"
                 */
                if (strpos($targetPath, ":")) {
                    $additionalConfig   = explode(":", $targetPath);
                    $destinationPath    = Mage::getBaseDir('media') . DS . $additionalConfig[0];
                    $targetPath         = $additionalConfig[0];
                }

                $destinationPath = trim($destinationPath);

            /*
             * Verify destination path is provided && consists of only alpha numeric characters, hyphen, underscore and no spaces
             */
            if (isset($destinationPath) == false || !preg_match('/^[^*?"<>|:]*$/', $destinationPath)) {
                $response->disposition = Mediotype_Core_Model_Response::FATAL;
                $response->description = "NO PATH PROVIDED AS VALUE TO KEY 'DOWNLOAD' VALIDATOR TYPE :: " . $destinationPath;
                return $response;
            }

            $parts = explode("/",$data);

            $finalFilename = end($parts);

            $dispretionPath = Varien_File_Uploader::getDispretionPath($finalFilename); // Acheneli.woff A/c/

            if (isset($additionalConfig) && array_search('nodispersion',$additionalConfig)) {
                $dispretionPath = "";
            }

            $fullFinalPath = $destinationPath . $dispretionPath . DS . $finalFilename;

            if(is_file($fullFinalPath)){
                $data = $dispretionPath . DS . $finalFilename;

                if(strpos('catalog/product/', $data)){
                    $data = str_replace('catalog/product/',"",$data);
                }

                $cdnHelper = Mage::helper('aoe_amazoncdn');  /** @var Aoe_AmazonCdn_Helper_Data $cdnHelper */
                if($cdnHelper->isEnabled()){
                    $cdnAdapter = $cdnHelper->getCdnAdapter(); /** @var Aoe_AmazonCdn_Model_Cdn_Adapter $cdnAdapter */
                    $cdnAdapter->save($fullFinalPath, $fullFinalPath);
                }

                $response->disposition = Mediotype_Core_Model_Response::OK;
                $response->description = "DOWNLOAD SKIPPED - FILE EXISTS";
                return $response;
            }

            $downloadResults = Mage::helper('mediotype_core/file')->downloadFile($data); // file:///einvite/assets/uploads/Fonts/WOFF/Acheneli.woff

            if($downloadResults == 404){
                Mage::log($data, NULL, 'missing-urls.log');
                $response->disposition = Mediotype_Core_Model_Response::FATAL;
                $response->description = "URL PROVIDED IS 404, $data";
                $response->data = $data;
                return $response;
            }

            $newFilename = Varien_File_Uploader::getCorrectFileName(
                $downloadResults['filename'] . "." . $downloadResults['extension']
            );

            $fullFinalPath = $destinationPath . $dispretionPath . DS . $newFilename;
            $data          = $dispretionPath . DS . $newFilename;
            Mage::log(array(
                "AGGREGATED FINAL VALUE" => $dispretionPath . DS . $newFilename,

            ));

            /** @var Varien_Io_File $io */
            $io = new Varien_Io_File();
            $io->setAllowCreateFolders(true);
            $io->createDestinationDir( $destinationPath . $dispretionPath );
            $io->mv($downloadResults['path'], $fullFinalPath);

            $cdnHelper = Mage::helper('aoe_amazoncdn');  /** @var Aoe_AmazonCdn_Helper_Data $cdnHelper */
            if($cdnHelper->isEnabled()){
                $cdnAdapter = $cdnHelper->getCdnAdapter(); /** @var Aoe_AmazonCdn_Model_Cdn_Adapter $cdnAdapter */
                $cdnAdapter->save($fullFinalPath, $fullFinalPath);
            }

            $response->disposition = Mediotype_Core_Model_Response::OK;
            $response->description = "DOWNLOAD SUCCESSFUL";
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return "download";
    }
}