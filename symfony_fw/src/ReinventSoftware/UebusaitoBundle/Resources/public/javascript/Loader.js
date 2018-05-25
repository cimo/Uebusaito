// Version 1.0.0

var loader = new Loader();

function Loader() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public    
    self.show = function() {
        $(".loader").show();
    };
    
    self.hide = function() {
        $(".loader").hide();
    };
    
    // Functions private
}