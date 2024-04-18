async function aggiungiCommento() {
    let textareaCommento = document.getElementById('commento');
    let commento = textareaCommento.value;

    //ricavo il sid del brano dall'url. Letto da: https://developer.mozilla.org/en-US/docs/Web/API/URLSearchParams
    let params = new URLSearchParams(window.location.search);       
    let sid = params.get("sid");

    let contenitoreErrore = document.getElementById("contenitore-messaggio-brano");     //tolgo eventuali messaggi di errore già presenti
    while(contenitoreErrore.firstChild){
        contenitoreErrore.firstChild.remove();
    }
    
    let risposta = await fetch('aggiungi_commento.php', {                       //invio commento e sid allo script aggiungi_commento.php
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'commento=' + encodeURIComponent(commento) + '&sid=' + encodeURIComponent(sid)    //faccio encode per non aver problemi con caratteri speciali (es '&')         
    });

    let risultato = await risposta.json();
    if (risultato.success) {                                        //se il commento è stato aggiunto, lo mostro nella pagina
        let contenitoreCommento = document.createElement("div");
        contenitoreCommento.classList.add("contenitore-commento");


        let contenitoreImmagineMiniatura = document.createElement("div");
        contenitoreImmagineMiniatura.classList.add("contenitore-immagine-miniatura");

        let immagineMiniatura = document.createElement("img");
        immagineMiniatura.src = risultato.immagineUtente;
        immagineMiniatura.classList.add("immagine-miniatura");
        immagineMiniatura.alt = "Immagine del profilo";
        contenitoreImmagineMiniatura.appendChild(immagineMiniatura);
        contenitoreCommento.appendChild(contenitoreImmagineMiniatura);


        let corpoCommento = document.createElement("div");

        let headerCommento = document.createElement("span");
        headerCommento.classList.add("header-commento");

        let utente = document.createElement("a");
        utente.href = "profilo.php?uid=" + risultato.uid;    
        utente.textContent = risultato.username;

        let data = document.createElement("span");
        data.textContent = "Appena aggiunto";

        headerCommento.appendChild(utente);
        headerCommento.appendChild(data);
        corpoCommento.appendChild(headerCommento);

        let contenutoCommento = document.createElement("p");
        contenutoCommento.classList.add("commento");
        contenutoCommento.textContent = risultato.commento;

        corpoCommento.appendChild(contenutoCommento);

        contenitoreCommento.appendChild(contenitoreImmagineMiniatura);
        contenitoreCommento.appendChild(corpoCommento);

        let contenitoreAggiungiCommento = document.getElementById("contenitore-aggiungi-commento");
        contenitoreAggiungiCommento.insertAdjacentElement("afterend", contenitoreCommento);

        let numeroCommenti = document.getElementById("numero-commenti");        //incremento poi il numero di commenti nella pagina
        numeroCommenti.textContent++;

        textareaCommento.value = "";        //e svuoto la textarea in cui c'era il commento                                
    } 
    else {                                                        //altrimenti, mostro nella pagina l'errore che si è verificato
        let messaggioErrore = document.createElement("span");
        messaggioErrore.textContent = risultato.err;
        messaggioErrore.classList.add("contenitore-errore");
        contenitoreErrore.appendChild(messaggioErrore);
    }
}


async function toggleMiPiace(){
    let params = new URLSearchParams(window.location.search);                       //ricavo il sid del brano dall'url
    let sid = params.get("sid");

    let contenitoreErrore = document.getElementById("contenitore-messaggio-brano");     //tolgo eventuali messaggi di errore già presenti
    while(contenitoreErrore.firstChild){
        contenitoreErrore.firstChild.remove();
    }

    let risposta = await fetch('toggle_mipiace.php', {                       //invio il sid allo script toggle_mipiace.php
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '&sid=' + encodeURIComponent(sid)             
    });

    let risultato = await risposta.json();
    if (risultato.success) {                                            //se è stato fatto toggle del mipiace con successo
        let bottoneMiPiace = document.getElementById("mipiace");
        let numeroMiPiace = document.getElementById("numero-mipiace");

        if(risultato.azione === "aggiunto"){                        //se il mi piace è stato aggiunto,
            bottoneMiPiace.classList.add("mipiace-aggiunto");       //aggiungo la classe mipiace-aggiunto al bottone del mipiace nella pagina
            numeroMiPiace.textContent++;                            //e incremento il numero di mi piace nella pagina
        }
        else{                                                       //se il mi piace è stato tolto,
            bottoneMiPiace.classList.remove("mipiace-aggiunto");    //rimuovo la classe mipiace-aggiunto al bottone del mipiace nella pagina
            numeroMiPiace.textContent--;                            //e decremento il numero di mi piace nella pagina        
        }

    }
    else{                                                           //se ci sono stati errori, mostro nella pagina l'errore che si è verificato.
        let messaggioErrore = document.createElement("span");
        messaggioErrore.textContent = risultato.err;
        messaggioErrore.classList.add("contenitore-errore");
        contenitoreErrore.appendChild(messaggioErrore);
    }
}

function toggleCommenti(){
    let contenitoreContenitoreCommenti = document.getElementById("contenitore-contenitore-commenti");
    contenitoreContenitoreCommenti.classList.toggle("nascosto");
}


function inizializza(){
    let bottoneAggiungiCommento = document.getElementById("aggiungi-commento");
    bottoneAggiungiCommento.addEventListener("click", aggiungiCommento);

    let bottoneMiPiace = document.getElementById("mipiace");
    bottoneMiPiace.addEventListener("click", toggleMiPiace);

    let bottoneMostraCommenti = document.getElementById("mostra-commenti");
    bottoneMostraCommenti.addEventListener("click", toggleCommenti);

}

document.addEventListener("DOMContentLoaded", inizializza);
