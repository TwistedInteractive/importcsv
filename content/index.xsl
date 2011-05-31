<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/">
        <fieldset class="left">
            <h2>Import CSV</h2>
            <p>Select a CSV file to upload:</p>
            <label>
                <input name="csv-file" type="file"/>
            </label>
            <p>Select a section as target:</p>
            <label>
                <select name="section" class="small">
                    <xsl:for-each select="data/sections/section">
                        <option value="{@id}">
                            <xsl:value-of select="."/>
                        </option>
                    </xsl:for-each>
                </select>
            </label>
            <p>Don't worry, going to the next step won't import anything yet.</p>
            <input name="import-step-2" type="submit" value="Next step"/>
        </fieldset>
        <fieldset class="right">
            <h2>Export CSV</h2>
            <p>Please choose a section you wish to export to a CSV file.</p>
            <label>
                <select name="section-export" class="small">
                    <xsl:for-each select="data/sections/section">
                        <option value="{@id}">
                            <xsl:value-of select="."/>
                        </option>
                    </xsl:for-each>
                </select>
            </label>
            <input name="export" type="submit" value="Export CSV"/>
            <p>
                <br/>
                <strong>Tip:</strong>
                You can also create a direct link to export a certain section by creating a link like<code>
                /symphony/extension/importcsv/?export&amp;section-export=9</code>, where
                <code>section-export</code>
                is the ID of the section you wish to export. In combination with the
                <a href="https://github.com/makenosound/publish_shortcuts">publish shortcuts extension</a>
                this can be a great addition to your clients' site.
            </p>
        </fieldset>
    </xsl:template>

</xsl:stylesheet>