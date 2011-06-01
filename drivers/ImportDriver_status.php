<?php

/*
 * Import Driver for type: status
 */

class ImportDriver_status extends ImportDriver_default {

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_status()
    {
        $this->type = 'status';
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value   The value to import
     * @param  $entry_id    If a duplicate is found, an entry ID will be provided.
     * @return The data returned by the field object
     */
    public function import($value, $entry_id = null)
    {
        $data = $this->field->processRawFieldData($value, $this->field->__OK__, false, $entry_id);
        return $data;
    }

    /**
     * Process the data so it can be exported to a CSV
     * @param  $data    The data as provided by the entry
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export($data)
    {
        if(isset($data['value']))
        {
            return $data['value'];
        } else {
            return '';
        }
    }

}
