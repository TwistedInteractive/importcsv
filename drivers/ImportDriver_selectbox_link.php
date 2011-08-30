<?php

/*
 * Import Driver for type: selectbox_link
 */

class ImportDriver_selectbox_link extends ImportDriver_default
{

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_selectbox_link()
    {
        $this->type = 'selectbox_link';
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value   The value to import
     * @param  $entry_id    If a duplicate is found, an entry ID will be provided.
     * @return The data returned by the field object
     */
    public function import($value, $entry_id = null)
    {
        // Import selectbox link:
        // Get the correct ID of the related fields
        $related_field = Symphony::Database()->fetchVar('related_field_id', 0, 'SELECT `related_field_id` FROM `tbl_fields_selectbox_link` WHERE `field_id` = ' . $this->field->get('id'));
        $data = explode(',', $value);
        $related_ids = array('relation_id'=>array());
        foreach ($data as $relationValue)
        {
            $related_ids['relation_id'][] = Symphony::Database()->fetchVar('entry_id', 0, 'SELECT `entry_id` FROM `tbl_entries_data_' . $related_field . '` WHERE `value` = \'' . trim($relationValue) . '\';');
        }
        return $related_ids;
    }

    /**
     * Process the data so it can be exported to a CSV
     * @param  $data    The data as provided by the entry
     * @param  $entry_id    The ID of the entry that is exported
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export($data, $entry_id = null)
    {
        // Get the correct values of the related field
        $related_field = Symphony::Database()->fetchVar('related_field_id', 0, 'SELECT `related_field_id` FROM `tbl_fields_selectbox_link` WHERE `field_id` = ' . $this->field->get('id'));
        if (!is_array($data['relation_id'])) {
            $data['relation_id'] = array($data['relation_id']);
        }
        $related_values = array();
        foreach ($data['relation_id'] as $relation_id)
        {
            if(!empty($relation_id))
            {
                $row = Symphony::Database()->fetchRow(0, 'SELECT * FROM `tbl_entries_data_' . $related_field . '` WHERE `entry_id` = ' . $relation_id . ';');
                if (isset($row['value'])) {
                    $related_values[] = trim($row['value']);
                } else {
                    // Fallback to empty value:
                    $related_values[] = '';
                }
            }
        }
        return implode(', ', $related_values);
    }

}
