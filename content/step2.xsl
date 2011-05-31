<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/">
        <fieldset>

            <h2>Select corresponding field</h2>
            <p>Please select the corresponding field which should be used to populate the entry with:</p>
            <table class="import">
                <tr>
                    <th>
                        CSV Key
                    </th>
                    <th>

                    </th>
                    <th>
                        Field name
                    </th>
                    <th class="unique">
                        <input type="radio" name="unique-field" value="no" checked="checked">Not unique</input>
                    </th>
                </tr>
                <xsl:for-each select="data/csv/key">
                    <xsl:variable name="key" select="."/>
                    <tr>
                        <td>
                            <xsl:value-of select="."/>
                        </td>
                        <td>→</td>
                        <td>
                            <select name="field-{position()-1}" class="small">
                                <option value="0" class="dont-use">Don't use</option>
                                <xsl:for-each select="/data/fields/field">
                                    <option value="{@id}">
                                        <xsl:if test="$key = .">
                                            <xsl:attribute name="selected">selected</xsl:attribute>
                                        </xsl:if>
                                        <xsl:value-of select="."/>
                                    </option>
                                </xsl:for-each>
                            </select>
                        </td>
                        <td>
                            <input type="radio" name="unique-field" value="{position()-1}">Unique</input>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>
            <p>To import fields of the type<em>'upload field'</em>, make sure the filename used in your CSV is the same
                as the file you wish to import. Also, the file you wish to import should already be placed manually in
                the correct folder (which is the folder you picked as destination folder for the field).
            </p>
            <p>When a field is marked as 'unique' special rules will apply if an entry with one ore more unique values
                already exists.
            </p>
            <label>With unique fields:
                <select name="unique-action" class="small">
                    <option value="default">Add new entry anyway (default)</option>
                    <option value="update">Update existing value</option>
                    <option value="ignore">Do nothing</option>
                </select>
            </label>
            <input type="hidden" name="section" value="{data/@section-id}"/>
            <p>
                <!--Please double-check everything. Clicking on 'next step' will start an import simulation. Nothing yet will be imported.-->
                Please double-check everything. Clicking on 'next step' will start the import process.
            </p>
            <h3>Please note</h3>
            <p>
                The use of this software is at your own risk. The author of this extension is under no condition responsible for any
                unexpected results of this extension. This software is licenced under the <a href="http://en.wikipedia.org/wiki/MIT_License" target="_blank">MIT Licence</a>.

            </p>
            <input name="import-step-3" type="submit" value="Next Step"/>
        </fieldset>
    </xsl:template>

</xsl:stylesheet>