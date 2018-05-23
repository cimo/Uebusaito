// Version 1.0.0

var loader = new Loader();

function Loader() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public    
    self.create = function(tag) {
        $(".loader").appendTo(tag);
    }
    
    // Functions private
}