<?php
class Mediotype_Core_Helper_Spreadsheet extends Mage_Core_Helper_Abstract
{
    public function alphaBase26ToIntBase10($columnDesignator)
    {
        $results = 0;
        $base = 26;
        $numbersPlace = 0;
        $placeValue = pow($base, $numbersPlace);
        $cursor = (strlen($columnDesignator)) - 1;

        for ($cursor = (strlen($columnDesignator)) - 1; $cursor > -1; $cursor--) {
            $placeValue = pow($base, $numbersPlace);
            $character = substr($columnDesignator, $cursor, 1);
            $characterValue = $this->alphaToDigit($character);
            $positionValue = ((int)$characterValue * $placeValue);
            $results += $positionValue;
            $numbersPlace++;
        }
        return $results;
    }

    // INCOMPLETE
    public function intBase10ToAlphaBase26($columnDesignator)
    {
        // CONVERT 1000 INTO alphaBase26
        // FIND THE HIGHEST PLACE VALUE
        $base = 26;
        $startingDigit = 0;
        $workValue = 0;
        while($workValue < $columnDesignator){

        }
        // 26 ^ 0    26 ^ 1     26 ^ 2    26 ^ 3
        // 1         26         676       17576
        // PUT A ONE IN THAT COLUMN AND SUBTRACT THE VALUE FROM THE STARTING VALUE
        // ?          ?         A
        //  1000 - 676 = 324
        // DIVIDE THE NEXT LOWEST PLACE VALUE INTO THE NEW VALUE (324) AND DROP THE THE REMAINDER
        // 324 /  26 = 12.46...
        // ADD 12 TO THAT PLACE
        // ?          L         A
        // SUBTRACT 12 * 26 FROM OUR WORK VARIABLE
        // 324 - 312 = 12
        // ADD THE REST TO THE ONES COLUMN
        // L          L         A
    }

    public function alphaToDigit($character)
    {
        $letterMap = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        return strpos($letterMap, $character) + 1;
    }

    public function digitToAlpha($integer)
    {
        $letterMap = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        return substr($letterMap, $integer - 1, 1);
    }

    /**
     * @param PHPExcel_Worksheet $excelSheet
     * @param $row
     * @param array $data
     * @param int $startColumn
     */
    public function setRowCellValuesFromArray($excelSheet, $data, &$row = 1, $startColumn = 0){
        foreach($data as $index => $value){
            $excelSheet->setCellValueByColumnAndRow($startColumn, $row, $value);
            $startColumn++;
        }
        $row++;
        return $excelSheet;
    }

}