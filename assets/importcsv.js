jQuery(function($){
    $("ul.importer-nav a").click(function(){
        $("ul.importer-nav a").removeClass("active");
        $(this).addClass("active");
        $("div.importer").hide();
        $("div."+$(this).attr("rel")).show();
        return false;
    });
});