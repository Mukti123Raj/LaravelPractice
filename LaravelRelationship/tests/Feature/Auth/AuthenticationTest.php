<?php

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

uses(RefreshDatabase::class);

describe('Teacher Registration', function () {
    
    test('can view teacher registration form', function () {
        $response = $this->get('/register');
        
        $response->assertStatus(200);
        $response->assertViewIs('register');
    });

    test('can register a new teacher successfully', function () {
        Event::fake();
        
        $teacherData = [
            'role' => 'teacher',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $teacherData);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'teacher',
        ]);

        $this->assertDatabaseHas('teachers', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertAuthenticated();
        
        $response->assertRedirect(route('verification.notice'));
        
        Event::assertDispatched(Registered::class);
    });

    test('teacher registration requires valid role', function () {
        $teacherData = [
            'role' => 'invalid_role',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/register', $teacherData);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
        $this->assertDatabaseMissing('teachers', ['email' => 'john@example.com']);
    });

    test('teacher registration requires name', function () {
        $teacherData = [
            'role' => 'teacher',
            'name' => '',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/register', $teacherData);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    });

    test('teacher registration requires valid email', function () {
        $teacherData = [
            'role' => 'teacher',
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->post('/register', $teacherData);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('users', ['email' => 'invalid-email']);
    });

    test('teacher registration requires unique email', function () {
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $teacherData = [
            'role' => 'teacher',
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/register', $teacherData);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('users', 1);
    });

    test('teacher registration requires password with minimum length', function () {
        $teacherData = [
            'role' => 'teacher',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
        ];

        $response = $this->post('/register', $teacherData);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    });

    test('teacher registration creates both user and teacher records', function () {
        $teacherData = [
            'role' => 'teacher',
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/register', $teacherData);

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('teacher', $user->role);
        $this->assertEquals('Jane Smith', $user->name);

        $teacher = Teacher::where('email', 'jane@example.com')->first();
        $this->assertNotNull($teacher);
        $this->assertEquals('Jane Smith', $teacher->name);
        $this->assertEquals('jane@example.com', $teacher->email);
    });


    test('authenticated user cannot access registration form', function () {
        $user = User::create([
            'name' => 'Test Teacher',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $this->actingAs($user);

        $response = $this->get('/register');

        $response->assertRedirect(route('teacher.index'));
    });

    test('teacher registration redirects authenticated teacher to teacher dashboard', function () {
        $user = User::create([
            'name' => 'Test Teacher',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $this->actingAs($user);

        $response = $this->get('/register');

        $response->assertRedirect(route('teacher.index'));
    });
});

describe('Teacher Login', function () {
    
    beforeEach(function () {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    });
    
    test('can view teacher login form', function () {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('login');
    });

    test('can login as teacher with valid credentials', function () {
        $user = User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        $loginData = [
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ];

        $response = $this->post('/login', $loginData);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teacher.index'));
    });

    test('teacher login redirects to verification notice if email not verified', function () {
        $user = User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
            'email_verified_at' => null,
        ]);

        $loginData = [
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ];

        $response = $this->post('/login', $loginData);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice'));
    });

    test('teacher login requires valid email', function () {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'password123',
            'role' => 'teacher',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    });

    test('teacher login requires email field', function () {
        $loginData = [
            'email' => '',
            'password' => 'password123',
            'role' => 'teacher',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    });

    test('teacher login requires password field', function () {
        $loginData = [
            'email' => 'teacher@example.com',
            'password' => '',
            'role' => 'teacher',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    });

    test('teacher login requires role field', function () {
        $loginData = [
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'role' => '',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['role']);
        $this->assertGuest();
    });

    test('teacher login requires valid role', function () {
        $loginData = [
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'role' => 'invalid_role',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['role']);
        $this->assertGuest();
    });

    test('teacher login fails with incorrect password', function () {
        $user = User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
        ]);

        $loginData = [
            'email' => 'teacher@example.com',
            'password' => 'wrongpassword',
            'role' => 'teacher',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    });

    test('teacher login fails with non-existent email', function () {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    });

    test('teacher login fails with wrong role', function () {
        $user = User::create([
            'name' => 'John Student',
            'email' => 'student@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $loginData = [
            'email' => 'student@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    });

    test('authenticated teacher cannot access login form', function () {
        $user = User::create([
            'name' => 'Test Teacher',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $this->actingAs($user);

        $response = $this->get('/login');

        $response->assertRedirect(route('teacher.index'));
    });

    test('teacher login regenerates session', function () {
        $user = User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        Teacher::create([
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
        ]);

        $loginData = [
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ];

        $response = $this->post('/login', $loginData);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teacher.index'));
        
        $this->get('/teacher')->assertStatus(200);
    });

    test('teacher can logout successfully', function () {
        $user = User::create([
            'name' => 'Test Teacher',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    });

    test('teacher login with student role fails even with correct credentials', function () {
        $user = User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
        ]);

        $loginData = [
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'role' => 'student',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    });
});
