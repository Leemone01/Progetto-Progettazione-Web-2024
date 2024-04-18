<?php
    //ESPRESSIONI REGOLARI
    define("REG_EXP_USERNAME", "/^[A-Za-z0-9]{2,16}$/");
    define("REG_EXP_EMAIL", "/^(.+)@([^\.].*)\.([a-z]{2,})$/");
    define("REG_EXP_PASSWORD", "/^\S{4,16}$/");
    define("REG_EXP_TITOLO", "/^.{1,50}$/");
    define("REG_EXP_DESCRIZIONE", "/^.{0,140}$/");
    define("REG_EXP_ID", "/^\d+$/");
    define("REG_EXP_COMMENTO", "/^.{1,140}$/");

    //DIMENSIONI MASSIME, ESTENSIONI E MIME-TYPE ACCETTATI
    define("IMG_MAX_SIZE", 1000000);
    define("TRACK_MAX_SIZE", 10000000);

    define("IMG_VALID_EXT", ["jpg", "png"]);
    define("TRACK_VALID_EXT", ["mp3"]);

    define("IMG_VALID_MIME", ["image/jpeg", "image/png"]);
    define("TRACK_VALID_MIME", ["audio/mpeg"]);

    //PATH DELLE IMMAGINI DI DEFAULT
    define("DEFAULT_PROFILE_IMG", "../imgs/profilo/default.png");
    define("DEFAULT_TRACK_IMG", "../imgs/brano/default.png");
    
    //FUNZIONI
    //Pulisce l'input ricevuto dall'utente che può presentare caratteri speciali (es. '<', '>'). Andrà chiamata ogni volta che tale input dovrà essere stampato.
    function pulisciStringa($stringa){
        return htmlspecialchars($stringa, ENT_QUOTES, 'UTF-8');
    }

?>


