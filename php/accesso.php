<?php
    session_start();
    include_once "utils.php";
    include_once "dbaccess.php";

    if(isset($_SESSION["uid"])){            //se utente è gia loggato, lo rimando alla homepage
        header("location: index.php");
        exit();
    }

    $err = "";
    $connection;

    //Processa l'accesso dell'utente. Restituisce true se tutto va bene, 
    //false in caso di errore, e in $err ci sarà il motivo dell'errore.
    function processaAccesso(){   
        global $err; 
        global $connection;       

        if(!isset($_POST["username"]) || $_POST["username"] === "" ||                      //se qualche dato manca
           !isset($_POST["password"]) || $_POST["password"] === ""){
            $err = "Ci sono dei dati mancanti.";
            return false;
        }

        $username = $_POST["username"];                         //recupero i dati inseriti dall'utente
        $password = $_POST["password"];

        if(!preg_match(REG_EXP_USERNAME, $username) ||               //se i dati non rispettano il formato richiesto
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
        
        $query = "SELECT * FROM utente WHERE username=?";        //vedo se esiste un utente con tale username
        $types = "s";
        $statement = execute_prepared_statement($connection, $query, $types, $username);
        if(!$statement){
            $err = "Errore nell'accesso al database degli utenti.";
            return false;
        }
        $result = mysqli_stmt_get_result($statement);
        if(mysqli_num_rows($result) === 0){
            $err = "Username non esistente.";
            return false;
        }

        $row = mysqli_fetch_assoc($result);                     //vedo se la password è corretta
        $passwordHashata = $row["password"];
        if(!password_verify($password, $passwordHashata)){
            $err = "Password errata.";
            return false;
        }

        $_SESSION["uid"] = $row["uid"];                 //arrivati qui, l'utente ha inserito username e password corretti, dunque rendo l'utente loggato
        $_SESSION["username"] = $row["username"];       //e recupero username, tipo e immagine dell'utente
        $_SESSION["tipo"] = $row["tipo"];
        $_SESSION["immagine"] = $row["immagineUtente"];
           
        return true;               
    }

    if($_SERVER["REQUEST_METHOD"] === "POST"){      //se questo script va in esecuzione a seguito di una richiesta POST, processo l'accesso dell'utente.
        $success = processaAccesso();               
        if($connection){                            //se la connessione era stata aperta, la chiudo perché non serve più
            mysqli_close($connection);  
        }
        if($success){                               //se l'accesso avviene con successo, reindirizzo l'utente alla home page
            header("Location: index.php");
            exit();
        }              
    }
    
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Accesso</title>
        <link rel="icon" href="../imgs/icona.ico"> 
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
        <?php
            include_once "header.php";
        ?>
        <main id="main-form">
            <div id="contenitore-form">
                <h2>Accedi</h2>
                <form class="form" method="post" action="accesso.php">
                    <div class="campo-form">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" pattern="^[A-Za-z0-9]{2,16}$" required>
                    </div>

                    <div class="campo-form">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" pattern="^\S{4,16}$" required>            
                    </div>
                    
                    <button type="submit" class="submit-form">Accedi</button>
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