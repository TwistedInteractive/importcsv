<?php

/*
 * Import Driver
 *
 * Each field should have a specific class for import-export functions.
 * When none is found, the default fallback is this class.
 */

class ImportDriver_default {

    protected $type;
    protected $field;

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_default()
    {
        $this->type = 'default';
    }

    /**
     * Set a reference to the field object.
     * @param  $field   The field
     * @return void
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * Get the type of the driver
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value       The value to import
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
     * @param  $entry_id    The ID of the entry that is exported
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export($data, $entry_id = null)
    {
        if(isset($data['value']))
        {
            return $data['value'];
        } else {
            return '';
        }
    }

}
