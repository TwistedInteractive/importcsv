# Import / export CSV #

Version: 0.2
Release date: 2011-06-01
Author: Giel Berkers  
Website: http://www.gielberkers.com
E-mail: info@gielberkers.com

Import a CSV file to create new entries for a certain section, or export an existing section to a CSV file

## Import drivers ##

The import- and export-functions are provided by importdrivers. These are located in the folder `drivers`.
By default, the import/export-function looks for a driver called `ImportDriver_(fieldname)` to handle the
imports and export of a specific field.

For example, for select-fields the `ImportDriver_select` is used and for upload-fields the `ImportDriver_upload` is used.
If no exact driver can be found, it falls back to `ImportDriver_default` which is good for most fields (as it simply relies
on a `value`-field in your database table.

## Default drivers ##

Default drivers are packaged for:

- Frontend Member Manager Password (to prevent double MD5-ing)
- Select (to provide selecting of multiple values)
- Selectbox link (to import and export while remaining human-readable values in your CSV)
- Status (to select the right value from the dedicated statusses-table)
- Subsection Manager (to keep the multiple ID's intact)
- Upload (to import files with CSV)

## Write your own driver ##

Do you have an exotic field where the default driver just isn't enough? Then you should write your own driver.
Don't worry! It's not that difficult. Just look at the default driver and at the pre-packaged drivers installed with the extension
to get an idea about how it works.

Most important to remember are the functions `import($value, $entry_id)` and `export($data, $entry_id)`.

### import($value, $entry_id) ###

The import function processes the value fetched from the CSV-file and prepares it for storing into the entry. Sometimes some
pre-processing must be done to store the entry correct. For example, look at the select-driver, which breaks the value into an array
so it gets sent to the entry. Or the upload-driver which loads some meta-data to store in the entry. The function should return
a `$data`-parameter. This parameter is used by the extension to provide the entry with data: `$entry->setData($fieldID, $data);`.

### export($data, $entry_id) ###

The export function processes the data fetch from the entry by the `$entry->getData($field->get('id'));`-function. It is the raw
data that is provided by Symphony. Here you can do some post-processing to alter the data before it gets stored in the CSV. For example:
the select-driver stores multiple values as a single string, combining them with a comma. And the selectbox-link driver loads the human-readable
value of the item to store in the CSV. The function should return a `$value`-parameter. This parameter is used by the extension to
be placed in the CSV file.

### Make this extension better! ###

Did you write a driver for a field? Share it with us! It will only make this extension better and stronger!