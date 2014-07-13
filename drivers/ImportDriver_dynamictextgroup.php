<?php

/*
 * Import Driver for type: dynamictextgroup
 */

class ImportDriver_dynamictextgroup extends ImportDriver_default {

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_dynamictextgroup()
    {
        $this->type = 'dynamictextgroup';
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value   The value to import
     * @param  $entry_id    If a duplicate is found, an entry ID will be provided.
     * @return The data returned by the field object
     */
    public function import($value, $entry_id = null)
    {
        $message = '';
        $data = $this->field->processRawFieldData(trim($value), Field::__OK__, $message, false, $entry_id);
        return $data;
    }

    /**
     * Process the data so it can be exported to a CSV
     * @param  $data        The data as provided by the entry
     * @param  $entry_id    The ID of the entry that is exported
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export($data, $entry_id = null)
    {
        if(!is_null($entry_id))
        {
            $row = array();

            // Get all the data matched with the key/val
            foreach($data as $col => $values) {
                if(is_array($values)) foreach($values as $key => $val) {
                    $row[$key][] = $col . ":" . $val;
                }
                else {
                    $row[$col][] = $values;
                }
            }

            // Implode the multiple pieces of data
            foreach($row as &$r) {
                $r = implode($r, '|');
            }

            return implode($row, ',');
        }

        return '';
    }

}
