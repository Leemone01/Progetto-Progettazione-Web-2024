<header>
    <div id="contenitore-header-sinistra">
        <a href="index.php" id="href-logo">
            <img src="../imgs/logo.png" id="logo" alt="Logo">
        </a>
        <a href="guida.php">Guida</a>
    </div>
    

    <div id="contenitore-ricerca">
        <form action="ricerca.php?">
            <?php
                $type = (isset($_GET["search-type"])) ? $_GET["search-type"] : "brano";        //mantengo il tipo di ricerca effettuato in precedenza. Se è la prima, ricerco per brano.
                echo "<input type='hidden' name='search-type' value='$type'>";
            ?>
            <input type="search" name="search-query" id="barra-ricerca" placeholder="Cerca un brano o un artista...">
            <button type="submit">Cerca</button>
        </form>

    </div>

    <nav id="nav-utente">
        <?php
            if(isset($_SESSION["uid"])){
                echo "Ciao, " . $_SESSION["username"] . "!";
                echo "<a href='carica_brano.php'>Carica un brano</a>";
                echo "<a href='profilo.php?uid=" . $_SESSION["uid"] . "'>Profilo</a>";
                echo "<a href='esci.php'>Esci</a>";
            }
            else{
                echo '<a href="accesso.php">Accedi</a>';
                echo '<a href="registrazione.php">Registrati</a>';
            }
        ?>
    </nav>
</header>