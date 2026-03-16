<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('unverified customer can access customer profile page', function () {
    Role::findOrCreate('customer');

    $user = User::factory()->unverified()->create();
    $user->assignRole('customer');

    $this->actingAs($user)
        ->get(route('customer.profile'))
        ->assertRedirect(route('verification.notice'));
});
