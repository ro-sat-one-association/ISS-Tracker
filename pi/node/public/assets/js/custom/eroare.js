setInterval(verificaEroare, 100);

function verificaEroare(){
    if(document.getElementById("err_code") != null){
        if(document.getElementById("err_code").innerHTML == "Wrong NORAD"){
            document.getElementById("eroare").innerHTML = "<div class=\"alert alert-danger\" role=\"alert\">Codul NORAD este nevalid!</div>";
        } 
        if(document.getElementById("err_code").innerHTML == "Wrong Coordinates/Altitude"){
        
            document.getElementById("eroare").innerHTML = "<div class=\"alert alert-danger\" role=\"alert\">Coordonatele/altitudinea sunt nevalide!</div>";
        }
        if(document.getElementById("err_code").innerHTML == "No reported error"){
            document.getElementById("eroare").innerHTML = "";
        }
    }
}