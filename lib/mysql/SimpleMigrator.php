<?php

require_once dirname(__FILE__) . '/MySQL.php';

class SimpleMigrator {
  private $connection;
  private $migration_directory;

  /**
   * @param IConfigReader $config Config object containing the database config
   * @param string $migration_directory Path to the directory with the migration files
   * @param string $connection_target Name of the database connection to read the settings from
   */
  public function __construct($config, $migration_directory, $connection_target = 'default') {
    $this->connection = new MySQL($config, $connection_target);
    $this->migration_directory = $migration_directory;

    if(!is_dir($migration_directory)) {
      throw new SimpleMigratorException('Migration directory does not exist or is not a directory.');
    }
  }

  /**
   * @return int
   */
  private function get_current_migration_version() {
    if(count($this->connection->query("SHOW TABLES LIKE 'migration_ver'")) < 1) {
      return 0;
    }

    return $this->connection->field('migration_ver', 'version');
  }

  /**
   * @param int $version
   */
  private function set_current_migration_version($version) {
    if(count($this->connection->query("SHOW TABLES LIKE 'migration_ver'")) < 1) {
      $sql = "CREATE TABLE IF NOT EXISTS `migration_ver` (`version` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
      $this->connection->execute($sql);
    }

    if($this->connection->count('migration_ver') < 1) {
      $this->connection->insert('migration_ver', array('version' => $version));
    } else {
      $this->connection->update('migration_ver', array('version' => $version), '1 = 1');
    }
  }

  /**
   * @param string $filename
   * @throws SimpleMigratorException when any command of the file is not executable
   */
  private function execute_statement_file($filename) {
    $this->connection->execute('SET autocommit = 0;');
    $this->connection->execute('START TRANSACTION;');

    $f = @fopen($filename, "r");
    if($f === false) {
      throw new SimpleMigratorException('Unable to open file "' . $filename . '"');
    }
    $sqlFile = fread($f, filesize($filename));
    $sqlArray = explode(';', $sqlFile);
    foreach($sqlArray as $stmt) {
      if(strlen($stmt) > 3 && substr(ltrim($stmt), 0, 2) != '/*') {
        try {
          $this->connection->execute($stmt);
        } catch(DBQueryException $ex) {
          $this->connection->execute('ROLLBACK;');
          throw new SimpleMigratorException('An error occured while executing query of "' . $filename . '": "' . $stmt . '"');
        }
      }
    }

    $this->connection->execute('COMMIT;');
    $this->connection->execute('SET autocommit = 1;');
  }

  public function migrate() {
    $migration_files = array();
    $matches = array();
    if($handle = opendir($this->migration_directory)) {
      while($file = readdir($handle)) {
        if(preg_match('/^([0-9]+)_.*\.sql$/', $file, $matches) > 0) {
          $migration_files[(int)$matches[1]] = $file;
        }
        if(preg_match('/^([0-9]+)\.sql$/', $file, $matches) > 0) {
          $migration_files[(int)$matches[1]] = $file;
        }
      }
    }

    $this->connection->connect();
    do {
      $next_migration = $this->get_current_migration_version() + 1;
      if(!array_key_exists($next_migration, $migration_files)) {
        break;
      }
      $next_path = rtrim($this->migration_directory, '/') . '/' . $migration_files[$next_migration];
      if(file_exists($next_path) && is_file($next_path)) {
        $this->execute_statement_file($next_path);
        $this->set_current_migration_version($next_migration);
      }
    } while(file_exists($next_path));
    $this->connection->disconnect();
  }

}

class SimpleMigratorException extends Exception {}
