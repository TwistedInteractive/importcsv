<?php

/*
 * Import Driver for type: subsectionmanager
 */

class ImportDriver_subsectionmanager extends ImportDriver_default
{

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_subsectionmanager()
    {
        $this->type = 'subsectionmanager';
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value   The value to import
     * @param  $entry_id    If a duplicate is found, an entry ID will be provided.
     * @return The data returned by the field object
     */
    public function import($value, $entry_id = null)
    {
        $data = $this->field->processRawFieldData(explode(',', $value), $this->field->__OK__);
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
        if(!is_array($data['relation_id']))
        {
            $data['relation_id'] = array($data['relation_id']);
        }
        return implode(',', $data['relation_id']);
    }

}
