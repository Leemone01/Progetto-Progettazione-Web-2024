<?php
    session_start();
    include_once "dbaccess.php";
    include_once "utils.php";

    if(!isset($_SESSION["uid"])){           //se l'utente non è loggato, lo mando alla pagina di login
        header("location: accesso.php");
        exit();
    }

    $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   //apro come prima cosa la connessione al database, così in caso di errore
    if(mysqli_connect_errno()){                                     //termino subito lo script.
        exit(mysqli_connect_error());
    }
    
    $query = "SELECT* FROM utente WHERE uid=" . $_SESSION["uid"];       //recupero i dati personali dell'utente (servirà sia prima di modificare il profilo,
    $result = mysqli_query($connection, $query);                        //che per effettuare la modifica vera e propria)
    if(!$result){                                                       //in caso di errore, termino lo script.
        mysqli_close($connection);
        exit("Errore nel recuperare i dati dell'utente.");
    }

    $row = mysqli_fetch_assoc($result);
    $usernameCorrente = $row["username"];
    $emailCorrente = pulisciStringa($row["email"]);
    $immagineUtenteCorrente = $row["immagineUtente"];
    $descrizioneUtenteCorrente = pulisciStringa($row["descrizioneUtente"]);
    $passwordCorrenteHashata = $row["password"];

    mysqli_free_result($result);

    $succ = "";
    $err = "";


    function processaModificaPersonali(){
        global $connection;
        global $usernameCorrente;
        global $emailCorrente;
        global $immagineUtenteCorrente;
        global $descrizioneUtenteCorrente;
        global $succ;
        global $err;
        
        if(!isset($_POST["username"]) || $_POST["username"] === "" ||                      //se qualche dato testuale obbligatorio manca
           !isset($_POST["email"]) || $_POST["email"] === ""){
            $err = "Ci sono dei dati personali mancanti.";
            return;
        }                  

        $username = $_POST["username"];                         //recupero i dati inseriti dall'utente
        $email = $_POST["email"];
        $descrizioneUtente = (isset($_POST["descrizione"])) ? $_POST["descrizione"] : "";
        $imgDestPath = "";                                               //qui metterò il nuovo path della nuova immagine di profilo, se l'utente l'ha caricata
        $imgCurrPath = "";                                               //qui metterò il path corrente della nuova immagine di profilo, se l'utente l'ha caricata
        $imgExtension = "";                                              //qui metterò l'estensione della nuova immagine di profilo, se l'utente l'ha caricata

        if(!preg_match(REG_EXP_USERNAME, $username) ||               //se i dati testuali non rispettano il formato richiesto
           !preg_match(REG_EXP_EMAIL, $email) ||
           !preg_match(REG_EXP_DESCRIZIONE, $descrizioneUtente)){
            $err = "I dati personali testuali non rispettano il formato richiesto.";
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
            $imgDestPath = "../imgs/profilo/" . $_SESSION["uid"] . ".$imgExtension";                              //mi fermo qui, sposterò poi l'immagine solo se la query per aggiornare il profilo va buon fine
        }

        $query = "SELECT * FROM utente WHERE (username=? OR email=?) AND uid <>". $_SESSION["uid"];            //vedo se esiste un utente diverso da lui con username o email uguale a quelli nuovi
        $types = "ss";
        $statement = execute_prepared_statement($connection, $query, $types, $username, $email);
        if(!$statement){
            $err = "Errore nell'accesso al database degli utenti.";
            return;
        }
        $result = mysqli_stmt_get_result($statement);
        if(mysqli_num_rows($result) > 0){
            $err = "Username o email già in uso.";
            return;
        }

        $query = "UPDATE utente SET username=?, email=?, descrizioneUtente=?";       //preparo la query in base ai dati inseriti dall'utente
        $types = "sss";
        $params = [$username, $email, $descrizioneUtente];

        if($imgDestPath !== ""){
            $query .= ", immagineUtente=?";
            $types .= "s";
            $params[] = $imgDestPath;
        }

        $query .= " WHERE uid=". $_SESSION["uid"];

        $statement = execute_prepared_statement($connection, $query, $types, ...$params);
        if(!$statement){
            $err = "Errore nella modifica dei dati personali dell'utente.";
            return;
        }

        $usernameCorrente = $username;              //aggiorno i dettagli che verranno mostrati nella pagina
        $emailCorrente = pulisciStringa($email);
        $descrizioneUtenteCorrente = pulisciStringa($descrizioneUtente);
        
        $_SESSION["username"] = $username;          //aggiorno l'username mostrato nell'header

        if($imgDestPath != ""){                                 //a questo punto, sposto l'immagine di profilo
            $nomeImmagineUtenteCorrenteConEstensione = explode("/", $immagineUtenteCorrente);
            $nomeImmagineUtenteCorrenteConEstensione = end($nomeImmagineUtenteCorrenteConEstensione);
            $nomeImmagineUtenteCorrenteSenzaEstensione = explode(".", $nomeImmagineUtenteCorrenteConEstensione);
            $nomeImmagineUtenteCorrenteSenzaEstensione = $nomeImmagineUtenteCorrenteSenzaEstensione[0];
            if($nomeImmagineUtenteCorrenteSenzaEstensione == $_SESSION["uid"]){
                unlink($immagineUtenteCorrente);                   //rimuovo la vecchia immagine solo se non è quella di default
            }
            
            move_uploaded_file($imgCurrPath, $imgDestPath);     //metto la nuova
            $immagineUtenteCorrente = $imgDestPath;            //aggiorno l'immagine di profilo che verrà mostrata nella pagina
            $_SESSION["immagine"] = $imgDestPath;              //e aggiorno il path dell'immagine presente in nell'array $_SESSION
        }

        $succ = "Profilo aggiornato con successo.";
        
    }

    function processaModificaPassword(){
        global $succ;
        global $err;
        global $connection;
        global $passwordCorrenteHashata;

        if(!isset($_POST["corrente-password"]) || $_POST["corrente-password"] === "" ||                      //se qualche dato testuale obbligatorio manca
           !isset($_POST["nuova-password"]) || $_POST["nuova-password"] === "" ||
           !isset($_POST["nuova-password-conferma"]) || $_POST["nuova-password-conferma"] === ""){
            $err = "Inserisci tutti i campi.";
            return;
        }

        $passwordCorrente = $_POST["corrente-password"];                //recupero i dati inseriti dall'utente
        $nuovaPassword = $_POST["nuova-password"];
        $nuovaPasswordConferma = $_POST["nuova-password-conferma"];

        if($nuovaPassword !== $nuovaPasswordConferma){
            $err = "Le password non coincidono.";
            return;
        }

        if(!preg_match(REG_EXP_PASSWORD, $nuovaPassword)){
            $err = "La nuova password non rispetta il formato richiesto.";
            return;
        }

        if(!password_verify($passwordCorrente, $passwordCorrenteHashata)){
            $err = "Password corrente errata.";
            return;
        }

        $query = "UPDATE utente SET password=? WHERE uid=" . $_SESSION["uid"];
        $types = "s";
        $nuovaPasswordHashata = password_hash($nuovaPassword, PASSWORD_BCRYPT);
        $statement = execute_prepared_statement($connection, $query, $types, $nuovaPasswordHashata);
        if(!$statement){
            $err = "Errore nell'aggiornamento della password.";
            return;
        }

        $succ = "Profilo aggiornato con successo.";

    }

    if($_SERVER["REQUEST_METHOD"] === "POST"){      //se questo script va in esecuzione a seguito di una richiesta POST, processo la modifica del profilo.
        if(isset($_POST["submit-personali"]) && $_POST["submit-personali"] === "submit"){
            processaModificaPersonali();
        }
        else if(isset($_POST["submit-password"]) && $_POST["submit-password"] === "submit"){
            processaModificaPassword();
        }
    }
    
    mysqli_close($connection);      //che sia POST o GET, dopo aver fatto tutto il necessario con il database, chiudo la connessione
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Modifica Profilo</title>
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
                        <?php
                            echo "<img src='$immagineUtenteCorrente' id='immagine-mostrata' alt='Immagine del profilo'>";
                        ?>
                    </div>
                </div>
                <div id="contenitore-form">
                    <h2>Modifica profilo</h2>
                    <form class="form" method="post" action="modifica_profilo.php" enctype="multipart/form-data">
                        <fieldset>
                            <legend>Informazioni Personali</legend>
                            <div class="campo-form">
                                <label for="username">Username</label>
                                <?php
                                    echo "<input type='text' id='username' name='username' value='$usernameCorrente' pattern='^[A-Za-z0-9]{2,16}$' required>"
                                ?>
                                <span class="hint">Deve essere costituito da 2-16 caratteri alfanumerici.</span>
                            </div>

                            <div class="campo-form">
                                <label for="email">Email</label>
                                <?php
                                    echo "<input type='text' id='email' name='email' value='$emailCorrente' pattern='^(.+)@([^\.].*)\.([a-z]{2,})$' required>"
                                ?>
                                <span class="hint">Il tuo indirizzo email.</span>
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
                                    echo "<textarea id='descrizione' name='descrizione' rows=4 maxlength='140'>$descrizioneUtenteCorrente</textarea>"
                                ?>
                                
                            </div>
                            
                            <button type="submit" class="submit-form" name="submit-personali" value="submit">Aggiorna</button>
                        </fieldset>
                    </form>
                    
                    <form class="form" method="post" action="modifica_profilo.php">   
                        <fieldset>
                            <legend>Password</legend>
                            <div class="campo-form">
                                <label for="corrente-password">Password Corrente</label>
                                <input type="password" id="corrente-password" name="corrente-password" pattern="^\S{4,16}$" required>            
                                <span class="hint">Scrivi la tua password corrente.</span>
                            </div>

                            <div class="campo-form">
                                <label for="nuova-password">Nuova Password</label>
                                <input type="password" id="nuova-password" name="nuova-password" pattern="^\S{4,16}$" required>            
                                <span class="hint">Deve essere costituita da 4-16 caratteri.</span>
                            </div>
                        
                            <div class="campo-form">
                                <label for="nuova-password-conferma">Conferma Nuova Password</label>
                                <input type="password" id="nuova-password-conferma" name="nuova-password-conferma" pattern="^\S{4,16}$" required>
                                <span class="hint">Riscrivi la tua nuova password.</span>                    
                            </div>

                            <button type="submit" class="submit-form" name="submit-password" value="submit">Aggiorna</button>
                        </fieldset> 
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
        </main>
        <?php
            include_once "footer.php";
        ?>
    </body>
</html>
