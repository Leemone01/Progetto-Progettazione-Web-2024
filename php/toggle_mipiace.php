<?php
    session_start();
    include_once "utils.php";
    include_once "dbaccess.php";

    $risultato = [                          //preparo un oggetto che conterrà il risultato di questo script
        'success' => false
    ];

    if(!isset($_SESSION["uid"])){            //se utente non è loggato
        $risultato['err'] = "Devi fare l'accesso per poter lasciare mi piace.";
        echo json_encode($risultato);
        exit();
    }

    $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   
    if(mysqli_connect_errno()){                                     //in caso di errore di connessione
        $risultato['err'] = "Errore nell'accesso al database";
        echo json_encode($risultato);
        exit();
    }
                         
    $sid = $_POST["sid"];                       //ricavo sid e uid 
    $uid = $_SESSION["uid"];

    $query = "SELECT * FROM mipiace WHERE utente=? AND brano=?";        //vedo se l'utente ha già messo mi piace a tale brano
    $types = "ii";
    $statement = execute_prepared_statement($connection, $query, $types, $uid, $sid);
    if(!$statement){
        $risultato['err'] = "Errore nell'accesso al database dei mi piace";
        echo json_encode($risultato);
        mysqli_close($connection);
        exit();
    }

    $result = mysqli_stmt_get_result($statement);

    if(mysqli_num_rows($result) === 0){     //se l'utente non l'ha messo, lo aggiungo
        $query = "INSERT INTO mipiace(utente, brano) VALUES (?, ?)";
        $types = "ii";
        $statement = execute_prepared_statement($connection, $query, $types, $uid, $sid);
        if(!$statement){
            $risultato['err'] = "Errore nell'inserimento del mi piace";
            echo json_encode($risultato);
            mysqli_close($connection);
            exit();
        }
        $risultato["azione"] = "aggiunto";
    }
    else{                                   //se l'utente l'ha messo, lo rimuovo
        $query = "DELETE FROM mipiace WHERE utente=? AND brano=?";
        $types = "ii";
        $statement = execute_prepared_statement($connection, $query, $types, $uid, $sid);
        if(!$statement){
            $risultato['err'] = "Errore nella rimozione del mi piace";
            echo json_encode($risultato);
            mysqli_close($connection);
            exit();
        }
        $risultato["azione"] = "rimosso";
    }

    $risultato["success"] = true;                       //arrivati qui, è andato tutto bene.
    
    echo json_encode($risultato);                        
    mysqli_close($connection);
?>