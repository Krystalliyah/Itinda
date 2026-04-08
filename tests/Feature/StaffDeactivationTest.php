<?php

use App\Models\InactiveStaff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->vendor = User::factory()->create();
    $this->vendor->assignRole('vendor');
    $this->actingAs($this->vendor);
});

test('deactivating a staff member moves them to inactive table with reason', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $reason = 'No longer needed';

    $this->delete(route('vendor.staff.destroy', $staff), ['reason' => $reason])
        ->assertRedirect();

    $this->assertDatabaseHas('inactive_staff', [
        'user_id' => $staff->id,
        'name' => $staff->name,
        'email' => $staff->email,
        'login_id' => $staff->login_id,
        'reason' => $reason,
        'deactivated_by' => $this->vendor->id,
    ]);

    // Check that staff role is removed
    $staff->refresh();
    expect($staff->hasRole('staff'))->toBeFalse();

    // Check permissions are cleared
    expect($staff->getAllPermissions()->count())->toBe(0);
});

test('cannot deactivate vendor owner', function () {
    $this->delete(route('vendor.staff.destroy', $this->vendor), ['reason' => 'test'])
        ->assertStatus(403);
});

test('deactivation requires reason', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $this->delete(route('vendor.staff.destroy', $staff))
        ->assertSessionHasErrors('reason');
});
