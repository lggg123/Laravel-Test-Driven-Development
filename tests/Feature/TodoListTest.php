<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use App\Models\TodoList;
use Tests\TestCase;

class TodoListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    private $list;
    public function setUp(): void
    {
        // this function runs before everything
        parent::setUp();
        $this->list = $this->createTodoList(['name' => 'my list']);
    }
    public function test_fetch_all_todo_list(): void
    {

        // preparation / prepare
        // whenever you use the create method in laravel you must add the fillable property in the model.
        // reason we use factory is because we have to write the fields, and we will not do so in every test.
        // Therefore, we use a factory to create the fields properly.


        // action / perform
        $response = $this->getJson(route('todo-list.index'));

        // assertion / predict
        $this->assertEquals(1,count($response->json()));
        $this->assertEquals('my list', $response->json()[0]['name']);
    }

    public function test_fetch_single_todo_list()
    {
        // preparation

        // action
        $response = $this->getJson(route('todo-list.show', $this->list->id))
                    ->assertOk()
                    ->json();

        $this->assertEquals($response['name'], $this->list->name);
    }

    public function test_store_new_todo_list()
    {
        // preparation
        $list = TodoList::factory()->create();
        // action
       $response = $this->postJson(route('todo-list.store'),['name' => $list->name])
        ->assertCreated()
        ->json();
        // assertion
        $this->assertEquals($list->name,$response['name']);
        $this->assertDatabaseHas('todo_lists',['name' => $list->name]);
    }

    public function test_while_storing_todo_list_name_field_is_required()
    {
        # code ...
        $this->withExceptionHandling();

        $this->postJson(route('todo-list.store'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_delete_todo_list()
    {
        $this->deleteJson(route('todo-list.destroy',$this->list->id))
            ->assertNoContent();

        $this->assertDatabaseMissing('todo_lists', ['name' => $this->list->name]);
    }

    public function test_update_todo_list()
    {
        $this->patchJson(route('todo-list.update',$this->list->id),['name' => 'my list'])
        ->assertOk();

        $this->assertDatabaseHas('todo_lists',['id' => $this->list->id, 'name' => 'my list']);
    }

    public function test_while_updating_todo_list_name_field_is_required()
    {
        $this->withExceptionHandling();

        $this->patchJson(route('todo-list.update',$this->list->id))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}
