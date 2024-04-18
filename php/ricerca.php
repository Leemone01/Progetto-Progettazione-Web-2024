<?php
    session_start();
    include_once "dbaccess.php";
    include_once "utils.php";

    $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   
    if(mysqli_connect_errno()){                                     
        exit(mysqli_connect_error());
    }


    function caricaRisultati(){
        global $connection;
        global $err;

        if(!isset($_GET["search-type"]) || ($_GET["search-type"] !== "utente" && $_GET["search-type"] !== "brano") || 
           !isset($_GET["search-query"])) {
            echo "<span class='contenitore-errore'>Tipo di ricerca non valido.</span>";
            return;
        }

        $searchQuery = "%" . $_GET["search-query"] . "%";
        $searchType = $_GET["search-type"];
        
        $query = "SELECT * FROM ";           //costruisco la query da fare al database in base ai dati inseriti dall'utente
        $types = "s";

        if($searchType === "brano"){
            $query .= "brano INNER JOIN utente ON utente = uid WHERE titolo Like ?";
        }
        else {
            $query .= "utente WHERE username LIKE ?";
        }
        
        $statement = execute_prepared_statement($connection, $query, $types, $searchQuery);
        if(!$statement){
            echo "<span class='contenitore-errore'>Errore nella ricerca.</span>";
            return;
        }

        echo "<h2>Risultati di ricerca per '" . pulisciStringa($_GET["search-query"]) . "':</h2>";
        echo "<div id='contenitore-griglie'>";
    
        $result = mysqli_stmt_get_result($statement);

        if($searchType === "brano"){
            while($row = mysqli_fetch_assoc($result)){
                $immagineBrano = $row["immagineBrano"];
                $titolo = pulisciStringa($row["titolo"]);
                $sid = $row["sid"];
                $username = $row["username"];
                $uid = $row["utente"];
                echo <<<EOT
                    <div class='contenitore-griglia'>
                        <div class='contenitore-immagine-griglia'>
                            <img src='$immagineBrano' class='immagine-griglia' alt='Immagine di copertina del brano'>
                        </div>
                        <a href='brano.php?sid=$sid' class='titolo-brano'>$titolo</a>
                        <a href='profilo.php?uid=$uid' class='utente-brano'>$username</a>    
                    </div> 
                EOT;
            }
        }
        else{
            while($row = mysqli_fetch_assoc($result)){
                $immagineUtente = $row["immagineUtente"];
                $username = $row["username"];
                $uid = $row["uid"];
                echo <<<EOT
                    <div class='contenitore-griglia'>
                        <div class='contenitore-immagine-griglia'>
                            <img src='$immagineUtente' class='immagine-griglia' alt="Immagine dell'utente">
                        </div>
                        <a href='profilo.php?uid=$uid' class='utente-brano'>$username</a>    
                    </div> 
                EOT;
            }
        }

        echo "</div>";
        
    }             

?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Ricerca</title>
        <link rel="icon" href="../imgs/icona.ico"> 
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
        <?php
            include_once "header.php";
        ?>
        <main id="main-ricerca">
            <div id="brani-caricati">
                <?php
                    caricaRisultati();
                    mysqli_close($connection);
                ?>  
            </div>
            <div id="contenitore-filtri">
                <?php
                    echo "<a href='ricerca.php?search-type=brano&search-query=" . (isset($_GET["search-query"]) ? urlencode($_GET["search-query"]) : '') . "'>Brani</a>";       //faccio encode della search-query (così da non avere problemi con i caratteri speciali, es '&')
                    echo "<a href='ricerca.php?search-type=utente&search-query=" . (isset($_GET["search-query"]) ? urlencode($_GET["search-query"]) : '') . "'>Utenti</a>";
                ?>
            </div>
        </main>
        
        <?php
            include_once "footer.php";
        ?>
    </body>
</html>
