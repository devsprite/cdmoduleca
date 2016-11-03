$( document ).ready(function() {
    setInterval(compteur, 500);
    function compteur(){
        $appels = $.cookie('appelKeyyo');
        $('#compteurAppels').text($appels);
    }

});