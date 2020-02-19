"use strict";

/* global helper */

class MenuUser {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        $("#menu_user").find(".control_panel").on("click", "", (event) => {
            window.location.href = window.url.controlPanel;
        });
        $("#menu_user").find(".myPage").on("click", "", (event) => {
            window.location.href = `${window.url.root}/${window.session.languageTextCode}/1`;
        });
        $("#menu_user").find(".logout").on("click", "", (event) => {
            if (helper.readCookie(`${window.session.name}_login`) !== null)
                window.location.href = window.url.authenticationExitCheck;
            else
                window.location.href = window.url.root;
        });
        $("#menu_user").find(".login").on("click", "", (event) => {
            window.location.href = `${window.url.root}/${window.session.languageTextCode}/0/user_login`;
        });
        $("#menu_user").find(".registration").on("click", "", (event) => {
            window.location.href = `${window.url.root}/${window.session.languageTextCode}/3`;
        });
        $("#menu_user").find(".recover_password").on("click", "", (event) => {
            window.location.href = `${window.url.root}/${window.session.languageTextCode}/4`;
        });
    }
    
    // Functions private
}