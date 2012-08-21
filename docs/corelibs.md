# Usage

All core libraries are loaded by the autoloader then registered using this command:

    BaseAutoLoader::register_base_lib('library_name');

- The name of the library is written in lower case letters for every library. You also can
look at the folder called "lib" in the framework. All folder names in it are base libraries.
- The file names inside the base library folders are also the class names for that class.

# MySQL

This library provides the two classes `MySQL` and `SimpleMigrator` to connect to your
MySQL database by using configuration names following a defined pattern. Also with the
migrator it is possible to load .sql files from one folder and automaticaly update the
structure of your database without any manual action.

## Documentation

- [MySQL](http://integration.kserver.biz/job/PHPFramework_Subdir/javadoc/default/MySQL.html)
- [SimpleMigrator](http://integration.kserver.biz/job/PHPFramework_Subdir/javadoc/default/SimpleMigrator.html)

## Examples

    BaseAutoLoader::register_base_lib('mysql');

    $config = new ConfigIni('myapp/settings.ini', 'settings_local.ini');

    $migrator = new SimpleMigrator($config, dirname(__FILE__) . '/myapp/migrations/');
    if($config->get('enable_migration', false)) {
      $migrator->migrate();
    }

    $dispatcher = new Dispatcher(
        $config
      , 'myapp'
    );

# CouchDB

This library provides the class `CouchDB` to access your CouchDB instance with or without
password. The database configuration is read by connection names from the configuration.

## Documentation

- [CouchDB](http://integration.kserver.biz/job/PHPFramework_Subdir/javadoc/default/CouchDB.html)

# CloudControl

This library provides access to the `CloudcontrolCredentialReader` to redirect database
connections to the configured credentials in your credential file set by CloudControl.
Only the default MySQL and CouchDB connections are redirected to the databases defined
by CloudControl. All other connections are still read from your own configuration. In
case you have both MySQL addons (dedicated and shared) enabled, the dedicated database
will be used.

## Documentation

- [CloudcontrolCredentialReader](http://integration.kserver.biz/job/PHPFramework_Subdir/javadoc/default/CloudcontrolCredentialReader.html)

## Examples

    BaseAutoLoader::register_base_lib('cloudcontrol');

    $baseconfig = new ConfigIni('myapp/settings.ini', 'settings_local.ini');
    $config = new CloudcontrolCredentialReader($baseconfig);

    $dispatcher = new Dispatcher(
        $config
      , 'myapp'
    );

# SimpleAuth

This library provides access to the `SimpleAuthHandler` to have a simple basic authentication
in your handlers. You just have to extend this class in your handlers and make sure to execute
its constructor. The username -> password mapping is configured in your settings file. You can
find an example in the docs. Every password has to be an sha1sum of the real password.

## Documentation

- [SimpleAuthHandler](http://integration.kserver.biz/job/PHPFramework_Subdir/javadoc/default/SimpleAuthHandler.html)
