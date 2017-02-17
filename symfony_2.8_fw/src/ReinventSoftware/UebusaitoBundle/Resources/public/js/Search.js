/* global ajax */

var search = new Search();

function Search() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $(".form_search").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                true,
                null,
                function(xhr) {
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined) {
                        
                    }
                    else
                        ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(".form_search_button").on("click", "", function() {
            $(this).parents(".form_search").submit();
        });
    };
    
    // Functions private
}