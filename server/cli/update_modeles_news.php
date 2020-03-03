<?php
$db= new DB();
$db->database->beginTransaction();

$query="SELECT * FROM news";
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
    $blocs=json_decode($row['blocs']);
    $delete = $db->database->prepare('DELETE FROM modeles_news WHERE id_news=?');
    $delete->execute(array($row['id']));
    $already=array();
    foreach($blocs as $b){
        if (!in_array($b->id_modele,$already)) {
            $insert = $db->database->prepare('INSERT INTO modeles_news (id_modele,id_news) VALUES (?,?)');
            $insert->execute(array($b->id_modele,$row['id']));
            $already[]=$b->id_modele;
        }
    }
}

$db->database->commit();
WS_maj(array('*'));
echo "\n";
