<?php
    session_start();
    include_once "dbaccess.php";
    include_once "utils.php";

    $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   //apro come prima cosa la connessione al database, così in caso di errore
    if(mysqli_connect_errno()){                                     //termino subito lo script (visto che non potrei caricare il brano)
        exit(mysqli_connect_error());
    }

    
    $sid;
    $titolo;
    $utente;
    $username;
    $immagineBrano;
    $pathBrano;             
    $descrizioneBrano;
    $numeroMiPiace;
    $numeroAscolti;
    $numeroCommenti;
    $branoRecuperato = false;          //true se i dati del brano sono stati recuperati dal database, false altrimenti
    $miPiaceUtente;
    $dataRilascio;
    $err = "";
    $msg = "";

    //Recupera i dettagli del brano il cui sid è passato come query string.
    function recuperaBrano(){
        global $sid;
        global $connection;
        global $titolo;
        global $utente;
        global $username;
        global $immagineBrano;
        global $pathBrano;
        global $numeroMiPiace;
        global $numeroAscolti;
        global $numeroCommenti;
        global $descrizioneBrano;
        global $branoRecuperato;
        global $dataRilascio;
        global $err;
                                                         
        if(!isset($_GET["sid"]) || !preg_match(REG_EXP_ID, $_GET["sid"])){      //prima verifico che il sid non sia assente e che sia un intero  
            $err = "SID assente o non valido.";
            return false;
        }

        $query = "SELECT * FROM brano INNER JOIN utente ON utente=uid WHERE sid=?";       //provo a recuperare i dati del brano, con info sull'utente che l'ha caricato
        $types = "i";
        $sid = $_GET["sid"];
        $statement = execute_prepared_statement($connection, $query, $types, $sid);
        if(!$statement){
            $err = "Errore nel recuperare i dati dell'utente";
            return false;
        }

        $result = mysqli_stmt_get_result($statement);
        if(mysqli_num_rows($result) === 0){
            $err = "SID non corrispondente ad alcun brano.";
            return false;
        }

        $branoRecuperato = true;                   //arrivati qui, i dati del brano sono stati recuperati
        $row = mysqli_fetch_assoc($result);
        
        $titolo = pulisciStringa($row["titolo"]);
        $utente = $row["utente"];
        $username = $row["username"];                       //salvo i dettagli del brano
        $immagineBrano = $row["immagineBrano"];
        $pathBrano = $row["pathBrano"];
        $descrizioneBrano = pulisciStringa($row["descrizioneBrano"]);
        $numeroMiPiace = $row["numeroMiPiace"];
        $numeroAscolti = $row["numeroAscolti"];
        $numeroCommenti = $row["numeroCommenti"];
        $dataRilascio = $row["dataRilascio"];

        mysqli_free_result($result);
        return true;
    }

    function recuperaMiPiaceUtente(){  //restituisce true se l'utente attuale ha messo mi piace, false se no o in caso di errore
        global $connection;

        if(!isset($_SESSION["uid"])){
            return false;
        }

        $query = "SELECT * FROM mipiace WHERE utente =" . $_SESSION["uid"] . " AND brano =?";
        $types = "i";
        $sid = $_GET["sid"];
        $statement = execute_prepared_statement($connection, $query, $types, $sid);
        if(!$statement){                                //in caso di errore
            return false;        
        }                       

        $result = mysqli_stmt_get_result($statement);
        if(mysqli_num_rows($result) === 0){             //se utente non ha messo mi piace
            mysqli_free_result($result);
            return false;
        }
        else{                                           //se utente ha messo mi piace
            mysqli_free_result($result);
            return true;
        }

    }

    function aggiornaAscolti(){
        global $connection;

        $query = "UPDATE brano set numeroAscolti = numeroAscolti + 1 WHERE sid=?";
        $types = "i";
        $sid = $_GET["sid"];
        $statement = execute_prepared_statement($connection, $query, $types, $sid);     //in caso di errore, non faccio nulla (non è così grave da dover essere gestito)
    }

    function recuperaCommenti(){
        global $connection;

        $query = "SELECT * FROM commento C inner join brano B ON C.brano = B.sid INNER JOIN utente U on C.utente = U.uid WHERE C.brano = ? ORDER BY C.dataCommento DESC";                            //provo a recuperare i dati del brano, con info sull'utente che l'ha caricato
        $types = "i";
        $sid = $_GET["sid"];
        $statement = execute_prepared_statement($connection, $query, $types, $sid);
        if(!$statement){
            echo "<span class='contenitore-errore'>Errore nel recuperare i commenti del brano.</span>";
            return;
        }

        $immagineMiniatura = (isset($_SESSION["uid"])) ? $_SESSION["immagine"] : DEFAULT_PROFILE_IMG;

        echo <<<EOD
            <div id="contenitore-aggiungi-commento">
                <div class="contenitore-immagine-miniatura">
                    <img src='$immagineMiniatura' class="immagine-miniatura" alt='Immagine del profilo'>
                </div>
                <div id="corpo-aggiungi-commento">
                    <textarea rows=2 maxlength='140' placeholder = "Aggiungi un commento (deve essere costituito da 1-140 caratteri)..." id='commento'></textarea>
                    <button type="button" id="aggiungi-commento">Aggiungi commento</button>
                </div>  
            </div>
        EOD;

        $result = mysqli_stmt_get_result($statement);
        while($row = mysqli_fetch_assoc($result)){
            $immagineMiniatura = $row["immagineUtente"];
            $username = $row["username"];
            $uid = $row["uid"];
            $contenuto = pulisciStringa($row["contenuto"]);
            $dataCommento = $row["dataCommento"];

            echo <<<EOD
                <div class="contenitore-commento">
                    <div class="contenitore-immagine-miniatura">
                        <img src='$immagineMiniatura' class="immagine-miniatura" alt='Immagine del profilo'>
                    </div>

                    <div>
                        <span class="header-commento">
                            <a href='profilo.php?uid=$uid'>$username</a>
                            <span>$dataCommento</span>
                        </span>
                        <p class='commento'>$contenuto</p>
                    </div>  
                </div>
            EOD;
        }
    }

    if(recuperaBrano()){    //recupero i dati del brano. Se vengono recuperati con successo,
        $miPiaceUtente = recuperaMiPiaceUtente();       //recupero anche se l'utente ha messo o meno mi piace al brano,
        aggiornaAscolti();                              //e aggiorno gli ascolti del brano
    }

?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Brano</title>
        <link rel="icon" href="../imgs/icona.ico"> 
        <link rel="stylesheet" href="../css/style.css">
        <script src="../js/brano.js"></script>
    </head>

    <body>
        <?php
            include_once "header.php";
        ?>
        <main id='main-pagina'>
            <?php if($branoRecuperato){ ?>            
                <div id='contenitore-dettagli'>
                    <div id='contenitore-immagine'>
                        <?php
                            echo "<img src='$immagineBrano' id='immagine-mostrata' alt='Immagine del brano'>";
                        ?>
                    </div>
                    <div id='dettagli'>
                        <span id="categoria-dettagli">Brano</span>
                        <div id="intestazione-dettagli">
                            <?php
                                echo "<span id='titolo-dettagli'>$titolo</span>";
                                if(isset($_SESSION["uid"]) && ($_SESSION["uid"] == $utente || $_SESSION["tipo"] === 0)){        //se utente è loggato ed il suo uid è uguale a quello di chi ha caricato il brano o è admin, può modificarlo
                                    echo "<a href='modifica_brano.php?sid=$sid' id='modifica'>Modifica brano</a>";
                                }
                            ?>
                        </div>
                        <div id="autore-dettagli">
                            <?php
                                echo "<a href='profilo.php?uid=$utente'>$username</a> $dataRilascio";
                            ?>
                        </div>
                        <p id="descrizione">
                            <?php
                                echo ($descrizioneBrano !== "") ? $descrizioneBrano : "Nessuna descrizione disponibile.";
                            ?>
                        </p>
                    </div>
                </div>

                <div id="contenitore-player">
                    <?php
                        echo <<<EOT
                                <audio controls>
                                    <source src='$pathBrano' type="audio/mpeg">
                                    Il tuo browser non supporta l'elemento audio.
                                </audio>
                        EOT;
                    ?>
                    <p id="statistiche">
                        <?php echo $numeroAscolti ?> ascolti, <span id="numero-mipiace"><?php echo $numeroMiPiace ?></span> mi piace, <span id="numero-commenti"><?php echo $numeroCommenti ?></span> commenti
                    </p>
                </div>
                
                <div id="contenitore-messaggio-brano">

                </div>

                <div id="contenitore-bottoni">
                    <button type="button" id="mipiace" <?php if($miPiaceUtente){echo "class='mipiace-aggiunto'";}?>>Mi piace</button>
                    <button type="button" id="mostra-commenti">Mostra commenti</button>
                </div>
                
                <!-- ulteriore contenitore per poter poi nascondere i commenti impostando la proprietà display: none-->
                <div id="contenitore-contenitore-commenti">
                    <div id="contenitore-commenti">
                        <?php
                            recuperaCommenti();
                        ?>
                    </div>
                </div>
            <?php }
                else {                                                           //se invece i dati dell'utente non sono stati recuperati,
                    echo "<span class='contenitore-errore'>$err</span>";         //mostro il messaggio di errore relativo
                }
                mysqli_close($connection);                                      //che abbia recuperato o meno i dati dell'utente, chiudo la connessione una volta che questa non serve più
            ?>
            
        </main>
        <?php
            include_once "footer.php";
        ?>
    </body>
</html>
