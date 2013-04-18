<?php

/*
 * Import Driver for type: referencelink
 */

class ImportDriver_referencelink extends ImportDriver_default
{

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_referencelink()
    {
        $this->type = 'referencelink';
    }

    /**
     * Returns related Field ID for this Reference Link
     * @todo This only handles a Reference Link that links to one section
     * @return integer
     */
    private function getRelatedField()
    {
        // Get the correct ID of the related fields
        $related_field = Symphony::Database()->fetchVar('related_field_id', 0, 'SELECT `related_field_id` FROM `tbl_fields_referencelink` WHERE `field_id` = ' . $this->field->get('id'));

        return $related_field;
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value   The value to import
     * @param  $entry_id    If a duplicate is found, an entry ID will be provided.
     * @return The data returned by the field object
     */
    public function import($value, $entry_id = null)
    {
        // Import reference link:
        $data = $this->field->processRawFieldData(explode(',', $value), $this->field->__OK__);

        $related_ids = array('relation_id'=>array());
        $related_field_id = $this->getRelatedField();
        foreach ($data['relation_id'] as $key => $relationValue)
        {
            $query = sprintf('
                SELECT `entry_id`
                FROM `tbl_entries_data_%d`
                WHERE `value` = "%s";
            ',
                $related_field_id,
                Symphony::Database()->cleanValue(trim($relationValue))
            );

            $related_ids['relation_id'][] = Symphony::Database()->fetchVar('entry_id', 0, sprintf('
                SELECT `entry_id`
                FROM `tbl_entries_data_%d`
                WHERE `value` = "%s";
            ',
                $related_field_id,
                Symphony::Database()->cleanValue(trim($relationValue))
            ));
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
        if (!is_array($data['relation_id'])) {
            $data['relation_id'] = array($data['relation_id']);
        }

        $related_values = array();
        $related_field_id = $this->getRelatedField();
        foreach ($data['relation_id'] as $relation_id)
        {
            if(!empty($relation_id))
            {
                $row = Symphony::Database()->fetchRow(0, 'SELECT * FROM `tbl_entries_data_' . $related_field_id . '` WHERE `entry_id` = ' . $relation_id . ';');
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

    /**
     * Scan the database for a specific value
     * @param  $value       The value to scan for
     * @return null|string  The ID of the entry found, or null if no match is found.
     */
    public function scanDatabase($value)
    {
        $related_field_id = $this->getRelatedField();
        $searchResult = Symphony::Database()->fetchVar('entry_id', 0, sprintf('
            SELECT `entry_id`
            FROM `tbl_entries_data_%d`
            WHERE `value` = "%s";
        ',
            $related_field_id,
            Symphony::Database()->cleanValue(trim($value))
        ));

        // If there is a matching result, it means the entry exists in the SBL section
        // Now check to see if there is another entry with this value in the current section
        if ($searchResult != false) {
            $existing = Symphony::Database()->fetchVar('entry_id', 0, sprintf('
                SELECT `entry_id`
                FROM `tbl_entries_data_%d`
                WHERE `relation_id` = %d;
            ',
                $this->field->get('id'),
                $searchResult
            ));

            if ($existing != false) {
                return $existing;
            }
            else {
                return null;
            }
        }
        else {
            return null;
        }
    }

}
