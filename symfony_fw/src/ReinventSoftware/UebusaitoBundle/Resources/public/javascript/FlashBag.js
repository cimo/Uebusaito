// Version 1.0.0

var flashBag = new FlashBag();

function FlashBag() {
    // Vars
    var self = this;
    
    var element = null;
    var message = "";
    
    // Properties
    self.setElement = function(value) {
        element = value;
    }
    
    self.setMessage = function(value) {
        message = value;
    }
    
    // Functions public
    self.show = function() {
        var snackbarDataObj = {
            message: message,
            actionText: "Undo",
            actionHandler: function() {}
        };
        
        element.show(snackbarDataObj);
    };
    
    self.sessionActivity = function() {
        if ($("#flashBag").find(".content").html() !== undefined && $("#flashBag").find(".content").html().trim() === "" && window.session.userActivity !== "") {
            var snackbarDataObj = {
                message: message,
                actionText: "Undo",
                actionHandler: function() {}
            };

            element.show(snackbarDataObj);
        }
    };
    
    // Functions private
}