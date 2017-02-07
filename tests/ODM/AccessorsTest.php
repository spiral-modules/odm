<?php
/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */

namespace Spiral\Tests\ODM;

use Spiral\ODM\Accessors\ObjectIDsArray;
use Spiral\ODM\Accessors\StringArray;
use Spiral\Tests\ODM\Fixtures\Accessed;
use Spiral\Tests\ODM\Traits\ODMTrait;

class AccessorsTest extends \PHPUnit_Framework_TestCase
{
    use ODMTrait;

    public function testAccessorConstruction()
    {
        $builder = $this->makeBuilder();
        $odm = $this->makeODM();

        $builder->addSchema($this->makeSchema(Accessed::class));
        $odm->setSchema($builder);

        $entity = $odm->make(Accessed::class, []);
        $this->assertInstanceOf(Accessed::class, $entity);

        $this->assertInstanceOf(StringArray::class, $entity->tags);
        $this->assertInstanceOf(ObjectIDsArray::class, $entity->relatedIDs);
    }

    public function testAccessorWithDefaults()
    {
        $builder = $this->makeBuilder();
        $odm = $this->makeODM();

        $builder->addSchema($this->makeSchema(Accessed::class));
        $odm->setSchema($builder);

        $entity = $odm->make(Accessed::class, [
            'tags' => ['a', 'b', 'c']
        ]);
        $this->assertInstanceOf(Accessed::class, $entity);

        $this->assertInstanceOf(StringArray::class, $entity->tags);
        $this->assertCount(3, $entity->tags);

        $this->assertTrue($entity->tags->has('a'));
        $this->assertTrue($entity->tags->has('b'));
        $this->assertTrue($entity->tags->has('c'));
        $this->assertFalse($entity->tags->has('d'));
    }

    public function testArrayAccessorAdd()
    {
        $builder = $this->makeBuilder();
        $odm = $this->makeODM();

        $builder->addSchema($this->makeSchema(Accessed::class));
        $odm->setSchema($builder);

        $entity = $odm->make(Accessed::class, [
            'tags' => ['a', 'b', 'c']
        ]);
        $this->assertInstanceOf(Accessed::class, $entity);

        $this->assertInstanceOf(StringArray::class, $entity->tags);
        $this->assertCount(3, $entity->tags);

        $this->assertTrue($entity->tags->has('a'));
        $this->assertTrue($entity->tags->has('b'));
        $this->assertTrue($entity->tags->has('c'));

        $entity->tags->add('d');
        $entity->tags->add(null);

        $this->assertTrue($entity->tags->has('d'));
        $this->assertFalse($entity->tags->has(null));
    }

    public function testArrayAccessorPull()
    {
        $builder = $this->makeBuilder();
        $odm = $this->makeODM();

        $builder->addSchema($this->makeSchema(Accessed::class));
        $odm->setSchema($builder);

        $entity = $odm->make(Accessed::class, [
            'tags' => ['a', 'b', 'c']
        ]);
        $this->assertInstanceOf(Accessed::class, $entity);

        $this->assertInstanceOf(StringArray::class, $entity->tags);
        $this->assertCount(3, $entity->tags);

        $this->assertTrue($entity->tags->has('a'));
        $this->assertTrue($entity->tags->has('b'));
        $this->assertTrue($entity->tags->has('c'));

        $entity->tags->pull('c');
        $entity->tags->pull(null);
        $this->assertFalse($entity->tags->has('c'));
    }

    public function testSerilization()
    {
        $builder = $this->makeBuilder();
        $odm = $this->makeODM();

        $builder->addSchema($this->makeSchema(Accessed::class));
        $odm->setSchema($builder);

        $entity = $odm->make(Accessed::class, [
            'tags' => ['a', 'b', 'c']
        ]);

        $entity->tags->pull('c');

        $this->assertInternalType('array', $entity->tags->__debugInfo());
        $this->assertSame(['a', 'b'], $entity->tags->jsonSerialize());

        $entity->tags->solidState(true);
        $this->assertTrue($entity->tags->isSolid());
        $entity->tags->solidState(false);
        $this->assertFalse($entity->tags->isSolid());

        $tags = clone $entity->tags;
        $this->assertTrue($tags->isSolid());

        $result = [];
        foreach ($entity->tags as $tag) {
            $result[] = $tag;
        }

        $this->assertSame(['a', 'b'], $result);
    }

    /**
     * @expectedException \Spiral\ODM\Exceptions\AccessException
     * @expectedExceptionMessage No such property 'test' in 'Spiral\Tests\ODM\Fixtures\Accessed',
     *                           check schema being relevant
     */
    public function testException()
    {
        $builder = $this->makeBuilder();
        $odm = $this->makeODM();

        $builder->addSchema($this->makeSchema(Accessed::class));
        $odm->setSchema($builder);

        $entity = $odm->make(Accessed::class, [
            'tags' => ['a', 'b', 'c']
        ]);

        $entity->test->pull('c');
    }
}