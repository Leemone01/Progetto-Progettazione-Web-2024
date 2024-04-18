<?php
    session_start();
    include_once "dbaccess.php";
    include_once "utils.php";

    if(isset($_SESSION["uid"])){            //se utente è gia loggato, lo rimando alla homepage
        header("location: index.php");
        exit();
    }

    $err = "";
    $connection;

    //Processa la registrazione dell'utente. Restituisce l'uid dell'utente appena creato se tutto va bene, 
    //false in caso di errore, e in $err ci sarà il motivo dell'errore.
    function processaRegistrazione(){   
        global $err; 
        global $connection;       

        if(!isset($_POST["username"]) || $_POST["username"] === "" ||                      //se qualche dato manca
           !isset($_POST["email"]) || $_POST["email"] === "" ||
           !isset($_POST["password"]) || $_POST["password"] === "" ||
           !isset($_POST["password-conferma"]) || $_POST["password-conferma"] === ""){
            $err = "Ci sono dei dati mancanti.";
            return false;
        }

        $username = $_POST["username"];                         //recupero i dati inseriti dall'utente
        $email = $_POST["email"];
        $password = $_POST["password"];
        $passwordConferma = $_POST["password-conferma"];

        if($password !== $passwordConferma){                         //se le password non coincidono
            $err = "Le password non coincidono.";
            return false;
        }

        if(!preg_match(REG_EXP_USERNAME, $username) ||               //se i dati non rispettano il formato richiesto
           !preg_match(REG_EXP_EMAIL, $email) ||
           !preg_match(REG_EXP_PASSWORD, $password)){
            $err = "I dati non rispettano il formato richiesto.";
            return false;
        }
                                                                        //arrivati qui, i dati dell'utente sono nel formato richiesto.
                                                                        //mi connetto allora al database
        $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   
        if(mysqli_connect_errno()){                                     //in caso di errore di connessione
            exit(mysqli_connect_error());
        }
        
        $query = "SELECT * FROM utente WHERE username=? OR email=?";        //vedo se esiste già un utente con stesso username o email
        $types = "ss";
        $statement = execute_prepared_statement($connection, $query, $types, $username, $email);
        if(!$statement){
            $err = "Errore nell'accesso al database degli utenti.";
            return false;
        }
        $result = mysqli_stmt_get_result($statement);
        if(mysqli_num_rows($result) > 0){
            $err = "Username o email già in uso.";
            return false;
        }

        $query = "INSERT INTO utente(tipo, username, email, password) VALUES (?, ?, ?, ?)";     //arrivati qui, non esiste un utente con stesso username o email, dunque effettuo la registrazione dell'utente nel database
        $types = "isss";    
        $tipo = 1;                                                      //lo rendo un utente normale
        $passwordHashata = password_hash($password, PASSWORD_BCRYPT);   //hasho la sua password
        $statement = execute_prepared_statement($connection, $query, $types, $tipo, $username, $email, $passwordHashata);
        if(!$statement){
            $err = "Errore nell'inserimento dell'utente.";
            return false;
        }

        $_SESSION["uid"] = mysqli_insert_id($connection);       //arrivati qui, è andato tutto bene, dunque rendo l'utente loggato. Recupero l'UID autoincrementante dell'utente
        $_SESSION["username"] = $username;                      //l'username verrà mostrato nell'header di ogni pagina
        $_SESSION["tipo"] = $tipo;                              //il tipo servirà per capire se l'utente è admin o meno
        $_SESSION["immagine"] = DEFAULT_PROFILE_IMG;            //l'immagine dell'utente verrà mostrata quando dovrà lasciare un commento su un brano
             
        return true;               
    }

    if($_SERVER["REQUEST_METHOD"] === "POST"){      //se questo script va in esecuzione a seguito di una richiesta POST, processo la registrazione dell'utente.
        $success = processaRegistrazione();
        if($connection){                            //se la connessione era stata aperta, la chiudo perché non serve più
            mysqli_close($connection);              
        }
        if($success){                               //se la registrazione avviene con successo, reindirizzo l'utente alla home page
            header("Location: index.php");          
            exit();
        }
    }
    
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Registrazione</title>
        <link rel="icon" href="../imgs/icona.ico"> 
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
        <?php
            include_once "header.php";
        ?>
        <main id="main-form">
            <div id="contenitore-form">
                <h2>Registrati</h2>
                <form class="form" method="post" action="registrazione.php">
                    <div class="campo-form">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" pattern="^[A-Za-z0-9]{2,16}$" required>
                        <span class="hint">Deve essere costituito da 2-16 caratteri alfanumerici.</span>
                    </div>

                    <div class="campo-form">
                        <label for="email">Email</label>
                        <input type="text" id="email" name="email" pattern="^(.+)@([^\.].*)\.([a-z]{2,})$" required>
                        <span class="hint">Il tuo indirizzo email.</span>
                    </div>

                    <div class="campo-form">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" pattern="^\S{4,16}$" required>            
                        <span class="hint">Deve essere costituita da 4-16 caratteri.</span>
                    </div>
                    
                    <div class="campo-form">
                        <label for="password-conferma">Conferma Password</label>
                        <input type="password" id="password-conferma" name="password-conferma" pattern="^\S{4,16}$" required>
                        <span class="hint">Riscrivi la tua password.</span>                    
                    </div>

                    <button type="submit" class="submit-form">Registrati</button>
                </form>

                <div id="contenitore-messaggio-form">
                    <?php
                        if($err !== ""){
                            echo "<span class='contenitore-errore'>$err</span>";
                        }
                    ?>
                </div>

            </div>
        </main>
        <?php
            include_once "footer.php"
        ?>
    </body>
</html>