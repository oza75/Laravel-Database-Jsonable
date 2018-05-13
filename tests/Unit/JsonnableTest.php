<?php
/**
 * Created by PhpStorm.
 * User: oza
 * Date: 12/05/18
 * Time: 20:00
 */

namespace Oza\DatabaseJsonnable\Tests;


use App\Posts;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Oza\DatabaseJsonable\Exceptions\InvalidSchema;
use Tests\TestCase;

class JsonnableTest extends TestCase
{
    Use DatabaseMigrations, DatabaseTransactions;

    protected $post;


    protected function setUp()
    {
        parent::setUp();
        $this->post = (new Posts)->create()->first();
    }

    public function testOk()
    {
        $this->assertNotNull($this->post);
    }

    public function testAddItemUsingArray()
    {
        $this->post->contents->add(['title' => 'Hello kitty']);
        $this->assertCount(1, $this->post->contents->all());
        $this->assertTrue($this->post->contents->first()->contains('Hello kitty'));
    }

    public function testAddItemUsingSchema()
    {
        $this->post->actions->add('like', 1234);
        $this->assertNotNull($this->post->actions);
        $this->assertTrue($this->post->actions->first()->contains('like'));
        $this->assertEquals(1234, $this->post->actions->first()['count']);
    }

    public function testAddItemUsingSchemaAndArray()
    {
        $this->post->actions->add(['type' => 'like', 'count' => 1234, 'trailer' => 'avengers infinity war']);
        $this->assertNotNull($this->post->actions);
        $this->assertTrue($this->post->actions->first()->contains('like'));
        $this->assertFalse($this->post->actions->first()->has('trailer'));
        $this->assertEquals(1234, $this->post->actions->first()['count']);
    }

    public function testAddItemUsingNotDefinedSchema()
    {
        $this->expectException(InvalidSchema::class);
        $this->post->contents->add('Avagenrs Infinity wars', 'lorem');
        $this->assertNull($this->post->contents->items);
    }

    public function testChangeValue()
    {
        $id = $this->post->actions->add('like', 12345);
        $this->post->actions->change($id, 'count', 12);
        $this->post->actions->change($id, 'dinosor', true);
        $this->assertEquals(12, $this->post->actions->get($id)['count']);
        $this->assertTrue($this->post->actions->get($id)->has('dinosor'));
        $this->assertEquals(true, $this->post->actions->get($id)['dinosor']);
    }

    public function testUpdate()
    {
        $id = $this->post->actions->add('like', 12345);
        $this->post->actions->update($id, ['count' => 457, 'dinosor' => true]);
        $this->assertEquals(457, $this->post->actions->get($id)['count']);
        $this->assertTrue($this->post->actions->get($id)->has('dinosor'));
        $this->assertEquals(true, $this->post->actions->get($id)['dinosor']);
    }

    public function testRemove()
    {
        $id = $this->post->actions->add('like', 456);
        $this->post->actions->remove($id);
        $this->assertCount(0, $this->post->actions->all());
    }

    public function testAddingWithCreateMethod()
    {
        $data = Posts::create(['actions' => ['type' => 'Avatars Infinity', 'count' => "Hello", 'aka' => 'de']]);
        $this->assertTrue(is_string($data->actions->toJson()));
        $this->assertArrayHasKey('type', $data->actions->first());
        $this->assertArrayNotHasKey('aka', $data->actions->first());
        $this->assertTrue($data->actions->first()->contains('Avatars Infinity'));
    }

    public function testAddingTimestampsField()
    {
        $data = Posts::create(['actions' => ['type' => 'like', 'count' => 1234]]);
        $this->assertNotNull($data);
        $this->assertTrue( $data->actions->first()->has('created_at'));
        $this->assertTrue( $data->actions->first()->has('updated_at'));
    }
    public function testAddingTimestampsFieldWithAddMethod()
    {
        $id = $this->post->actions->add('like',1234);
        $this->assertNotNull($this->post);
        $this->assertTrue($this->post->actions->first()->has('created_at'));
        $this->assertTrue($this->post->actions->first()->has('updated_at'));
        $updated_at = $this->post->actions->first()->has('updated_at');
        $this->post->actions->change($id, 'count', 124);
        $this->assertNotEquals($updated_at, $this->post->actions->first()['updated_at']);

    }
}