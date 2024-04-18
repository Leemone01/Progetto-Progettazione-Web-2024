<?php
    session_start();
    include_once "dbaccess.php";
    include_once "utils.php";

    $connection = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);   
    if(mysqli_connect_errno()){                                     
        exit(mysqli_connect_error());
    }

    function caricaBrani(){
        global $connection;

        $query = "SELECT * FROM brano INNER JOIN utente ON utente = uid ORDER BY dataRilascio DESC LIMIT 10";                            
        $result = mysqli_query($connection, $query);
        if(!$result){
            echo "<span class='contenitore-errore'>Errore nel recuperare gli ultimi brani caricati.</span>";
            return;
        }

        echo "<h2>Ultimi brani caricati</h2>";
        echo "<div id='contenitore-griglie'>";

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

        echo "</div>";     
    }             

?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>MusicShare</title>
        <link rel="icon" href="../imgs/icona.ico"> 
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
        <?php
            include_once "header.php";
        ?>
        <main>
            <div id="brani-caricati">
                <?php
                    caricaBrani();
                    mysqli_close($connection);
                ?>
            </div>
        </main>
        <?php
            include_once "footer.php";
        ?>
    </body>
</html>
