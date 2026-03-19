<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('unverified vendor is redirected to email verification before vendor dashboard', function () {
    Role::findOrCreate('vendor');

    $vendor = User::factory()->unverified()->create();
    $vendor->assignRole('vendor');

    $this->actingAs($vendor)
        ->get(route('vendor.dashboard'))
        ->assertRedirect(route('verification.notice', absolute: false));
});

test('verified vendor can access vendor dashboard', function () {
    Role::findOrCreate('vendor');

    $vendor = User::factory()->create();
    $vendor->assignRole('vendor');

    $this->actingAs($vendor)
        ->get(route('vendor.dashboard'))
        ->assertOk();
});
