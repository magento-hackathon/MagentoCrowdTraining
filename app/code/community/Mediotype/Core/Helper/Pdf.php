<?php

class Mediotype_Core_Helper_Pdf extends Mage_Core_Helper_Abstract
{

    /**
     * @param array|string $handles
     * @return Mage_Core_Model_Layout
     * @throws Mage_Core_Exception
     */
    public function loadLayoutByHandle($handles)
    {
        if (is_string($handles)) {
            $handles = array('default', $handles);
        }

        /** @var  Mage_Core_Model_Layout $layout */
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load($handles);
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout;
    }

    /**
     * @param $html
     * @param $filename
     * @param bool $attachFile
     * @param string $orientation
     * @param bool $saveFile
     * @param string $pageSize
     *
     */
    public function htmlToPdf($html, $filename, $attachFile = true, $orientation = 'landscape', $saveFile = false, $pageSize = 'letter')
    {
        require_once('dompdf/dompdf_config.inc.php');

        define(DOMPDF_ENABLE_REMOTE, true);
        $dompdf = new DOMPDF();
        $dompdf->set_option('enable_remote', true);
        $dompdf->load_html($html);
        $dompdf->set_paper($pageSize, $orientation);

        $dompdf->render();

        if (!$saveFile) {
            $dompdf->stream($filename, array("Attachment" => $attachFile));
        } else {
            $ioHandler = fopen($filename, 'w+');
            fwrite($ioHandler, $dompdf->output());
            fclose($ioHandler);
        }
    }
}