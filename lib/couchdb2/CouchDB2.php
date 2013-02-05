<?php

class CouchDB2 {
  private $host = null;
  private $port = null;
  private $database = null;
  private $config;

  /**
   * @param IConfigReader $config
   * @param string $connection
   * @throws CouchDB2ConfigurationError
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
      throw new CouchDB2ConfigurationError('Configration key "db.couchdb.' . $connection . '.database" is missing.');
    }
  }

  // public function allApps() { throw new MethodNotImplementedException(); }

  /**
   * Fetch all the design docs in this db
   *
   * @param array $options
   * @return array
   */
  public function allDesignDocs($options = array()) {
    $options['startkey'] = '_design/';
    $options['endkey'] = '_design0';
    return $this->allDocs($options);
  }

  /**
   * Fetch all the docs in this db, you can specify an array of keys to fetch by passing the keys field in the options
   * parameter.
   *
   * @param array $options
   * @throws CouchDB2NotFoundException
   * @return array
   */
  public function allDocs($options = array()) {
    $options['include_docs'] = 'true';
    $result = $this->send(array('_all_docs'), 'GET', null, $options);
    if(array_key_exists('rows', $result)) {
      return $result['rows'];
    } else {
      throw new CouchDB2NotFoundException('There were no documents in the response.');
    }
  }

  //public function bulkRemove() { throw new MethodNotImplementedException(); }

  /**
   * Save a list of documents
   *
   * @param array $docs List of documents to save
   * @param array $options
   * @throws CouchDB2ResultException
   * @return bool
   */
  public function bulkSave($docs, $options = array()) {
    $failed = array();
    foreach($docs as $doc) {
      try {
        $this->saveDoc($doc, $options);
      } catch(CouchDB2ResultException $ex) {
        $failed[] = $doc['_id'];
      }
    }
    if(count($failed) > 0) {
      throw new CouchDB2ResultException('Storing of these documents failed: ' . join(', ', $failed));
    }
    return true;
  }

  /**
   * Request compaction of the specified database.
   *
   * @param array $options
   * @return bool
   */
  public function compact($options = array()) {
    $url = 'http://'. $this->host .':'. $this->port .'/'. $this->database . '/_compact';
    $result = $this->send(array(), 'POST', $url);
    return (array_key_exists('ok', $result) && $result['ok'] == true);
  }

  /**
   * Compacts the view indexes associated with the specified design document. You can use this in place of the full
   * database compaction if you know a specific set of view indexes have been affected by a recent database change.
   *
   * @param string $groupName
   * @param array $options
   * @return bool
   */
  public function compactView($groupName, $options = array()) {
    $url = 'http://'. $this->host .':'. $this->port .'/'. $this->database . '/_compact/' . $groupName;
    $result = $this->send(array(), 'POST', $url);
    return (array_key_exists('ok', $result) && $result['ok'] == true);
  }

  //public function copyDoc() { throw new MethodNotImplementedException(); }

  /**
   * Create a new database
   *
   * @param array $options
   * @return bool
   * @throws MethodNotImplementedException
   */
  public function create($options = array()) {
    $result = $this->send(array(), 'PUT');
    return (array_key_exists('ok', $result) && $result['ok'] == true);
  }

  /**
   * Deletes the specified database, and all the documents and attachments contained within it.
   *
   * @param array $options
   * @return bool
   */
  public function drop($options = array()) {
    $result = $this->send(array(), 'DELETE');
    return (array_key_exists('ok', $result) && $result['ok'] == true);
  }


  //public function getDbProperty() { throw new MethodNotImplementedException(); }

  /**
   * Gets information about the specified database.
   *
   * @param array $options
   * @return array
   */
  public function info($options = array()) {
    return $this->send(array(), 'GET');
  }

  //public function list_view() { throw new MethodNotImplementedException(); }

  /**
   * Returns the specified doc from the specified db.
   *
   * @param string $docId id of document to fetch
   * @param array $options
   * @return array
   */
  public function openDoc($docId, $options = array()) {
    return $this->send(array($docId), 'GET', null, $options);
  }

  //public function query() { throw new MethodNotImplementedException(); }

  /**
   * Deletes the specified document from the database. You must supply the current (latest) revision and id of the
   * document to delete eg removeDoc({_id:"mydoc", _rev: "1-2345"})
   *
   * @param array $doc Document to delete
   * @param array $options
   * @return bool
   */
  public function removeDoc($doc, $options = array()) {
    $result = $this->send($doc, 'DELETE');
    return (array_key_exists('ok', $result) && $result['ok'] == true);
  }

  /**
   * Create a new document in the specified database, using the supplied JSON document structure. If the JSON structure
   * includes the _id field, then the document will be created with the specified document ID. If the _id field is not
   * specified, a new unique ID will be generated.
   *
   * @param array $doc document to save
   * @param array $options
   * @throws CouchDB2ResultException
   * @return array
   */
  public function saveDoc($doc, $options = array()) {
    $result = $this->send($doc, 'POST', null, $options);
    if(array_key_exists('ok', $result) && $result['ok'] == true) {
      return $this->openDoc($result['id'], $options);
    } else {
      throw new CouchDB2ResultException('Storing of document failed: ' . $result['reason']);
    }
  }

  //public function setDbProperty() { throw new MethodNotImplementedException(); }

  /**
   * Executes the specified view-name from the specified design-doc design document, you can specify a list of keys in
   * the options object to receive only those keys.
   *
   * @param string $name View to run list against
   * @param array $options
   * @throws CouchDB2NotFoundException
   * @return
   */
  public function view($name, $options = array()) {
    $nameparts = explode('/', $name);
    if(count($nameparts) != 2) {
      throw new CouchDB2NotFoundException('Please specify _design/_view');
    }
    $result = $this->send(array('_design', $nameparts[0], '_view', $nameparts[1]), 'GET', null, $options);
    if(array_key_exists('rows', $result)) {
      return $result['rows'];
    } else {
      throw new CouchDB2NotFoundException('There were no documents in the response.');
    }
  }

  /**
   * Cleans up the cached view output on disk for a given view.
   *
   * @param array $options
   * @return bool
   */
  public function viewCleanup($options = array()) {
    $url = 'http://'. $this->host .':'. $this->port .'/'. $this->database . '/_view_cleanup';
    $result = $this->send(array(), 'POST', $url);
    return (array_key_exists('ok', $result) && $result['ok'] == true);
  }

  private function send($data = null, $method = 'GET', $custom_url = null, $parameters = array()) {
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
      $options[CURLOPT_CUSTOMREQUEST] = 'POST';
      if(is_array($data) && count($data) > 0) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
      }
    }

    if($method == 'PUT') {
      $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
      if(is_array($data)) {
        if(!empty($data['_id'])) {
          $options[CURLOPT_URL] = $options[CURLOPT_URL] .'/'. $data['_id'];
          unset($data['_id']);
        }
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
      }
    }

    if($method == 'DELETE') {
      $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
      if(!empty($data['_id']) && !empty($data['_rev'])) {
        $options[CURLOPT_URL] = $options[CURLOPT_URL] .'/'. $data['_id'] . '?rev=' . $data['_rev'];
      }
    }

    if($custom_url !== null) {
      $options[CURLOPT_URL] = $custom_url;
    } else {
      if(is_array($parameters) && count($parameters) > 0) {
        $opts = array();
        foreach($parameters as $key => $value) {
          $opts[] = urlencode($key) . '=' . urlencode($value);
        }
        $options[CURLOPT_URL] = $options[CURLOPT_URL] . '?' . join('&', $opts);
      }
    }

    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    $retval = json_decode($result, true);

    if(!is_array($retval)) {
      throw new CouchDB2ResultException('No valid result for '. $options[CURLOPT_URL] .': '. $result);
    }
    curl_close($ch);

    if(array_key_exists('error', $retval) && $retval['error'] === 'unauthorized') {
      throw new CouchDB2AuthenticationError('Authentication failure: ' . $retval['reason']);
    }

    return $retval;
  }

}


class CouchDB2ResultException extends Exception {}
class CouchDB2AlreadyExistException extends Exception {}
class CouchDB2NotFoundException extends Exception {}
class CouchDB2ConfigurationError extends Exception {}
class CouchDB2AuthenticationError extends Exception {}