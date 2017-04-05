/* global ajax */
var captcha = new Captcha();

function Captcha() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.reload = function() {
        $(".captcha").find(".icon").on("click", "", function(event) {
            ajax.send(
                window.url.index,
                "post",
                {
                    'event': "captchaReload"
                },
                true,
                null,
                function(xhr) {
                    ajax.reply(xhr, "");

                    if (xhr.response.captcha !== undefined)
                        $(".captcha").find("img").prop("src", "data:image/png;base64," + xhr.response.captcha);
                },
                null,
                null
            );
        });
    };
    
    // Functions private
}