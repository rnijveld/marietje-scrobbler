<?php

define('APP_LOCATION', __DIR__);
define('ROOT_LOCATION', dirname(__DIR__));
require_once ROOT_LOCATION . '/vendor/autoload.php';

use Doctrine\DBAL\Schema\Schema;

$app = new Marietje\Scrobbler\App();

// generate a schema
$schema = new Schema();

$listeners = $schema->createTable($app['listeners']->table);
$listeners->addColumn('id', 'integer', array('unsigned' => true));
$listeners->addColumn('user', 'string', array('length' => 255));
$listeners->addColumn('session', 'string', array('length' => 255));
$listeners->addColumn('location', 'string', array('length' => 32));
$listeners->addColumn('started', 'integer', array('unsigned' => true));
$listeners->setPrimaryKey(array('id'));

$retrieved = $schema->createTable($app['retrieved']->table);
$retrieved->addColumn('id', 'integer', array('unsigned' => true));
$retrieved->addColumn('artist', 'string', array('length' => 255));
$retrieved->addColumn('title', 'string', array('length' => 255));
$retrieved->addColumn('image', 'string', array('length' => 255, 'notnull' => false));
$retrieved->addColumn('start', 'integer', array('unsigned' => true));
$retrieved->addColumn('length', 'integer', array('unsigned' => true));
$retrieved->addColumn('location', 'string', array('length' => 32));
$retrieved->addColumn('offset', 'integer', array('unsigned' => true));
$retrieved->setPrimaryKey(array('id'));

$scrobbles = $schema->createTable($app['scrobbles']->table);
$scrobbles->addColumn('id', 'integer', array('unsigned' => true));
$scrobbles->addColumn('user', 'string', array('length' => 255));
$scrobbles->addColumn('artist', 'string', array('length' => 255));
$scrobbles->addColumn('title', 'string', array('length' => 255));
$scrobbles->addColumn('start', 'integer', array('unsigned' => true));
$scrobbles->addColumn('sent', 'integer', array('unsigned' => true));
$scrobbles->setPrimaryKey(array('id'));

$ignores = $schema->createTable($app['ignores']->table);
$ignores->addColumn('id', 'integer', array('unsigned' => true));
$ignores->addColumn('user', 'string', array('length' => 255));
$ignores->addColumn('artist', 'string', array('length' => 255));
$ignores->addColumn('title', 'string', array('length' => 255, 'notnull' => false));
$ignores->setPrimaryKey(array('id'));

// do the actual work
try {
    $platform = $app['db']->getDatabasePlatform();
    $sql = $schema->toSql($platform);

    $i = 0;
    $app['db']->beginTransaction();
    foreach ($sql as $query) {
        $app['db']->executeQuery($query);
        $i += 1;
    }
    $app['db']->commit();
} catch (\Exception $e) {
    $app['db']->rollback();
    throw $e;
}
print "Executed $i queries...\n";
