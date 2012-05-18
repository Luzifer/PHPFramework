<?php

  /**
   * CouchDB wrapper
   */
class CouchDB {
  private $host = null;
  private $port = null;
  private $database = null;
  private $config;

  /**
   * @param IConfigReader $config
   * @param string $connection
   */
  public function __construct($config, $connection = 'default') {
    $this->config = $config;
    $this->host = $this->config->get('db.couchdb.' . $connection . '.host', 'localhost');
    $this->port = $this->config->get('db.couchdb.' . $connection . '.port', 5984);
    $this->database = $this->config->get('db.couchdb.' . $connection . '.database', null);

    if($this->config->get('db.couchdb.' . $connection . '.user', null) !== null) {
      $user = $this->config->get('db.couchdb.' . $connection . '.user', null);
      $pass = $this->config->get('db.couchdb.' . $connection . '.password', null);
      $this->host = $user . ':' . $pass . '@' . $this->host;
    }

    if($this->database === null) {
      throw new CouchDBConfigurationError('Configration key "db.couchdb.' . $connection . '.database" is missing.');
    }
  }

  /**
   * @param array $data
   * @param string $method
   * @param null $custom_url
   * @return mixed
   * @throws CouchDBResultException
   */
  private function send($data = null, $method = 'GET', $custom_url = null) {
    $url = 'http://'. $this->host .':'. $this->port .'/'. $this->database;
    $ch = curl_init();
    $options = array(
      CURLOPT_URL => $url
    , CURLOPT_HEADER => 0
    , CURLOPT_RETURNTRANSFER => 1
    , CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    );

    if($method == 'GET') {
      if(is_array($data) && count($data) > 0) {
        $options[CURLOPT_URL] = $options[CURLOPT_URL] .'/'. implode("/", $data);
      }
    }

    if($method == 'POST') {
      if(is_array($data) && count($data) > 0) {
        $options[CURLOPT_CUSTOMREQUEST] = 'POST';
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
      }
    }

    if($method == 'PUT') {
      if(is_array($data)) {
        if(!empty($data['_id'])) {
          $options[CURLOPT_URL] = $options[CURLOPT_URL] .'/'. $data['_id'];
          unset($data['_id']);
        }
        $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
      }
    }

    if($method == 'DELETE') {
      if(!empty($data['id'])) {
        $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        $options[CURLOPT_URL] = $options[CURLOPT_URL] .'/'. $data['id'];
      }
    }
    if($custom_url !== null) {
      $options[CURLOPT_URL] = $custom_url;
    }

    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    $retval = json_decode($result);

    if(!is_object($retval)) {
      throw new CouchDBResultException('No valid result for '. $options[CURLOPT_URL] .': '. $result);
    }
    curl_close($ch);

    if(array_key_exists('error', $retval) && $retval['error'] === 'unauthorized') {
      throw new CouchDBAuthenticationError('Authentication failure: ' . $retval['reason']);
    }

    return $retval;
  }

  /**
   * Create current database from settings file
   *
   * @throws CouchDBAlreadyExistException when the database already exists
   * @throws CouchDBResultException when the database cannot be created
   * @return boolean
   */
  public function create_database() {
    $result = $this->send(array(), 'PUT');
    if(!empty($result->ok) && $result->ok === true) {
      return true;
    } elseif(!empty($result->error) && $result->error == 'file_exists') {
      throw new CouchDBAlreadyExistException('Database already exists');
    } else {
      throw new CouchDBResultException($result->reason);
    }
  }

  /**
   * @param string $category
   * @param array $views
   * @return mixed
   */
  public function create_views($category, $views = array()) {
    $data = array();
    $data['_id'] = '_design/'. $category;
    $data['language'] = 'javascript';
    $data['views'] = array();
    foreach($views as $name => $function) {
      $data['views'][$name] = array('map' => 'function(doc) { '. $function .' }');
    }

    return $this->send($data, 'POST');
  }

  /**
   * @param string $category
   * @param string $name
   * @param string $function
   * @return mixed
   */
  public function create_simple_view($category, $name, $function) {
    return $this->create_views($category, array(
      $name => $function
    ));
  }

  /**
   * Insert a new document to current database
   *
   * @param array $values Array of values
   * @throws CouchDBResultException when insert fails
   * @return string ID of the created document
   */
  public function insert($values) {
    $result = $this->send($values, 'POST');
    if(!empty($result->ok) && $result->ok == 1) {
      return $result->id;
    } else {
      throw new CouchDBResultException($result->reason);
    }
  }

  /**
   * Update a document in the database
   *
   * @param string $id the document ID
   * @param array $values array of values to set/update in the document
   * @throws CouchDBResultException when the update fails
   * @throws CouchDBNotFoundException when the document does not exist
   * @return string the new revision of the document
   */
  public function update($id, $values) {
    $rev = $this->send(array($id));
    if(!empty($rev->_rev)) {
      $values['_id'] = $id;
      $values['_rev'] = $rev->_rev;
      $result = $this->send($values, 'PUT');
      if(!empty($result->ok) && $result->ok === true) {
        return $result->rev;
      } else {
        throw new CouchDBResultException("Failed to update document");
      }
    } elseif(!empty($rev->error) && $rev->error == 'not_found') {
      throw new CouchDBNotFoundException('Document not found');
    }
  }

  /**
   * Delete a document in the database
   *
   * @param string $id
   * @throws CouchDBResultException when deletion fails
   * @throws CouchDBNotFoundException when document does not exist
   * @return boolean true when deletion was successful
   */
  public function delete($id) {
    $rev = $this->send(array($id));
    if(!empty($rev->_rev)) {
      $result = $this->send(array('id' => $id . '?rev='. $rev->_rev), 'DELETE');
      if(!empty($result->ok) && $result->ok === true) {
        return true;
      } else {
        throw new CouchDBResultException($result->reason);
      }
    } elseif(!empty($rev->error) && $rev->error == 'not_found') {
      throw new CouchDBNotFoundException('Document not found');
    }
  }

  /**
   * Get a document in the database
   *
   * @param string $id
   * @throws CouchDBNotFoundException when document does not exist
   * @return object
   */
  public function get($id) {
    $doc = $this->send(array($id));
    if(!empty($doc->_id)) {
      return $doc;
    } elseif(!empty($doc->error) && $doc->error == 'not_found') {
      throw new CouchDBNotFoundException('Document not found');
    }
  }

  /**
   * Get a view from the database
   *
   * @param string $category
   * @param string $name
   * @param bool $descending
   * @throws CouchDBNotFoundException when view does not exist
   * @return object
   */
  public function get_view($category, $name, $descending = false) {
    $suffix = '';
    if($descending === true) {
      $suffix = '?descending=true';
    }
    $doc = $this->send(array('_design', $category, '_view', $name . $suffix));

    if(isset($doc->total_rows)) {
      return $doc;
    } elseif(!empty($doc->error) && $doc->error == 'not_found') {
      throw new CouchDBNotFoundException('Document not found');
    }
  }
}

class CouchDBResultException extends Exception {}
class CouchDBAlreadyExistException extends Exception {}
class CouchDBNotFoundException extends Exception {}
class CouchDBConfigurationError extends Exception {}
class CouchDBAuthenticationError extends Exception {}
