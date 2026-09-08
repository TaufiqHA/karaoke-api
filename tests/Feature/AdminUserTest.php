<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guest is redirected to login when accessing admin users', function () {
    $response = $this->get('/admin/users');

    $response->assertRedirect('/login');
});

test('regular user is forbidden from accessing admin users', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->get('/admin/users');

    $response->assertStatus(403);
});

test('admin can view users list', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'Admin Utama',
        'username' => 'adminutama',
    ]);

    $user = User::factory()->create([
        'name' => 'Budi Santoso',
        'username' => 'budisantoso',
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertStatus(200);
    $response->assertSee('Kelola Pengguna');
    $response->assertSee('Admin Utama');
    $response->assertSee('Budi Santoso');
    $response->assertSee('budisantoso');
    $response->assertSee('Tambah Pengguna');
});

test('admin can search users by name, username, or email', function () {
    $admin = User::factory()->admin()->create();

    User::factory()->create([
        'name' => 'Joko Anwar',
        'username' => 'jokoanwar',
        'email' => 'joko@example.com',
    ]);

    User::factory()->create([
        'name' => 'Siti Nurhaliza',
        'username' => 'sitinur',
        'email' => 'siti@example.com',
    ]);

    // Search by name
    $resName = $this->actingAs($admin)->get('/admin/users?search=Joko');
    $resName->assertStatus(200);
    $resName->assertSee('Joko Anwar');
    $resName->assertDontSee('Siti Nurhaliza');

    // Search by username
    $resUser = $this->actingAs($admin)->get('/admin/users?search=sitinur');
    $resUser->assertStatus(200);
    $resUser->assertSee('Siti Nurhaliza');
    $resUser->assertDontSee('Joko Anwar');

    // Search by email
    $resEmail = $this->actingAs($admin)->get('/admin/users?search=joko@example.com');
    $resEmail->assertStatus(200);
    $resEmail->assertSee('Joko Anwar');
    $resEmail->assertDontSee('Siti Nurhaliza');
});

test('admin can filter users by role', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'Current Admin',
    ]);

    $adminUser = User::factory()->admin()->create([
        'name' => 'Administrator Dua',
    ]);

    $normalUser = User::factory()->create([
        'name' => 'Pengguna Biasa',
        'role' => UserRole::User,
    ]);

    $resUserRole = $this->actingAs($admin)->get('/admin/users?role=user');
    $resUserRole->assertStatus(200);
    $resUserRole->assertSee('Pengguna Biasa');
    $resUserRole->assertDontSee('Administrator Dua');

    $resAdminRole = $this->actingAs($admin)->get('/admin/users?role=admin');
    $resAdminRole->assertStatus(200);
    $resAdminRole->assertSee('Administrator Dua');
    $resAdminRole->assertDontSee('Pengguna Biasa');
});

test('admin can store a new user', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'User Baru',
        'username' => 'userbaru',
        'email' => 'userbaru@example.com',
        'role' => 'user',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'User Baru',
        'username' => 'userbaru',
        'email' => 'userbaru@example.com',
        'role' => 'user',
    ]);

    $createdUser = User::where('username', 'userbaru')->first();
    expect(Hash::check('password123', $createdUser->password))->toBeTrue();
});

test('user creation fails when required fields are missing or invalid', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->from('/admin/users')->post('/admin/users', [
        'name' => '',
        'username' => '',
        'email' => 'not-an-email',
        'role' => 'invalid-role',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);

    $response->assertRedirect('/admin/users');
    $response->assertSessionHasErrors(['name', 'username', 'email', 'role', 'password']);
});

test('user creation fails when username or email already exists', function () {
    $admin = User::factory()->admin()->create();
    $existing = User::factory()->create([
        'username' => 'existinguser',
        'email' => 'existing@example.com',
    ]);

    $response = $this->actingAs($admin)->from('/admin/users')->post('/admin/users', [
        'name' => 'Duplicate User',
        'username' => 'existinguser',
        'email' => 'existing@example.com',
        'role' => 'user',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/admin/users');
    $response->assertSessionHasErrors(['username', 'email']);
});

test('admin can update an existing user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'name' => 'Old Name',
        'username' => 'oldusername',
        'email' => 'old@example.com',
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
        'name' => 'Updated Name',
        'username' => 'updatedusername',
        'email' => 'updated@example.com',
        'role' => 'admin',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'username' => 'updatedusername',
        'email' => 'updated@example.com',
        'role' => 'admin',
    ]);

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('admin can update a user without changing password', function () {
    $admin = User::factory()->admin()->create();
    $originalPassword = Hash::make('originalpassword');
    $user = User::factory()->create([
        'name' => 'User Keep Password',
        'password' => $originalPassword,
    ]);

    $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
        'name' => 'User Keep Password Updated',
        'username' => $user->username,
        'email' => $user->email,
        'role' => $user->role->value,
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('success');

    $user->refresh();
    expect($user->password)->toBe($originalPassword);
});

test('admin cannot delete themselves', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->from('/admin/users')->delete("/admin/users/{$admin->id}");

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

test('admin can delete another user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}");

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});
