function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return false;
}

function refreshMode() {
    if (getCookie("dark-edition") == "true") {
        document.getElementById("body").setAttribute("class", "dark-edition");
        document.getElementById("mode-button").innerHTML = "wb_sunny";

    } else {
        document.getElementById("body").setAttribute("class", "");
        document.getElementById("mode-button").innerHTML = "nights_stay";
    }
}

function changeMode() {
    setCookie("dark-edition", Boolean(!getCookie("dark-edition")), 1000);
    refreshMode();
}