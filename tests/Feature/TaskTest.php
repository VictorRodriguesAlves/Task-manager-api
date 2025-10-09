<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('GET /api/tasks', function () {
    it('returns a list of tasks for the authenticated user', function () {

        //arrange
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        //act
        $response = $this->getJson(route('tasks.index'));


        //assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'title',
                    'description',
                    'status',
                    'user_id',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);

    });

    it('returns an empty array when the user has no tasks', function () {

        //act
        $response = $this->getJson(route('tasks.index'));


        //assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => []
        ]);

    });

    it('returns an unauthorized error if user is not authenticated', function () {
        //arrange
        Auth::logout();

        //act
        $response = $this->getJson(route('tasks.index'));

        //assert
        $response->assertStatus(401);
    });

    it('does not show tasks belonging to other users', function () {

        //arrange
        $taskUser = User::factory()->create();
        Task::factory()->create(['user_id' => $taskUser->id]);

        //act
        $response = $this->getJson(route('tasks.index'));

        //assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => []
        ]);
    });
});

describe('GET /api/tasks/{id}', function () {
    it('can display a specific task for the authenticated user', function () {
        //arrange
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        //act
        $response = $this->getJson(route('tasks.show', ['id' => $task->id]));

        //assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'user_id' => $task->user_id,
                'created_at' => $task->created_at->toJson(),
                'updated_at' => $task->updated_at->toJson(),
            ]
        ]);
    });

    it('returns a not found error when that task does not belong to that user', function () {
        //arrange
        $taskUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $taskUser->id]);

        //act
        $response = $this->getJson(route('tasks.show', ['id' => $task->id]));

        //assert
        $response->assertStatus(404);

    });

    it('returns an unauthorized error if the user is not authenticated', function () {

        //arrange
        Auth::logout();
        //act
        $response = $this->getJson(route('tasks.show', ['id' => 1]));

        //assert
        $response->assertStatus(401);
    });

    it('returns a not found error if the task does not exist', function () {

        //act
        $response = $this->getJson(route('tasks.show', ['id' => 1]));

        //assert
        $response->assertStatus(404);
    });
});

describe('POST /api/tasks', function () {
    it('allows an authenticated user to create a task', function () {

        //arrange
        $data = [
            'title' => 'task title',
            'description' => 'task description',
        ];

        //act
        $response = $this->postJson(route('tasks.store'), $data);

        //assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'user_id',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertDatabaseHas('tasks', $data);
    });

    it('successfully creates a task without a description', function () {

        //arrange
        $data = [
            'title' => 'task title',
        ];

        //act
        $response = $this->postJson(route('tasks.store'), $data);

        //assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'user_id',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertDatabaseHas('tasks', $data);
    });

    it('returns an unauthorized error when creating a task without authentication', function () {
        //arrange
        Auth::logout();

        $data = [
            'title' => 'task title',
            'description' => 'task description',
        ];

        //act
        $response = $this->postJson(route('tasks.store'), $data);

        //assert
        $response->assertStatus(401);

    });

    it('returns a validation error if the title is missing', function () {

        //act
        $response = $this->postJson(route('tasks.store'));

        //assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');

    });
});

describe('PUT /api/tasks/{id}', function () {

    it('allows the task owner to update their own task', function () {
        //arrange
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $data = [
            'title' => 'task title12',
        ];

        //act
        $response = $this->putJson(route('tasks.update', ['id' => $task->id]), $data);


        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'user_id',
                'created_at',
                'updated_at',
            ]
        ]);
        $response->assertJson(['data' => ['title' => 'task title12']]);
        $this->assertDatabaseHas('tasks', $data);
    });

    it('returns an unauthorized error when an unauthenticated user tries to update a task', function () {
        //arrange
        Auth::logout();

        $data = [
            'title' => 'task title',
            'description' => 'task description',
        ];

        //act
        $response = $this->putJson(route('tasks.update', ['id' => 1]), $data);

        //assert
        $response->assertStatus(401);
    });

    it('prevents a user from updating another users task', function () {

        //arrange
        $taskUser = User::factory()->create();

        $task = Task::factory()->create(['user_id' => $taskUser->id]);

        $data = [
            'title' => 'new task title12',
        ];

        //act
        $response = $this->putJson(route('tasks.update', ['id' => $task->id]), $data);

        //assert
        $response->assertStatus(404);

    });

    it('returns a not found error when trying to update a task that does not exist', function () {

        //arrange
        $data = [
            'title' => 'new task title12',
        ];

        //act
        $response = $this->putJson(route('tasks.update', ['id' => 1]), $data);

        //assert
        $response->assertStatus(404);
    });

    it('returns a validation error if the title is sent empty', function () {
        //arrange
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'status' => 'pending',
        ];

        //act
        $response = $this->putJson(route('tasks.update', ['id' => $task->id]), $data);

        //assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    });

    it('returns a validation error for an invalid status value', function () {
        //arrange
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'title' => 'new task title12',
            'status' => 'error',
        ];

        //act
        $response = $this->putJson(route('tasks.update', ['id' => $task->id]), $data);

        //assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    });
});

describe('DELETE /api/tasks/{id}', function () {

    it('allows the task owner to delete their own task', function () {

        //arrange
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        //act
        $response = $this->deleteJson(route('tasks.destroy', ['id' => $task->id]));

        //assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', $task->toArray());

    });

    it('prevents a user from deleting another users task', function () {

        //arrange
        $taskUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $taskUser->id]);

        //act
        $response = $this->deleteJson(route('tasks.destroy', ['id' => $task->id]));

        //assert
        $response->assertStatus(404);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id
        ]);
    });

    it('returns an unauthorized error when an unauthenticated user tries to delete a task', function () {

        //arrange
        Auth::logout();

        //act
        $response =  $this->deleteJson(route('tasks.destroy', ['id' => 1]));

        //act
        $response->assertStatus(401);
    });

    it('returns a not found error when trying to delete a task that does not exist', function () {

        //act
        $response = $this->deleteJson(route('tasks.destroy', ['id' => 1]));

        //assert
        $response->assertStatus(404);
        $this->assertDatabaseMissing('tasks', [
            'id' => 1
        ]);
    });

});