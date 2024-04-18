<?php
    session_start();
    include_once "dbaccess.php";
    include_once "utils.php";

    if(!isset($_SESSION["uid"])){           //se l'utente non è loggato, lo mando alla pagina di login
        header("location: accesso.php");
        exit();
    }

    $err = "";
    $connection;
    $sid;

    function processaCaricamento(){
        global $err;
        global $connection;
        global $sid;

        if(!isset($_POST["titolo"]) || $_POST["titolo"] === "" ||                      //se qualche dato obbligatorio manca
           !isset($_FILES["brano"]) || !is_uploaded_file($_FILES["brano"]["tmp_name"])){
            $err = "Ci sono dei dati mancanti.";
            return false;
        }

        $titolo = $_POST["titolo"];                                                  //recupero i dati inseriti dall'utente
        $descrizioneBrano = (isset($_POST["descrizione"])) ? $_POST["descrizione"] : "";
        $branoCaricato = $_FILES["brano"];
        $imgDestPath = "";                                               //qui metterò il nuovo path dell'immagine del brano, se l'utente l'ha caricata
        $imgCurrPath = "";                                               //qui metterò il path corrente dell'immagine del brano, se l'utente l'ha caricata
        $imgExtension = "";                                              //qui metterò l'estensione dell'immagine del brano, se l'utente l'ha caricata

        if(!preg_match(REG_EXP_TITOLO, $titolo) ||               //se i dati testuali non rispettano il formato richiesto
           !preg_match(REG_EXP_DESCRIZIONE, $descrizioneBrano)){
            $err = "I dati testuali non rispettano il formato richiesto.";
            return false;
        }

        if($branoCaricato["error"] !== UPLOAD_ERR_OK){       //in caso di errore nel caricamento del brano
            $err = "Errore nel caricamento del brano";
            return false;
        }

        if($branoCaricato["size"] > TRACK_MAX_SIZE){              //se il brano supera la dim massima accettata
            $err = "Il brano supera la dimensione massima consentita.";
            return false;
        }

        $trackExtension = explode(".", $branoCaricato["name"]);     //recupero estensione e mime-type del brano
        $trackExtension = end($trackExtension);
        $trackMime = $branoCaricato["type"];

        if(!in_array($trackExtension, TRACK_VALID_EXT) || !in_array($trackMime, TRACK_VALID_MIME)){   //se il brano non è di un'estensione e/o mime-type valido
            $err = "Estensione e/o mime-type del brano non valido.";
            return false;
        }

        $trackCurrPath = $branoCaricato["tmp_name"];                                                    
        $trackDestPath = "../brani/" . $_SESSION["uid"] . "_" . time() . ".$trackExtension";        //caricherò il file con un nome temporaneo, che modificherò una volta ottenuto il sid (song id)

        
        if(isset($_FILES["immagine"]) && is_uploaded_file($_FILES["immagine"]["tmp_name"])){        //se l'utente ha caricato un'immagine
            $immagineCaricata = $_FILES["immagine"];                                              
            if($immagineCaricata["error"] !== UPLOAD_ERR_OK){       //in caso di errore nel caricamento
                $err = "Errore nel caricamento dell'immagine.";
                return false;
            }

            if($immagineCaricata["size"] > TRACK_MAX_SIZE){              //se l'immagine supera la dimensione massima consentita
                $err = "L'immagine supera la dimensione massima consentita.";
                return false;
            }

            $imgExtension = explode(".", $immagineCaricata["name"]);          
            $imgExtension = end($imgExtension);
            $imgMime = $immagineCaricata["type"];

            if(!in_array($imgExtension, IMG_VALID_EXT) || !in_array($imgMime, IMG_VALID_MIME)){   //se l'immagine non è di un'estensione e/o mime-type valido
                $err = "Estensione e/o mime-type dell'immagine non valida.";
                return false;
            }

            $imgCurrPath = $immagineCaricata["tmp_name"];
            $imgDestPath = "../imgs/brano/" . $_SESSION["uid"] . "_" . time() . ".$imgExtension";        //caricherò il file con un nome temporaneo, che modificherò una volta ottenuto il sid
        }


        $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   //procedo al caricamento, dunque apro la connessione al database
        if(mysqli_connect_errno()){                                     //in caso di errore di connessione
            exit(mysqli_connect_error());
        }

        mysqli_autocommit($connection, FALSE);          //committerò solo se sia il caricamento del brano che la rinominazione del file del brano e dell'immagine andranno bene              

        $query = "INSERT INTO brano(titolo, utente, pathBrano, descrizioneBrano, immagineBrano) VALUES (?, ?, ?, ?, " ;       //preparo la query in base ai dati inseriti dall'utente
        $types = "siss";
        $params = [$titolo, $_SESSION["uid"], $trackDestPath, $descrizioneBrano];

        if($imgDestPath !== ""){
            $query .= "?)";
            $types .= "s";
            $params[] = $imgDestPath;
        }
        else{
            $query .= "DEFAULT)";
        }

        $statement = execute_prepared_statement($connection, $query, $types, ...$params);
        if(!$statement){
            $err = "Errore nel caricamento del brano.";
            mysqli_rollback($connection);
            return false;
        }

        $sid = mysqli_insert_id($connection);
        $trackDestPath = "../brani/" . $sid . ".$trackExtension";                                   //rinomino i file del brano e dell'immagine sfruttando il sid
        $imgDestPath = ($imgDestPath !== "") ? ("../imgs/brano/" . $sid . ".$imgExtension") : "";

        $query = "UPDATE brano SET pathBrano=?";        //preparo la query in base ai dati inseriti dall'utente
        $types = "s";
        $params = [$trackDestPath];

        if($imgDestPath !== ""){
            $query .= ", immagineBrano=?";
            $types .= "s";
            $params[] = $imgDestPath;
        }
        
        $query .= " WHERE sid=". $sid;

        $statement = execute_prepared_statement($connection, $query, $types, ...$params);
        if(!$statement){
            $err = "Errore nella finalizzazione del caricamento del brano.";
            mysqli_rollback($connection);
            return false;
        }

        move_uploaded_file($trackCurrPath, $trackDestPath);         //se tutto è andato bene, sposto i file nella directory finale
        if($imgDestPath !== ""){
            move_uploaded_file($imgCurrPath, $imgDestPath);
        }

        mysqli_commit($connection);
        return true;
    }

    if($_SERVER["REQUEST_METHOD"] === "POST"){      //se questo script va in esecuzione a seguito di una richiesta POST, processo il caricamento del brano.
        $success = processaCaricamento();
        if($connection){                            //se la connessione era stata aperta, la chiudo perché non serve più
            mysqli_close($connection);              
        }
        if($success){                               //se il caricamento del brano avviene con successo, reindirizzo l'utente alla pagina del brano
            header("Location: brano.php?sid=$sid");          
            exit();
        }
    }

?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Carica brano</title>
        <link rel="icon" href="../imgs/icona.ico"> 
        <link rel="stylesheet" href="../css/style.css">
        <script src="../js/aggiorna_immagine_mostrata.js"></script>
    </head>

    <body>
        <?php
            include_once "header.php";
        ?>
        <main id="main-form">
            <div id="contenitore-doppio">
                <div id="contenitore-doppio-immagine">
                    <div id="contenitore-immagine">
                        <img src="../imgs/brano/default.png" id="immagine-mostrata" alt="Immagine del brano">
                    </div>
                </div>
                <div id="contenitore-form">
                    <h2>Carica un brano</h2>
                    <form class="form" method="post" action="carica_brano.php" enctype="multipart/form-data">
                        <div class="campo-form">
                            <label for="titolo">Titolo</label>
                            <input type='text' id='titolo' name='titolo' pattern='^.{1,50}$' required>
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
                            <textarea id='descrizione' name='descrizione' rows=4 maxlength='140'></textarea>          
                        </div>

                        <div class="campo-form">
                            <label for="brano">Brano</label>
                            <input type="file" id="brano" name="brano" accept=".mp3" required>
                            <span class="hint">Deve essere un brano .mp3, di dimensione massima 10MB.</span>                    
                        </div>
                        
                        <button type="submit" class="submit-form">Carica</button>
                    </form>

                    <div id="contenitore-messaggio-form">
                        <?php
                            if($err !== ""){
                                echo "<span class='contenitore-errore'>$err</span>";
                            }
                        ?>
                    </div>
 
                </div>
            </div>       
        </main>
        <?php
            include_once "footer.php";
        ?>
    </body>
</html>
