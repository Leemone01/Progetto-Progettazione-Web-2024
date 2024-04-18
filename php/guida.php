<?php
    session_start();       
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Guida</title>
        <link rel="icon" href="../imgs/icona.ico"> 
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
        <?php
            include_once "header.php";
        ?>
        <main>
            <h1 class="titolo-guida">Che cos'è MusicShare?</h1>
            <p><strong>MusicShare</strong> è un sito di condivisione di brani musicali.</p>
            <p>La <strong>pagina principale</strong> mostra gli ultimi brani caricati sul sito. Per poterne caricare uno, l’utente deve prima essersi registrato ed aver fatto l’accesso. 
                Questo può farlo cliccando sui due bottoni appositi presenti nella sezione di navigazione in alto a destra. Effettuato l’accesso, la sezione di navigazione cambia, permettendo all’utente di caricare un brano, accedere al proprio profilo o uscire dal sito.
            </p>
            <p>Nella <strong>pagina che permette di caricare un brano,</strong> l’utente può scegliere un <em>titolo</em> per il brano, una breve <em>descrizione</em> e <em>un’immagine di copertina.</em> 
                Non appena verrà scelta un’immagine, a sinistra verrà mostrata un’anteprima di come apparirà sul sito, così che l’utente possa eventualmente sceglierne un’altra se non fosse soddisfatto. </p>
            <p><strong>Una volta caricato un brano, sarà disponibile una pagina pubblica in cui è possibile ascoltarlo.</strong> E’ possibile anche lasciare un <em>mi piace</em> o un <em>commento,</em> senza che l’ascolto venga interrotto. 
                In questa pagina, l’utente che ha caricato il brano può modificarlo, cliccando sull’apposito bottone.</p>
            <p><strong>Ogni utente ha una pagina pubblica</strong>, in cui sono disponibili i <em>brani</em> che ha caricato. Nella propria pagina personale, l’utente può modificare il proprio profilo, cliccando sull’apposito bottone. 
                L’utente può modificare i dati che ha inserito in fase di registrazione, quali <em>username</em>, <em>email</em> e <em>password</em>, o aggiungerne nuovi, come una breve <em>descrizione</em> e <em>un’immagine di profilo.</em> </p>
            <p>In ogni pagina è presente in alto a sinistra il <strong>logo del sito</strong> che, se cliccato, reindirizza alla pagina principale.</p>
            <p>La <strong>casella di testo in alto</strong> permette la ricerca nel sito. Di default, questa viene effettuata per brano, ma è possibile specificare successivamente il tipo di ricerca cliccando sugli appositi bottoni. </p>
            <p>Tutti gli utenti che si registrano al sito saranno <em>utenti normali</em>. Oltre a questi sono presenti <em>utenti amministratori</em>, che possono modificare anche i brani caricati dagli altri utenti.</p>
            <h2 class="titolo-guida">Crediti contenuti multimediali</h2>
            <p>Tutte le immagini e i brani presenti sul sito sono a licenza libera, e sono stati scaricati da <a href="https://www.pexels.com/">Pexels</a> e <a href="https://pixabay.com/">Pixabay</a>.</p>
        </main>
        <?php
            include_once "footer.php";
        ?>
    </body>
</html>
