<?php

class BaseCryptCookieSession implements BaseSessionInterface {

  /**
   * @var array
   */
  private $store = array();
  /**
   * @var IConfigReader
   */
  private $config;

  /**
   * @var string
   */
  private $cookie_name;

  /**
   * @param IConfigReader $config
   */
  public function __construct($config) {
    $this->config = $config;
    $this->decodeCryptCookie();

    $this->cookie_name = $this->config->get('cookie.name', 'SecureSessionCookie');
    if($this->config->get('cookie.encrypt_key', null) === null) {
      throw new BaseCryptCookieSessionException('Config key "cookie.encrypt_key" must be set!');
    }
  }

  /**
   * @param string $key
   * @param mixed $default Default value to return when key is not found
   * @return mixed
   */
  public function get($key, $default = null) {
    return array_key_exists($key, $this->store) ? $this->store[$key] : $default;
  }

  /**
   * @param string $key
   * @param mixed $value
   * @return BaseSessionInterface
   */
  public function set($key, $value) {
    $this->store[$key] = $value;
    $this->encodeCryptCookie();
  }
  
  private function decodeCryptCookie() {
    $sSecretKey = $this->config->get('cookie.encrypt_key');
    if(!array_key_exists($this->cookie_name, $_COOKIE)) {
      return;
    }
    $sEncrypted = $_COOKIE[$this->cookie_name];
    $data = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $sSecretKey, base64_decode($sEncrypted), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    $this->store = json_decode($data, true);
  }
  
  private function encodeCryptCookie() {
    $sSecretKey = $this->config->get('cookie.encrypt_key');
    $sDecrypted = json_encode($this->store);
    $data = trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $sSecretKey, $sDecrypted, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    setcookie($this->cookie_name, $data, time() + 31 * 86400, '/');
  }

  /**
   * @param string $key
   * @return boolean
   */
  public function exist($key) {
    return array_key_exists($key, $this->store);
  }

  /**
   * @param string $key
   * @return BaseSessionInterface
   * @throws BaseSessionUndefinedIndexException
   */
  public function clear($key) {
    unset($this->store[$key]);
    $this->encodeCryptCookie();
  }

  /**
   * @return BaseSessionInterface
   */
  public function clear_all() {
    $this->store = array();
    $this->encodeCryptCookie();
  }
}

class BaseCryptCookieSessionException extends Exception {}
