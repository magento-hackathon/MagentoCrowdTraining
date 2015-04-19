<?php
/**
 * Schema Validator for enumerable data for attribute values of "Yes" Or "No"
 *
 * @method getEnumerable()
 * @method getAttributeCode()
 *
 * @author  Joel Hart   @mediotype
 */
class  Mediotype_Core_Helper_Mschema_Enumeration_Attribute_YesNo extends Mediotype_Core_Helper_Mschema_Enumeration_Attribute{

    protected $useUcwords = true;

    protected $enumerable = array(
        "Yes",
        "No",
        "0",
        "1",
        0,
        1,
        ""
    );

    public function evaluateEnumerable($validationObject, &$data)
    {
        $data = trim($data);

        if(empty($data)){
            $data = null;
            return true;
        }

        if(is_numeric($data)){
            if((int)$data == 1){
                $data = "Yes";
                return true;
            }
            if((int)$data == 0){
                $data = "No";
                return true;
            }
        }

        if(is_string($data)) {
            $helper = Mage::helper( 'mediotype_core/magento_attribute' );
            $att    = $helper->getAttributeByCode( $this->getAttributeCode() );
            $source = $att->getSource();
            if ( is_a( $source, 'Mage_Eav_Model_Entity_Attribute_Source_Boolean' ) ) {
                if(strtolower($data) == "yes"){
                    $data = "Yes";
                    return true;
                }
                if(strtolower($data) == "no"){
                    $data = "No";
                    return true;
                }
            }
        }

        return parent::evaluateEnumerable($validationObject, $data);
    }

}