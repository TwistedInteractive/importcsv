<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/">
        <h2>Import / Export CSV</h2>
        <ul class="importer-nav">
            <li>
                <a href="#" rel="regular" class="active">Regular import/export</a>
            </li>
            <xsl:if test="data/@multilanguage">
                <li>
                    <a href="#" rel="multilanguage">Multilingual field import/export</a>
                </li>
            </xsl:if>
        </ul>
        <div class="regular importer">
            <p>This is the default import/export tool. It allows you to import and export entire sections.</p>
            <fieldset class="left">
                <h3>Import CSV</h3>
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
                <h3>Export CSV</h3>
                <p>Please choose a section you wish to export to a CSV file:</p>
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
        </div>
        <xsl:if test="data/@multilanguage = 'yes'">
            <div class="multilanguage importer">
                <p>It appears that you have the <a href="https://github.com/6ui11em/multilingual_field">multilingual field extension</a> installed. You can export and import the content of these fields individualy here.</p>
                <p>
                    This function is great if you need to export content to send as a CSV file to a translation agency. The ID of the entry is also stored in the CSV file to assure a correct import.
                </p>
                <fieldset class="left">
                    <h3>Import CSV</h3>
                    <p>Select a CSV file to upload:</p>
                    <label>
                        <input name="csv-file-ml" type="file"/>
                    </label>
                    <p>
                        Please choose the field you wish to import into:
                    </p>
                    <label>
                        <select name="multilanguage-field-import" class="small">
                            <xsl:for-each select="data/multilanguage/field">
                                <xsl:sort select="." />
                                <option value="{@id}">
                                    <xsl:value-of select="."/>
                                </option>
                            </xsl:for-each>
                        </select>
                    </label>
                    <input name="multilanguage-import" type="submit" value="Import CSV"/>
                </fieldset>
                <fieldset class="right">
                    <h3>Export CSV</h3>
                    <p>
                        Please choose the field you wish to export to a CSV file:
                    </p>
                    <label>
                        <select name="multilanguage-field-export" class="small">
                            <xsl:for-each select="data/multilanguage/field">
                                <xsl:sort select="." />
                                <option value="{@id}">
                                    <xsl:value-of select="."/>
                                </option>
                            </xsl:for-each>
                        </select>
                    </label>
                    <input name="multilanguage-export" type="submit" value="Export CSV"/>
                </fieldset>
            </div>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>