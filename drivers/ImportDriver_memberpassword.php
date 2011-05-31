<?php

/*
 * Import Driver for type: memberpassword
 */

class ImportDriver_memberpassword extends ImportDriver_default {

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_memberpassword()
    {
        $this->type = 'memberpassword';
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value   The value to import
     * @return The data returned by the field object
     */
    public function import($value)
    {
        $data = $this->field->processRawFieldData($value, $this->field->__OK__);
        // Reset the value, to prevent double md5:
        $data['password'] = $value;
        return $data;
    }

    /**
     * Process the data so it can be exported to a CSV
     * @param  $data    The data as provided by the entry
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export($data)
    {
        return $data['password'];
    }

}
