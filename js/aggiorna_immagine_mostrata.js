const IMG_VALID_EXT = ["jpg", "png"];
const IMG_VALID_MIME = ["image/jpeg", "image/png"];
const IMG_MAX_SIZE = 1000000;

let URLImmagineMostrataOriginale;       //conterrà l'URL dell'immagine mostrata all'inizio.

function aggiornaAnteprimaImmagine(e){
    let immagineMostrata = document.getElementById("immagine-mostrata");

    if(e.target.files[0]){                                            //se l'utente ha inserito una nuova immagine
        let nuovaImmagine = e.target.files[0];
        let nuovaImmagineExt = nuovaImmagine.name.split(".").pop();   //prendo il nome del file, considero l'array ottenuto splittando il nome ogni volta che incontro '.' e prendo l'ultimo elemento
        let nuovaImmagineMime = nuovaImmagine.type;

        let contenitoreMessaggioForm = document.getElementById("contenitore-messaggio-form");        
        while(contenitoreMessaggioForm.firstChild){                                         //tolgo eventuali messaggi già presenti
            contenitoreMessaggioForm.firstChild.remove();
        }
        
        let errore = "";

        if(!IMG_VALID_EXT.includes(nuovaImmagineExt) || !IMG_VALID_MIME.includes(nuovaImmagineMime)){       //controllo estensione e mime-type
            errore = "Estensione e/o mime-type dell'immagine non valido.";
        }
        else if(nuovaImmagine.size > IMG_MAX_SIZE){                                                         //controllo dimensione
            errore = "L'immagine supera la dimensione massima consentita.";
        }

        if(errore !== ""){                                                                         //se c'è un errore, mostro un messaggio
            let contenitoreErrore = document.createElement("span");
            contenitoreErrore.classList.add("contenitore-errore");
            contenitoreErrore.textContent = errore;
            contenitoreMessaggioForm.appendChild(contenitoreErrore);
            return;
        }

        //se tutto va bene, creo un url per l'immagine inserita dall'utente, e lo metto al posto dell'url dell'immagine mostrata.
        //Letto da: https://developer.mozilla.org/en-US/docs/Web/API/URL/createObjectURL_static
        let nuovaImmagineURL = URL.createObjectURL(nuovaImmagine);                      
        immagineMostrata.src = nuovaImmagineURL;                                   
    }
    else{                                                                           //se invece l'utente annulla la scelta dell'immagine,
        immagineMostrata.src = URLImmagineMostrataOriginale;                        //rimetto l'url dell'immagine mostrata che c'era all'inizio
    }
}

function inizializza(){
    URLImmagineMostrataOriginale = document.getElementById("immagine-mostrata").src;        //recupero l'URL dell'immagine mostrata all'inizio

    let immagineForm = document.getElementById("immagine");
    immagineForm.addEventListener("change", aggiornaAnteprimaImmagine);
}

document.addEventListener("DOMContentLoaded", inizializza);
