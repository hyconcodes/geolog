<?php

use App\Livewire\Admin\UserManagement;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(UserManagement::class)
        ->assertStatus(200);
});
