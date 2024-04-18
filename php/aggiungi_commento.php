<?php
    session_start();
    include_once "utils.php";
    include_once "dbaccess.php";

    $risultato = [                          //preparo un oggetto che conterrà il risultato di questo script
        'success' => false
    ];

    if(!isset($_SESSION["uid"])){            //se utente non è loggato
        $risultato['err'] = "Devi fare l'accesso per poter aggiungere un commento.";
        echo json_encode($risultato);
        exit();
    }

    $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   
    if(mysqli_connect_errno()){                                     //in caso di errore di connessione
        $risultato['err'] = "Errore nell'accesso al database";
        echo json_encode($risultato);
        exit();
    }

    $commento = $_POST["commento"];         //ricavo commento, sid, uid, e altre informazioni che serviranno allo script js  
    $sid = $_POST["sid"];                   //per inserire il commento nella pagina
    $uid = $_SESSION["uid"];
    $immagineUtente = $_SESSION["immagine"];
    $username = $_SESSION["username"];

    if(!preg_match(REG_EXP_COMMENTO, $commento)){
        $risultato['err'] = "Il commento non rispetta il formato richiesto.";
        echo json_encode($risultato);
        mysqli_close($connection);
        exit();
    }

    $query = "INSERT INTO commento(utente, brano, contenuto) VALUES (?, ?, ?)";
    $types = "iis";
    $statement = execute_prepared_statement($connection, $query, $types, $uid, $sid, $commento);
    if(!$statement){
        $risultato['err'] = "Errore nell'inserimento del commento";
        echo json_encode($risultato);
        mysqli_close($connection);
        exit();
    }

    $risultato["success"] = true;                       //arrivati qui, è andato tutto bene.
    $risultato["commento"] = $commento;                 //metto allora in risultato tutto quello 
    $risultato["sid"] = $sid;                           //che servirà allo script js per mostrare
    $risultato["uid"] = $uid;                           //il nuovo commento
    $risultato["immagineUtente"] = $immagineUtente;
    $risultato["username"] = $username;
    
    echo json_encode($risultato);                                    //e mando risultato
    mysqli_close($connection);
?>