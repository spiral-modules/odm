<?php
/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */

namespace Spiral\Tests\ODM\Integration;

use Mockery as m;
use MongoDB\Driver\Manager;
use Spiral\Core\Container;
use Spiral\ODM\Document;
use Spiral\ODM\Entities\MongoDatabase;
use Spiral\ODM\MongoManager;
use Spiral\ODM\ODM;
use Spiral\ODM\ODMInterface;
use Spiral\ODM\Schemas\SchemaBuilder;
use Spiral\Tests\Core\Fixtures\SharedComponent;
use Spiral\Tests\ODM\Fixtures\Admin;
use Spiral\Tests\ODM\Fixtures\DataPiece;
use Spiral\Tests\ODM\Fixtures\User;
use Spiral\Tests\ODM\Traits\ODMTrait;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    use ODMTrait;

    const MODELS = [User::class, Admin::class, DataPiece::class];

    private $skipped;

    /**
     * @var MongoDatabase
     */
    protected $database;

    /**
     * @var MongoDatabase
     */
    static private $staticDatabase;

    /**
     * @var ODM
     */
    protected $odm;

    protected $container;

    public function setUp()
    {
        $this->odm = $this->realODM(static::MODELS);
        $this->database = self::$staticDatabase;

        SharedComponent::shareContainer($this->container);
    }

    public function tearDown()
    {
        if (!$this->skipped) {
            /**
             * ATTENTION, DATABASE WILL BE CLEAN AFTER TESTS!
             */
            foreach (self::$staticDatabase->listCollections() as $collection) {
                $collection = self::$staticDatabase->selectCollection($collection->getName());

                if (strpos($collection->getCollectionName(), 'system.') === 0) {
                    continue;
                }

                //Do not even think to test it on real server with real config!
                $collection->drop();
            }
        }

        SharedComponent::shareContainer(null);
    }

    protected function realODM(array $models)
    {
        $manager = m::mock(MongoManager::class);
        $manager->shouldReceive('database')->with(null)->andReturn(
            self::$staticDatabase ?? self::$staticDatabase = new MongoDatabase(
                new Manager('mongodb://localhost:27017'),
                'phpunit'
            )
        );

        $this->container = new Container();

        $odm = new ODM($manager, null, null, $this->container);
        $builder = new SchemaBuilder($manager);

        $this->container->bind(ODMInterface::class, $odm);

        foreach ($models as $model) {
            $builder->addSchema($this->makeSchema($model));
        }

        $odm->setSchema($builder);

        return $odm;
    }

    protected function fromDB(Document $document): Document
    {
        return $this->odm->source(get_class($document))->findByPK($document->primaryKey());
    }
}