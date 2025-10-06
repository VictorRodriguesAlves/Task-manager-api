<?php


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

//register tests
it('should register a user successfully when valid data is provided', function () {

    //arrange
    $data = [
        'name' => 'John Doe',
        'email' => 'john@doe.com',
        'password' => 'password',
    ];


    //act
    $response = $this->postJson(route('auth.register'), $data);

    //assert
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'user' => [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ],
        'token'
    ]);
    $this->assertDatabaseHas('users', [
        'name' => $data['name'],
        'email' => $data['email'],
    ]);

});

it('should return a validation error if the email already exists in register', function () {

    //arrange
    \App\Models\User::factory()->create([
        'email' => 'john@doe.com',
    ]);
    $data = [
        'name' => 'John Doe',
        'email' => 'john@doe.com',
        'password' => 'password',
    ];


    //act
    $response = $this->postJson(route('auth.register'), $data);

    //assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');


});

it('should return a validation error if a field is submitted empty in register', function () {

    //arrange
    $data = [
        'name' => '',
        'email' => '',
        'password' => '',
    ];

    //act
    $response = $this->postJson(route('auth.register'), $data);

    //assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'email', 'password']);

});

it('should return a validation error if the password is too short in register', function () {
    //arrange
    $data = [
        'name' => 'John Doe',
        'email' => 'john@doe.com',
        'password' => '1234',
    ];


    //act
    $response = $this->postJson(route('auth.register'), $data);

    //assert
    $response->assertStatus(422);
    $response->assertInvalid([
        'password' => 'The password field must be at least 8 characters.',
    ]);
});


//login tests
it('should authenticate a user and return a token when valid credentials are provided', function () {

    //arrange
    $user = User::factory()->create([
        'email' => 'john@doe.com',
    ]);

    $data = [
        'email' => $user->email,
        'password' => 'password',
    ];


    //act
    $response = $this->postJson(route('auth.login'), $data);

    //assert
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'user' => [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ],
        'token'
    ]);


});

it('should return an authentication error when attempting to log in with an incorrect password', function () {
    //arrange
    $user = User::factory()->create([
        'email' => 'john@doe.com',
    ]);

    $data = [
        'email' => $user->email,
        'password' => 'password1',
    ];


    //act
    $response = $this->postJson(route('auth.login'), $data);

    //assert
    $response->assertStatus(401);
    $response->assertJson([
        'message' => 'Invalid credentials.',
    ]);
});

it('should return an authentication error when attempting to log in with an email that does not exist', function () {

    //arrange
    $data = [
        'email' => 'john@doe.com',
        'password' => 'password',
    ];


    //act
    $response = $this->postJson(route('auth.login'), $data);

    //assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
    $response->assertInvalid([
        'email' => 'The selected email is invalid.',
    ]);

});

it('should return a validation error if a field is submitted empty in login', function () {

    //arrange
    $data = [
        'email' => '',
        'password' => '',
    ];

    //act
    $response = $this->postJson(route('auth.login'), $data);

    //assert
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email', 'password']);
});


//logout tests
it('should successfully log out an authenticated user', function () {

    //arrange
    $user = User::factory()->create();
    $token = $user->createToken('default')->plainTextToken;

    //act
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/logout');


    //assert
    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Logout realizado com sucesso.'
        ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
    ]);
});

it('should return an unauthorized error if an unauthenticated user attempts to log out', function () {
    //act
    $response = $this->postJson('/api/logout');

    // Assert: Verificar se a resposta é 401 Unauthorized
    $response->assertUnauthorized();
});