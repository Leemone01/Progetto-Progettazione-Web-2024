<?php
    session_start();
    include_once "dbaccess.php";
    include_once "utils.php";

    $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   //apro come prima cosa la connessione al database, così in caso di errore
    if(mysqli_connect_errno()){                                     //termino subito lo script (visto che non potrei caricare il profilo dell'utente)
        exit(mysqli_connect_error());
    }


    $username; 
    $immagineUtente;             
    $descrizioneUtente;
    $utenteRecuperato = false;          //true se i dati dell'utente sono stati recuperati dal database, false altrimenti
    $err = "";
    $msg = "";

    //Recupera i dettagli dell'utente il cui uid è passato come query string.
    function recuperaUtente(){
        global $connection;
        global $username;
        global $immagineUtente;
        global $descrizioneUtente;
        global $utenteRecuperato;
        global $err;
                                                         
        if(!isset($_GET["uid"]) || !preg_match(REG_EXP_ID, $_GET["uid"])){      //prima verifico che l'uid non sia assente e che sia un intero  
            $err = "UID assente o non valido.";
            return;
        }

        $query = "SELECT * FROM utente WHERE uid=?";                            //provo a recuperare i dati dell'utente
        $types = "i";
        $uid = $_GET["uid"];
        $statement = execute_prepared_statement($connection, $query, $types, $uid);
        if(!$statement){
            $err = "Errore nel recuperare i dati dell'utente";
            return;
        }

        $result = mysqli_stmt_get_result($statement);
        if(mysqli_num_rows($result) === 0){
            $err = "UID non corrispondente ad alcun utente.";
            return;
        }

        $utenteRecuperato = true;                   //arrivati qui, i dati dell'utente sono stati recuperati
        $row = mysqli_fetch_assoc($result);
        
        $username = $row["username"];                       //salvo i dettagli dell'utente
        $immagineUtente = $row["immagineUtente"];
        $descrizioneUtente = pulisciStringa($row["descrizioneUtente"]);

        mysqli_free_result($result);
        
    }
    
    //Mostra i brani caricati dall'utente il cui uid è passato come query string. 
    function caricaBraniUtente(){
        global $connection;
        global $err;

        $query = "SELECT * FROM brano WHERE utente=? ORDER BY numeroAscolti DESC";                            
        $types = "i";
        $uid = $_GET["uid"];
        $statement = execute_prepared_statement($connection, $query, $types, $uid);
        if(!$statement){
            echo "<span class='contenitore-errore'>Errore nel recuperare i brani dell'utente.</span>";
            return;
        }

        echo "<h2>Brani caricati</h2>";
        echo "<div id='contenitore-griglie'>";

        $result = mysqli_stmt_get_result($statement);

        if(mysqli_num_rows($result) === 0){
            echo "<span>Nessun brano caricato.</span>";
        }
        else{
            while($row = mysqli_fetch_assoc($result)){
                $immagineBrano = $row["immagineBrano"];
                $titolo = pulisciStringa($row["titolo"]);
                $sid = $row["sid"];
                echo <<<EOT
                    <div class='contenitore-griglia'>
                        <div class='contenitore-immagine-griglia'>
                            <img src='$immagineBrano' class='immagine-griglia' alt='Immagine di copertina del brano'>
                        </div>
                        <a href='brano.php?sid=$sid' class='titolo-brano'>$titolo</a>   
                    </div> 
                EOT;
            }       
        }
        echo "</div>";         

    }

    recuperaUtente();       //recupero i dati dell'utente

?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Profilo</title>
        <link rel="icon" href="../imgs/icona.ico"> 
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
        <?php
            include_once "header.php";
        ?>
        <main id='main-pagina'>
            <?php if($utenteRecuperato){ ?>            
                <div id='contenitore-dettagli'>
                    <div id='contenitore-immagine'>
                        <?php
                            echo "<img src='$immagineUtente' id='immagine-mostrata' alt='Immagine del profilo'>";
                        ?>
                    </div>
                    <div id='dettagli'>
                        <span id="categoria-dettagli">Profilo</span>
                        <div id="intestazione-dettagli">
                            <?php
                                echo "<span id='titolo-dettagli'>$username</span>";
                                if(isset($_SESSION["uid"]) && $_SESSION["uid"] == $_GET["uid"]){        //se utente è loggato ed il suo uid è uguale a quello del profilo, può modificarlo
                                    echo "<a href='modifica_profilo.php' id='modifica'>Modifica profilo</a>";
                                }
                            ?>
                        </div>
                        <p id="descrizione">
                            <?php
                                echo ($descrizioneUtente !== "") ? $descrizioneUtente : "Nessuna descrizione disponibile.";
                            ?>
                        </p>
                    </div>
                </div>
                <div id="brani-caricati">
                    <?php
                        caricaBraniUtente();
                    ?>
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
