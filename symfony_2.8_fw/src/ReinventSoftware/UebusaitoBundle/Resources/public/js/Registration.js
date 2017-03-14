/* global ajax */

var registration = new Registration();

function Registration() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $("#form_user_registration").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                true,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    };
    
    // Function private
}