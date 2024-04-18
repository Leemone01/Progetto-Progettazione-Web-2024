<?php
    session_start();
    include_once "dbaccess.php";
    include_once "utils.php";

    $modificaConsentita = false;
    $sid;
    $titoloCorrente;
    $immagineBranoCorrente;
    $descrizioneBranoCorrente;
    $succ = "";
    $err = "";

    if(!isset($_SESSION["uid"])){           //se l'utente non è loggato, lo mando alla pagina di login
        header("location: accesso.php");
        exit();
    }

    $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   //ora apro la connessione al database, e in caso di errore
    if(mysqli_connect_errno()){                                     //termino subito lo script.
        exit(mysqli_connect_error());
    }


    function recuperaBrano(){
        global $connection;
        global $modificaConsentita;
        global $titoloCorrente;
        global $immagineBranoCorrente;
        global $descrizioneBranoCorrente;
        global $sid;
        global $err;
    
        if(!isset($_GET["sid"]) || !preg_match(REG_EXP_ID, $_GET["sid"])){      //prima verifico che il sid non sia assente e che sia un intero  
            $err = "SID assente o non valido.";
            return;
        }

        $query = "SELECT* FROM brano WHERE sid=?";                                      //recupero ora i dati del brano
        $types = "i";
        $sid = $_GET["sid"];
        $statement = execute_prepared_statement($connection, $query, $types, $sid);     
        if(!$statement){
            $err = "Errore nel recuperare i dati del brano.";
            return;
        }   

        $result = mysqli_stmt_get_result($statement);
        if(mysqli_num_rows($result) === 0){
            $err = "SID non corrispondente ad alcun brano.";
            return;
        }

        $row = mysqli_fetch_assoc($result);
        $titoloCorrente = pulisciStringa($row["titolo"]);
        $immagineBranoCorrente = $row["immagineBrano"];
        $descrizioneBranoCorrente = pulisciStringa($row["descrizioneBrano"]);
        $utente = $row["utente"];

        if($utente !== $_SESSION["uid"] && $_SESSION["tipo"] !== 0){        //se l'utente nè è l'autore del brano, nè è admin, non ha il permesso di modificare il brano
            $err = "Non hai il permesso di modificare questo brano.";
            return;
        }

        $modificaConsentita = true;
        mysqli_free_result($result);
    }

    
    function processaModifica(){
        global $connection;
        global $titoloCorrente;
        global $immagineBranoCorrente;
        global $descrizioneBranoCorrente;
        global $sid;
        global $err;
        global $succ;
        
        if(!isset($_POST["titolo"]) || $_POST["titolo"] === ""){
            $err = "Ci sono dei dati mancanti.";
            return;
        }                  

        $titolo = $_POST["titolo"];                         //recupero i dati inseriti dall'utente
        $descrizioneBrano = (isset($_POST["descrizione"])) ? $_POST["descrizione"] : "";
        $imgDestPath = "";                                               //qui metterò il nuovo path della nuova immagine del brano, se l'utente l'ha caricata
        $imgCurrPath = "";                                               //qui metterò il path corrente della nuova immagine del brano, se l'utente l'ha caricata
        $imgExtension = "";                                              //qui metterò l'estensione della nuova immagine del brano, se l'utente l'ha caricata

        if(!preg_match(REG_EXP_TITOLO, $titolo) ||               //se i dati testuali non rispettano il formato richiesto
           !preg_match(REG_EXP_DESCRIZIONE, $descrizioneBrano)){
            $err = "I dati testuali non rispettano il formato richiesto.";
            return;
        }
    
        if(isset($_FILES["immagine"]) && is_uploaded_file($_FILES["immagine"]["tmp_name"])){   //se è stata caricata un'immagine
            $immagineCaricata = $_FILES["immagine"];                                              
            if($immagineCaricata["error"] !== UPLOAD_ERR_OK){       //in caso di errore nel caricamento
                $err = "Errore nel caricamento dell'immagine.";
                return;
            }

            if($immagineCaricata["size"] > IMG_MAX_SIZE){                       //se l'immagine supera la dim massima accettata
                $err = "L'immagine supera la dimensione massima consentita.";
                return;
            }

            $imgExtensionsArray = explode(".", $immagineCaricata["name"]);
            $imgExtension = end($imgExtensionsArray);
            $imgMime = $immagineCaricata["type"];

            if(!in_array($imgExtension, IMG_VALID_EXT) ||                       //se l'immagine non è di un'estensione e/o mime-type valido
               !in_array($imgMime, IMG_VALID_MIME)){
                $err = "Estensione e/o mime-type dell'immagine non valido.";
                return;
            }

            $imgCurrPath = $immagineCaricata["tmp_name"];
            $imgDestPath = "../imgs/brano/" . $_GET["sid"] . ".$imgExtension";                              //mi fermo qui, sposterò poi l'immagine solo se la query per aggiornare il brano va buon fine
        }

        $query = "UPDATE brano SET titolo=?, descrizioneBrano=?";       //preparo la query in base ai dati inseriti dall'utente
        $types = "ss";
        $params = [$titolo, $descrizioneBrano];

        if($imgDestPath !== ""){
            $query .= ", immagineBrano=?";
            $types .= "s";
            $params[] = $imgDestPath;
        }

        $query .= " WHERE sid=?";
        $types .= "i";
        $params[] = $sid;

        $statement = execute_prepared_statement($connection, $query, $types, ...$params);
        if(!$statement){
            $err = "Errore nella modifica dei dati del brano.";
            return;
        }

        $titoloCorrente = pulisciStringa($titolo);              //aggiorno i dettagli che verranno mostrati nella pagina
        $descrizioneBranoCorrente = pulisciStringa($descrizioneBrano);

        if($imgDestPath != ""){                                 //a questo punto, sposto l'immagine del brano
            $nomeImmagineBranoCorrenteConEstensione = explode("/", $immagineBranoCorrente);
            $nomeImmagineBranoCorrenteConEstensione = end($nomeImmagineBranoCorrenteConEstensione);
            $nomeImmagineBranoCorrenteSenzaEstensione = explode(".", $nomeImmagineBranoCorrenteConEstensione);
            $nomeImmagineBranoCorrenteSenzaEstensione = $nomeImmagineBranoCorrenteSenzaEstensione[0];
            if($nomeImmagineBranoCorrenteSenzaEstensione == $_GET["sid"]){
                unlink($immagineBranoCorrente);                   //rimuovo la vecchia immagine solo se non è quella di default
            }
            
            move_uploaded_file($imgCurrPath, $imgDestPath);     //metto la nuova
            $immagineBranoCorrente = $imgDestPath;            //aggiorno l'immagine del brano che verrà mostrata nella pagina
        }

        $succ = "Brano aggiornato con successo.";
        
    }

    

    recuperaBrano();                                //recupero i dati correnti del brano
    
    if($_SERVER["REQUEST_METHOD"] === "POST"){      //se questo script va in esecuzione a seguito di una richiesta POST, processo la modifica del brano,
        if($modificaConsentita){                    //e lo faccio solo se la modifica è consentita.
            processaModifica();
        }
    }
    
    mysqli_close($connection);      //che sia POST o GET, dopo aver fatto tutto il necessario con il database, chiudo la connessione
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Modifica Brano</title>
        <link rel="icon" href="../imgs/icona.ico"> 
        <link rel="stylesheet" href="../css/style.css">
        <script src="../js/aggiorna_immagine_mostrata.js"></script>
    </head>

    <body>
        <?php
            include_once "header.php";
        ?>
        <main id="main-form">
            <?php if($modificaConsentita){ ?>
                <div id="contenitore-doppio">
                    <div id="contenitore-doppio-immagine">
                        <div id="contenitore-immagine">
                            <?php
                                echo "<img src='$immagineBranoCorrente' id='immagine-mostrata' alt='Immagine del brano'>";
                            ?>
                        </div>
                    </div>
                    <div id="contenitore-form">
                        <h2>Modifica brano</h2>
                        <?php echo "<form class='form' method='post' action='modifica_brano.php?sid=$sid' enctype='multipart/form-data'>"; ?>
                            <div class="campo-form">
                                <label for="titolo">Titolo</label>
                                <?php
                                    echo "<input type='text' id='titolo' name='titolo' value='$titoloCorrente' pattern='^.{1,50}$' required>"
                                ?>
                                <span class="hint">Deve essere costituito da 1-50 caratteri.</span>
                            </div>

                            <div class="campo-form">
                                <label for="immagine">Immagine</label>
                                <input type="file" id="immagine" name="immagine" accept=".jpg, .png">
                                <span class="hint">Deve essere un'immagine .jpg o .png, di dimensione massima 1MB.</span>                    
                            </div>

                            <div class="campo-form">
                                <div class="label-hint">                                                      <!-- raggruppo label e hint di descrizione in un div -->
                                    <label for="descrizione">Descrizione</label>                              <!-- così da avere l'hint vicino al label -->
                                    <span class="hint">Deve essere costituita da 0-140 caratteri.</span>
                                </div>
                                <?php
                                    echo "<textarea id='descrizione' name='descrizione' rows=4 maxlength='140'>$descrizioneBranoCorrente</textarea>"
                                ?>
                                
                            </div>
                            
                            <div>
                                <button type="submit" class="submit-form" name="submit-personali" value="submit">Aggiorna</button>
                                <?php echo "<button type='button' class='submit-form'><a href='brano.php?sid=$sid'>Torna al brano</a></button>"?> 
                            </div>
                            
                        </form>

                        <div id="contenitore-messaggio-form">
                            <?php
                                if($err !== ""){
                                    echo "<span class='contenitore-errore'>$err</span>";
                                }
                                else if($succ !== ""){
                                    echo "<span class='contenitore-successo'>$succ</span>";
                                }
                            ?>
                        </div>
                    </div>
                </div>
            <?php }
                else {                                                           //se invece non è possibile effettuare la modifica
                    echo "<span class='contenitore-errore'>$err</span>";         //mostro il messaggio di errore relativo
                }
            ?>       
        </main>
        <?php
            include_once "footer.php";
        ?>
    </body>
</html>
