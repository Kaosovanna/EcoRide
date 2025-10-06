<?php
// utils.php - fonctions utilitaires simples
function json_input(){
  $data = file_get_contents('php://input');
  return json_decode($data, true) ?: [];
}
function respond($arr){ header('Content-Type: application/json'); echo json_encode($arr); exit; }
function hash_pass($p){ return password_hash($p, PASSWORD_DEFAULT); }
?>
