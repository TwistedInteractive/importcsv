<?php

/*
 * Import Driver for type: select
 */

class ImportDriver_select extends ImportDriver_default {

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_select()
    {
        $this->type = 'select';
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value   The value to import
     * @return The data returned by the field object
     */
    public function import($value)
    {
        $values = explode(',', $value);
        $newValues = array();
        foreach($values as $value)
        {
            $newValues[] = trim($value);
        }
        $data = $this->field->processRawFieldData($newValues, $this->field->__OK__);
        return $data;
    }

    /**
     * Process the data so it can be exported to a CSV
     * @param  $data    The data as provided by the entry
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export($data)
    {
        if(is_array($data['value']))
        {
            return implode(', ', $data['value']);
        } else {
            return $data['value'];
        }
    }

}
