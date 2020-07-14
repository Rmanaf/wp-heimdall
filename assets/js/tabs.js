(function () {

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

    function init() {

        hideTabs();

        inactiveTabs();

        addEvents();

        var tablinks = document.getElementsByClassName("tablinks");

        tablinks[0].click();

        document.dispatchEvent(new Event("heimdall--dashboard-tabs-loaded"));

    }

    docReady(init);


})();