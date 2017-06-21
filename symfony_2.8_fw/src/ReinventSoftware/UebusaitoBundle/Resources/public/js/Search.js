/* global ajax */

var search = new Search();

function Search() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        var table = new Table();
        table.init(window.url.searchResult, "#search_result", false);
        table.search(true);
        table.pagination(true);
        table.sort(true);
        
        $(".form_search_words").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                null,
                function(xhr) {
                    $("#search_button").dropdown("toggle");
                    
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined)
                        window.location.href = xhr.response.values.url;
                    else
                        ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(".form_search_words_button").on("click", "", function() {
            $(this).parents(".form_search_words").submit();
        });
    };
    
    // Functions private
}