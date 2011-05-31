var totalEntries;
var currentEntry;
var sectionID;
var uniqueAction;
var uniqueField;
var importURL;
var startTime;

jQuery(function($){
    // Window resize function (for adjusting the height of the console):
    $(window).resize(function(){
        $("div.console").height($(window).height() - 250);
    }).resize();

    put('Initializing...');

    totalEntries = getVar('total-entries');
    sectionID    = getVar('section-id');
    uniqueAction = getVar('unique-action');
    uniqueField  = getVar('unique-field');
    importURL    = getVar('import-url');

    put('Start import of ' + totalEntries + ' entries; section ID: ' + sectionID + '; unique action: ' + uniqueAction);

    startTime = new Date();

    if(totalEntries > 0)
    {
        importRow(0);
    }
});

/**
 * Import the current row.
 * @param nr    The number of the row to import
 */
function importRow(nr)
{
    currentEntry = nr;

    var csvRow = jQuery("var.csv-" + nr);
    var fields = {};
    jQuery("var", csvRow).each(function(){
        fields['field-' + jQuery(this).attr("field")] = jQuery(this).text();
    });
    fields.ajax = 1;
    fields['unique-action'] = uniqueAction;
    fields['unique-field'] = uniqueField;
    fields['section-id'] = sectionID;
    
    jQuery.ajax({
        url: importURL,
        async: true,
        type: 'post',
        cache: false,
        data: fields,
        success: function(data, textStatus){
            c = data.substring(0, 4) == '[OK]' ? null : 'error';
            put('Import entry ' + (currentEntry + 1) + ': ' + data, c);
            jQuery("div.progress div.bar").css({width: ((currentEntry / totalEntries) * 100) + '%'});
            elapsedTime = new Date();
            ms = elapsedTime.getTime() - startTime.getTime();
            e = time(ms);
            // eta = time(1 - (currentEntry / totalEntries) * ms);
            p = (currentEntry / totalEntries);
            eta = time((ms * (1/p)) - ms);

            jQuery("div.progress div.bar").text('Elapsed time: ' + e + ' / Estimated time left: ' + eta);

            // Check if the next entry should be imported:
            if(currentEntry < totalEntries - 1)
            {
                importRow(currentEntry + 1);
            } else {
                jQuery("div.progress div.bar").css({width: '100%'}).text('Import completed!');
                put('Import completed!');
            }
            jQuery("div.console").attr({ scrollTop: jQuery("div.console").attr("scrollHeight") });
        }
    });
}

/**
 * Get a variable from the HTML code
 * @param name  The name of the variable
 */
function getVar(name)
{
    return jQuery("var." + name).text();
}

/**
 * Put a message in the console
 * @param str   The content
 */
function put(str, cls)
{
    c = cls == null ? '' : ' class="' + cls + '"';
    jQuery("div.console").append('<span'+c+'>' + str + '</span>');
}


function two(x) {return ((x>9)?"":"0")+x}

function time(ms) {
    var sec = Math.floor(ms/1000);
    var min = Math.floor(sec/60);
    sec = sec % 60;
    t = two(sec);

    var hr = Math.floor(min/60);
    min = min % 60;
    t = two(min) + ":" + t;
    return t;
}