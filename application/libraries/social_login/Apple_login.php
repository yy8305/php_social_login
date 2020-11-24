<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Firebase\JWT\JWT;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

require_once APPPATH . "libraries/social_login/Social_login.php";

class Apple_login extends Social_login {
	function __construct()
	{
		parent::__construct();
	}

	protected function _get_authorize_param()
	{
		$param = parent::_get_authorize_param();
		$param['scope'] = "name email";
		$param['response_type'] = "code";
		$param['response_mode'] = "form_post";
		return $param;
	}

	protected function _get_token_param($code)
	{
		$param = parent::_get_token_param($code);
		$param['client_secret'] = $this->_generateJWT($this->social_setting['kid'], $this->social_setting['iss'], $this->social_setting['client_id']);
		unset($param['state']);
		return $param;
	}

	/**
	 * 사용자 프로필 받아오기
	 */
	protected function _get_info( $access_token, $add_param="" )
	{
		return $this->_jwt_token_decode($access_token);
	}

	protected function _jwt_token_decode($jwt_token){
		$curl = curl_init($this->social_setting['key_url']);

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);

		if ($info['http_code'] != 200) {
			return null;
		}
		$response = json_decode($response, true);
		$public_keys = $response['keys'];

		if ($public_keys === null) {
			return null;
		}

		$last_key = end($public_keys);
		foreach($public_keys as $data) {
			try {
				//decode action
				$public_key = $this->_create_jwk_public_key($data);
				$token = JWT::decode($jwt_token, $public_key, array('RS256'));
				break;
			} catch (Exception $e) {
				if($data === $last_key) {
					return null;
				}
			}
		}

		return $token;
	}

	protected function _create_jwk_public_key($jwk)
	{
		$rsa = new RSA();
		$rsa->loadKey(
			[
				'e' => new BigInteger(JWT::urlsafeB64Decode($jwk['e']), 256),
                'n' => new BigInteger(JWT::urlsafeB64Decode($jwk['n']),  256)
            ]
        );
        $rsa->setPublicKey();

        return $rsa->getPublicKey();
    }

	public function _jwt_decode($jwt, $key = null, $verify = true)
	{
		$tks = explode('.', $jwt);
		if (count($tks) != 3) {
			throw new UnexpectedValueException('Wrong number of segments');
		}
		list($headb64, $payloadb64, $cryptob64) = $tks;
		if (null === ($header = $this->jsonDecode($this->urlsafeB64Decode($headb64)))
		) {
			throw new UnexpectedValueException('Invalid segment encoding');
		}
		if (null === $payload = $this->jsonDecode($this->urlsafeB64Decode($payloadb64))
		) {
			throw new UnexpectedValueException('Invalid segment encoding');
		}
		$sig = $this->urlsafeB64Decode($cryptob64);
		if ($verify) {
			if (empty($header->alg)) {
				throw new DomainException('Empty algorithm');
			}
			if ($sig != $this->sign("$headb64.$payloadb64", $key, $header->alg)) {
				throw new UnexpectedValueException('Signature verification failed');
			}
		}
		return $payload;
	}

	private function _encode($data) {
		$encoded = strtr(base64_encode($data), '+/', '-_');
		return rtrim($encoded, '=');
	}

	public function _generateJWT($kid, $iss, $sub) {
		$header = [
			'kid' => $kid,
			'alg' => 'ES256'
		];
		$body = [
			'iss' => $iss,
			'iat' => time(),
			'exp' => time() + 3600,
			'aud' => 'https://appleid.apple.com',
			'sub' => $sub
		];

		$privKey = openssl_pkey_get_private(file_get_contents("key file 확장자(.pem)"));
		if (!$privKey){
			return false;
		}

		$payload = $this->_encode(json_encode($header)).'.'.$this->_encode(json_encode($body));

		$signature = '';
		$success = openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
		if (!$success) return false;

		$raw_signature = $this->_fromDER($signature, 64);

		return $payload.'.'.$this->_encode($raw_signature);
	}

	/**
	 * @param string $der
	 * @param int    $partLength
	 *
	 * @return string
	 */
	private function _fromDER($der, $partLength)
	{
		$hex = unpack('H*', $der)[1];
		if ('30' !== mb_substr($hex, 0, 2, '8bit')) { // SEQUENCE
			throw new \RuntimeException();
		}
		if ('81' === mb_substr($hex, 2, 2, '8bit')) { // LENGTH > 128
			$hex = mb_substr($hex, 6, null, '8bit');
		} else {
			$hex = mb_substr($hex, 4, null, '8bit');
		}
		if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
			throw new \RuntimeException();
		}
		$Rl = hexdec(mb_substr($hex, 2, 2, '8bit'));
		$R = $this->_retrievePositiveInteger(mb_substr($hex, 4, $Rl * 2, '8bit'));
		$R = str_pad($R, $partLength, '0', STR_PAD_LEFT);
		$hex = mb_substr($hex, 4 + $Rl * 2, null, '8bit');
		if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
			throw new \RuntimeException();
		}
		$Sl = hexdec(mb_substr($hex, 2, 2, '8bit'));
		$S = $this->_retrievePositiveInteger(mb_substr($hex, 4, $Sl * 2, '8bit'));
		$S = str_pad($S, $partLength, '0', STR_PAD_LEFT);
		return pack('H*', $R.$S);
	}
	/**
	 * @param string $data
	 *
	 * @return string
	 */
	private function _preparePositiveInteger($data)
	{
		if (mb_substr($data, 0, 2, '8bit') > '7f') {
			return '00'.$data;
		}
		while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') <= '7f') {
			$data = mb_substr($data, 2, null, '8bit');
		}
		return $data;
	}
	/**
	 * @param string $data
	 *
	 * @return string
	 */
	private function _retrievePositiveInteger($data)
	{
		while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') > '7f') {
			$data = mb_substr($data, 2, null, '8bit');
		}
		return $data;
	}
}