(function ($) {

    function hideTabs() {
        var tabcontent = document.getElementsByClassName("hmd-tabcontent");
        for (var i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
    }


    function inactiveTabs() {
        var tablinks = document.getElementsByClassName("tablinks");
        for (var i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
    }

    function openTab(evt, target) {

        let name = target.toLowerCase().trim();

        hideTabs();

        inactiveTabs();

        document.getElementById(target).style.display = "block";

        evt.currentTarget.className += " active";

        document.dispatchEvent(new Event("heimdall--dashboard-tab-changed"));

        document.dispatchEvent(new Event(`heimdall--dashboard-tab-is-${name}`));

    }


    function addEvents(){

        var tablinks = document.getElementsByClassName("tablinks");

        for (var i = 0; i < tablinks.length; i++) {
            tablinks[i].addEventListener("click" , function(event){
                openTab(event , event.currentTarget.dataset["target"])
            });
        }

    }

    function docReady(fn) {
        if (document.readyState === "complete" || document.readyState === "interactive") {
            setTimeout(fn, 1);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }

        
    }

    function updateLastTab(){
        var udata = {
            lastTab : 0
        }
        if(window.localStorage){
            let _key = 'heimdall_user_data';
            let _index = $(".hmd-tab > .active").index();
            let _localUdata = localStorage.getItem(_key);
            if(_localUdata){
                udata = JSON.parse(_localUdata);
            }
            udata.lastTab = _index;

            localStorage.setItem(_key , JSON.stringify(udata));
            
        }
        
    }

    function init() {

        var udata = {
            lastTab : 0
        }

        if(window.localStorage){
            let _localUdata = localStorage.getItem('heimdall_user_data');
            if(_localUdata){
                udata = JSON.parse(_localUdata);
            }
        }

        hideTabs();

        inactiveTabs();

        addEvents();

        var tablinks = document.getElementsByClassName("tablinks");

        tablinks[udata.lastTab].click();

        document.dispatchEvent(new Event("heimdall--dashboard-tabs-loaded"));

        document.addEventListener("heimdall--dashboard-tab-changed" , updateLastTab);

    }



    docReady(init);


})(jQuery);