<?php

/*
 * Import Driver for type: metakeys
 */

class ImportDriver_metakeys extends ImportDriver_default {

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_metakeys()
    {
        $this->type = 'metakeys';
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
        $data = $this->field->prepareImportValue(trim($value), ImportableField::STRING_VALUE, $entry_id);
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
            if(!is_array($data['key_handle'])) {
                $data = array(
                    'key_handle' => array($data['key_handle']),
                    'key_value' => array($data['key_value']),
                    'value_handle' => array($data['value_handle']),
                    'value_value' => array($data['value_value'])
                );
            }

            $row = array();
            for($i = 0, $ii = count($data['key_handle']); $i < $ii; $i++) {
                $row[$i][] = $data['key_value'][$i] . ":" . $data['value_value'][$i];
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
