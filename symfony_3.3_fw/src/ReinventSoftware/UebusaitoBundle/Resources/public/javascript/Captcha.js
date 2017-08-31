// Version 1.0.0

/* global ajax */

var captcha = new Captcha();

function Captcha() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.reload = function() {
        $(".captcha").find(".icon").on("click", "", function(event) {
            self.image();
        });
    };
    
    self.image = function() {
        ajax.send(
            true,
            false,
            window.url.index,
            "post",
            {
                'event': "captchaImage"
            },
            "json",
            false,
            null,
            function(xhr) {
                ajax.reply(xhr, "");

                if (xhr.response.captchaImage !== undefined)
                    $(".captcha").find("img").prop("src", "data:image/png;base64," + xhr.response.captchaImage);
            },
            null,
            null
        );
    };
    
    // Functions private
}