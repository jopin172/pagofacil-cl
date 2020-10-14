<?php
namespace Jopin172;
use Exception;
use PagoFacilCore\Transaction;

/**
 * En caso que no se encuentre la dependencia de PagoFacil, la puedes agregar
 * a través de composer copn el siguiente comando: 
 * composer require pstpagofacil/pagofacil-core-php
 * Importante: Esto se realizó indicando que se quería recibir la respuesta
 * por el método POST, y es en referencia a pagofacil.cl
 */

/**
 * ** TARJETAS DE PRUEBAS **
 * Para Crédito
 * VISA
 * Nro. Tarjeta	4051885600446623
 * Año Expiración	Cualquiera
 * Mes Expiración	Cualquiera
 * CVV	123
 * Resultado	APROBADO
 * MASTERCARD
 * Nro. Tarjeta 5186059559590568
 * Año Expiración		Cualquiera
 * Mes Expiración		Cualquiera
 * CVV		123
 * Resultado		RECHAZADO
 ***** Para Redcompra
 * VISA
 * Nro. Tarjeta	4051885600446623
 * Resultado	APROBADO
 * MASTERCARD
 * Nro. Tarjeta		5186059559590568
 * Resultado		RECHAZADO
 * 
 * Para autenticar, el usuario (RUT) a ingresar es 11.111.111-1 y la clave es 123
 */

class Pagofacil{
  
  protected $token_service;
  protected $token_secret;
  protected $url;


  public function __construct()
  {
    /**
     * $url, $token_secret y $token_service deben contener los datos que corresponda
     * al modo que estés utilizando, es decir modo "desarrollo" o modo "producción"
     */
    $this->url = 'https://apis-dev.pgf.cl/trxs';
    $this->token_service = '';
    $this->token_secret = '';
  }

  public function start():void
  {
    /**
     * Esto lo debes implementar según la lógica de tu proyecto, verás si consulta los datos 
     * desde tu base de datos, si se lo pasa desde otra clase y método, como mejor
     * lo consideres
     */
    if(!empty($this->token_service)&&!empty($this->token_secret)){

      if(empty(_URI_)){
        die('Debes indicar la URL de tu sitio web en el archivo principal');
      }

      $x_account_id = strval($this->token_service);
      $x_amount = (int)15000;
      $x_currency = strval("CLP");
      $x_reference = (int)time();
      $x_customer_email = 'johanrivera172@gmail.com';
      $x_session_id = (int)time() . time();
      $x_shop_country = "CL";
      $x_url_complete = _URI_ . "/index.php?param1=complete";
      $x_url_cancel = _URI_ . "/index.php?param1=cancel";
      $x_url_callback = _URI_ . "/index.php?param1=callback";

      $txs = new Transaction();
      $txs->setAccountId($x_account_id);
      $txs->setAmount($x_amount);
      $txs->setCurrency($x_currency);
      $txs->setReference($x_reference);
      $txs->setCustomerEmail($x_customer_email);
      $txs->setSessionId($x_session_id);
      $txs->setShopCountry($x_shop_country);
      $txs->setUrlComplete($x_url_complete);
      $txs->setUrlCancel($x_url_cancel);
      $txs->setUrlCallback($x_url_callback);
      $txs->firmar($this->token_secret);

      $result = $txs->toValidArray(true);
      //var_dump($result);

      $this->createTrx($result);
    }else{
      die('Debe indicar los valores que le corresponde a los tokens en Jopin172/Pagofacil.php, líneas 54 y 55');
    }
  }

  public function createTrx(array $trxBody):void{
    try {
      $ch = curl_init($this->url);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($trxBody));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      //set the content type to application/json
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

      $response = curl_exec($ch);
      if ($response === false) {
        $error = curl_error($ch);
        throw new Exception($error, 1);
      }
      $info = curl_getinfo($ch);
      if (!in_array($info['http_code'], ['200', '400', '401'])) {
        throw new Exception('Unexpected error occurred. HTTP_CODE: ' . $info['http_code'], $info['http_code']);
      } elseif ($info['http_code']) {
        /** Paso el Json a Object
         * Debió haber sido a array, pero al hacer var_dump al $result
         * me devuelve es un objeto, por lo que me da a entender que PagoFacil le aplica
         * el json_encode es a un objeto.
         * De otda la información que me brinda por ahora me interesa son dos
         * $idTrx y $payUrl
         */
        $result=json_decode($response);
        $idTrx  = $result->data->idTrx.' <br>';
        $payUrl = $result->data->payUrl[0]->url;
        $this->registerData($idTrx,$payUrl);
        header('location:'.$payUrl);

      } else {
        die($info['http_code'] . ' - ' . $response);
      }
      curl_close($ch);
    } catch (Exception $e) {
      die('Error: ' . $e->getCode() . ' - ' . $e->getMessage());
    }
  }

  public function registerData(int $idTrx,string $payUrl):void {
    /** Este método lo podemos asociar al pedido o la orden de pago 
     * Podemos guardar el idTrx Creado y la URL del pago, por si por alguna razón
     * falla la conexión o lo que sea, podamos reutilizar la autorización ya creada
     * en pago fácil
    */
  }

  public function response(){
    $param1=isset($_GET['param1']) ? $_GET['param1'] : '';
    /**
     * Los datos que devuelve pagofacil son:
     * x_account_id
     * x_amount
     * x_currency
     * x_gateway_reference
     * x_reference
     * x_result 
     * x_test
     * x_timestamp
     * x_signature
     * 
     * De todos estos datos, considero que se deben usar x_account_id que puedes compararlo
     * con el token_service para verificar que la respuesta enviada realmente te correponde
     * y x_reference, x_gateway_reference, x_timestamp que deberás asociarlo para que tu
     * cliente pueda auditar con facilidad sus relaciones con pagofacil y por supuesto, 
     * x_result, que aunque el param1 me indique complete, no está de más evaluar que su resultado sea
     * completed
     */
    $x_account_id = isset($_POST['x_account_id']) ? $_POST['x_account_id'] : '';
    $x_reference = isset($_POST['x_reference']) ? $_POST['x_reference'] : '';
    $x_gateway_reference = isset($_POST['x_gateway_reference']) ? $_POST['x_gateway_reference'] : '';
    $x_timestamp = isset($_POST['x_timestamp']) ? date('Y-m-d H:i:s',$_POST['x_timestamp']) : '';
    $x_result = isset($_POST['x_result']) ? $_POST['x_result'] : '';

    if($x_account_id==$this->token_service){

      /**
       * Ya los siguientes condicionales de igual forma lo debes implementar según la lógica de 
       * tu proyecto.
       */
        if ($param1 == 'complete' || $param1 == 'callback') {
          /** Estas líneas es solo de prueba, en producción eliminalas por favor  */
          if($param1 == 'callback'){
            /** Creare un archivo incluyendo los datos que requiero para saber si realmente
             * los estoy recibiendo
             */
              $file = fopen("callback.txt", "w");
              fwrite($file,"{$x_reference} - {$x_result} - {$x_gateway_reference}" . PHP_EOL);
              fclose($file);
          }
          /** Hasta aquí te recomiendo eliminar */
          if($x_result == 'completed'){
            die('La Orden '.$x_reference.' ha sido pagada según el código de pagofacil: ' . $x_gateway_reference);
          }else{
            die('El resultado ha sido: ' . $x_result);
          }
        } elseif ($param1 == 'cancel') {
          /** implementa tu lógica según el proyecto */
          die('El cliente ha cancelado la transacción - '.$x_result);
        }

        var_dump($_POST);
    }
    
  }

}