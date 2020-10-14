<?php

use Jopin172\Pagofacil;

require 'vendor/autoload.php';

/** Debe definir la URL de tu web */
define ('_URI_','https://tunombrededominio/tudirectorio');

if(isset($_POST)&&!empty($_POST)){
  /**
   * Aquí lo que hago es capturar el resultado, de igual forma lo puedes implementar según la
   * lógica de tu proyecto.
   */
  
  echo '<h1>Resultado:</h1>';

  $pagofacil = new Pagofacil();
  $pagofacil->response();
}else{
  /**
   * Aquí lo que hago es generar el trxs, esto lo puede implementar conforme
   * a la lógica de tu proyecto
   */
  $pagofacil = new Pagofacil();
  $pagofacil->start();
}
