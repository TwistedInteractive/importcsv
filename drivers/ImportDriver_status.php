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
        $data = $this->field->processRawFieldData(trim($value), $this->field->__OK__, false, $entry_id);
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
        if($entry_id != null)
        {
            return Symphony::Database()->fetchVar('status', 0, 'SELECT `status` FROM `tbl_fields_status_statusses` WHERE `field_id` = '.$this->field->get('id').' AND `entry_id` = '.$entry_id.' ORDER BY `date` DESC, `id` DESC;');
        }
        if(isset($data['value']))
        {
            return trim($data['value']);
        } else {
            return '';
        }
    }

}
