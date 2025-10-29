<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;

new class extends Component {
    public string $name = '';
    public string $matric_no = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'matric_no' => ['required', 'string', 'max:20', 'unique:users'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'unique:users',
                'regex:/^[a-zA-Z]+\.[0-9]+@bouesti\.edu\.ng$/'
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function register()
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'matric_no' => $validated['matric_no'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign student role
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $user->assignRole($studentRole);

        event(new Registered($user));

        auth()->login($user);

        return redirect()->route('student.dashboard');
    }
}; ?>

<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Student Registration')" :description="__('Enter your details below to create your student account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form wire:submit="register" class="flex flex-col gap-6">
            
            <!-- Name -->
            <flux:input
                wire:model="name"
                name="name"
                :label="__('Full Name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Enter your full name')"
            />

            <!-- Matric Number -->
            <flux:input
                wire:model="matric_no"
                name="matric_no"
                :label="__('Matric Number')"
                type="text"
                required
                autocomplete="off"
                :placeholder="__('e.g., 12345')"
                pattern="[0-9]+"
                title="Matric number should contain only numbers"
            />

            <!-- Email Address (Auto-generated based on lastname and matric_no) -->
            <flux:input
                wire:model="email"
                name="email"
                :label="__('Email Address')"
                type="email"
                required
                autocomplete="email"
                placeholder="lastname.matric_no@bouesti.edu.ng"
                pattern="[a-zA-Z]+\.[0-9]+@bouesti\.edu\.ng"
                title="Email format: lastname.matric_no@bouesti.edu.ng"
            />

            <!-- Password -->
            <flux:input
                wire:model="password"
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                wire:model="password_confirmation"
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full !bg-blue-600 hover:!bg-blue-700 !text-white" data-test="register-student-button">
                    {{ __('Create Student Account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Are you a supervisor?') }}</span>
            <flux:link :href="route('supervisor.register')" wire:navigate>{{ __('Register as Supervisor') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>