<?php
$params=json_decode(json_encode($_POST));
$hash=$params->hash;
$docs=Forms::generate_docs($hash,$my_session->user->id);
header('location:'.$docs['pdf']);
