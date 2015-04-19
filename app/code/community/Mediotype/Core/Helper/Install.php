<?php
/**
 * Helper for install processes or calls, for reference and making some install practices easier
 *
 * @author  Joel Hart    <joel@mediotype.com>
 */
class Mediotype_Core_Helper_Install extends Mediotype_Core_Helper_Abstract{


    /**
     * Remove a table by name from the database
     *
     * @param $tableName String a mysql table name
     */
    public function removeTable($tableName){

        $write = $this->getCoreWrite();
        $write->dropTable($tableName);

    }

    /**
     * Careful, this is a powerful function, passing the wrong string could remove un-intended config values
     * @param $like String xml path string maps to 'path' field in core_config_data table
     */
    public function truncateCoreConfigLike($like){

        $configCollection = Mage::getModel('core/config_data')
            ->getCollection()
            ->addFieldToFilter('path',
                array(
                    "like" => "%{$like}%"
                ))
            ->load();

        foreach($configCollection as $item){
            $item->delete();
        }

    }

    /**
     * Creates a new data install script from all values in a Magento collection
     *
     * TODO Finish this!!! its cool
     *
     * TODO add params array:
     * target module
     * target version | or next version increment auto detect
     * return string
     * output file
     * add use apostrophe or quote for array wrappers option
     *
     * TODO parse key and string agains array wrap value and escape where needed
     *
     * TODO add return
     *
     * @param $collectionReference  String  Magento model short tag
     * @param null $outputFile
     */
    public function createDataInstallScriptFromCollection($collectionReference, $outputFile = null){

        /** @var Mage_Core_Model_Resource_Db_Collection_Abstract $collection */
        $collection = Mage::getModel($collectionReference)->getCollection()->load();

        $idField = $collection->getIdFieldName(); // 'id'; or entity_id....
        $variableName = '$zipCode'; //array name in install script

        $jsonFields = array(
            "options_json",
            "columns_json",
            "conditions_json",
            "profile_state_json"
        );

            $output = $variableName . ' = array();';
            foreach($collection->getItems() as $item){

                $output .= "\n";
                $output .= $variableName . '[] = array(';

                foreach ($item->getData() as $key => $value){

                    if( is_array($value) ){
                        $output .= "\n" . "'{$key}' => array(\n";
                        foreach ( $value as $subKey => $subValue ){
                            $output .= "\n" . "'{$subKey}' => '{$subValue}',";
                        }
                        $output .= "\n), \n";
                    } else if (  in_array($key, $jsonFields) ) {
                        $jsonArray = json_decode($value);
                        foreach ( $jsonArray as $jsonKey => $jsonValue ){
                            $output .= "\n" . "'{$jsonKey}' => '{$jsonValue}',";

                        }
                    } else if ( $value == null ) {
                        //do nothing
                    } else if ( $key == "id" ){
                        //do nothing
                    } else if ( is_numeric($value) ) {
                        $output .= "\n" . "'{$key}' => {$value},";
                        //don't use quotes except for zip code or billing zip code or other number values.
                    } else {
                        $output .= "\n" . "'{$key}' => '{$value}',";
                        //normal output
                    }
                }

                $output .= "\n );\n";
            }

    }

    /**
     * Creates a new attribute install script based on an attribute in a Magento system
     *
     * TODO add params array:
     * target module
     * target version | or next version increment auto detect
     * return string ? 1 : 0
     * output file
     *
     * TODO add return
     *
     *
     * @param $attributeCode
     */
    public function createAttributeInstallScript($attributeCode){

    }

    /**
     * TODO everything
     *
     * @param $tableName
     */
    public function createTableInstallScriptFromTable($tableName){

        $coreResource = Mage::getModel('core/resource');
        $connection = Mage::getSingleton('core/resource')->getConnection('write');
        $description = $connection->describeTable($tableName);

        $create = array();
        foreach($description as $field){
            $create[] = $connection->getColumnCreateByDescribe($field);
        }


    }

}