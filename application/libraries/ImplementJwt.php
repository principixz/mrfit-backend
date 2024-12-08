<?php 
require APPPATH . 'libraries/JWT.php';


Class ImplementJwt{
	PRIVATE $key = "COLEGIO_ARQUITECTOS";

 
	public function GenerarToken($data){
		$jwt = JWT::encode($data, $this->key);
		return $jwt;
	}



	public function DecodeToken($token){

		$decoded = JWT::decode($token, $this->key, array('HS256'));
		$decodeData = (array) $decoded;
		 //echo "hola";
       // print_r($decodeData);
		return $decodeData;
	}


 
}

?>