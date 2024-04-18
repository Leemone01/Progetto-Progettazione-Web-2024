<?php
    define('DBHOST', 'localhost');
    define('DBNAME', 'calo_615600');
    define('DBUSER', 'root');
    define('DBPASS', '');

    //esegue il prepared statement ottenuto a partire da query, types e params. Restituisce l'oggetto che rappresenta il prepared statement,
    //così da poter effettuare operazioni successive sul result set. Restituisce false in caso di errore.
    function execute_prepared_statement($connection, $query, $types, ...$params) {
        if($statement = mysqli_prepare($connection, $query)){
            mysqli_stmt_bind_param($statement, $types, ...$params);
            if(mysqli_stmt_execute($statement)){
                return $statement;
            }
        }

        return false;
    }
?>