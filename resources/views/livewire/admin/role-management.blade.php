<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showPermissionModal = false;
    public $selectedRole = null;
    public $roleName = '';
    public $rolePermissions = [];
    
    public function mount()
    {
        $this->authorize('role.view');
    }
    
    public function with()
    {
        return [
            'roles' => Role::with('permissions')->paginate(10),
            'permissions' => Permission::all(),
        ];
    }
    
    public function createRole()
    {
        $this->authorize('role.create');
        
        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name',
        ]);
        
        Role::create(['name' => $this->roleName]);
        
        $this->reset(['roleName', 'showCreateModal']);
        session()->flash('message', 'Role created successfully!');
    }
    
    public function editRole($roleId)
    {
        $this->authorize('role.edit');
        
        $this->selectedRole = Role::findOrFail($roleId);
        $this->roleName = $this->selectedRole->name;
        $this->showEditModal = true;
    }
    
    public function updateRole()
    {
        $this->authorize('role.edit');
        
        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name,' . $this->selectedRole->id,
        ]);
        
        $this->selectedRole->update(['name' => $this->roleName]);
        
        $this->reset(['roleName', 'showEditModal', 'selectedRole']);
        session()->flash('message', 'Role updated successfully!');
    }
    
    public function deleteRole($roleId)
    {
        $this->authorize('role.delete');
        
        $role = Role::findOrFail($roleId);
        
        if (in_array($role->name, ['superadmin', 'supervisor', 'student'])) {
            session()->flash('error', 'Cannot delete system roles!');
            return;
        }
        
        $role->delete();
        session()->flash('message', 'Role deleted successfully!');
    }
    
    public function managePermissions($roleId)
    {
        $this->authorize('role.assign');
        
        $this->selectedRole = Role::with('permissions')->findOrFail($roleId);
        $this->rolePermissions = $this->selectedRole->permissions->pluck('id')->toArray();
        $this->showPermissionModal = true;
    }
    
    public function updatePermissions()
    {
        $this->authorize('role.assign');
        
        $this->selectedRole->syncPermissions($this->rolePermissions);
        
        $this->reset(['showPermissionModal', 'selectedRole', 'rolePermissions']);
        session()->flash('message', 'Permissions updated successfully!');
    }
    
    public function closeModals()
    {
        $this->reset(['showCreateModal', 'showEditModal', 'showPermissionModal', 'selectedRole', 'roleName', 'rolePermissions']);
    }
}; ?>
<main>
<div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-yellow-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 rounded-2xl">
    <x-slot name="title">Role Management</x-slot>
    
    <div class="p-6 max-w-7xl mx-auto">
        <!-- Header with Glassmorphism -->
        <div class="backdrop-blur-xl bg-white/30 dark:bg-zinc-800/30 rounded-3xl border border-white/20 dark:border-zinc-700/20 shadow-2xl p-8 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-green-600 to-yellow-600 bg-clip-text text-transparent">
                        Role Management
                    </h1>
                    <p class="text-zinc-600 dark:text-zinc-300 mt-2 text-lg">Manage system roles and permissions with advanced controls</p>
                </div>
                
                @can('role.create')
                <flux:button wire:click="$set('showCreateModal', true)" variant="primary" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Role
                </flux:button>
                @endcan
            </div>
        </div>

        <!-- Flash Messages with Glassmorphism -->
        @if (session()->has('message'))
            <div class="mb-6 backdrop-blur-xl bg-green-100/50 dark:bg-green-900/30 rounded-2xl border border-green-200/30 dark:border-green-700/30 shadow-xl p-4 animate-pulse">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-green-800 dark:text-green-200 font-medium">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 backdrop-blur-xl bg-red-100/50 dark:bg-red-900/30 rounded-2xl border border-red-200/30 dark:border-red-700/30 shadow-xl p-4 animate-pulse">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Roles Table with Advanced Glassmorphism -->
        <div class="backdrop-blur-xl bg-white/40 dark:bg-zinc-800/40 rounded-3xl border border-white/30 dark:border-zinc-700/30 shadow-2xl overflow-hidden">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-green-600/10 to-yellow-600/10 dark:from-green-800/20 dark:to-yellow-800/20 px-8 py-6 border-b border-white/20 dark:border-zinc-700/20">
                <div class="grid grid-cols-4 gap-6 font-semibold text-zinc-700 dark:text-zinc-200 text-sm uppercase tracking-wider">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Role Name
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Permissions
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                        </svg>
                        Created
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                        </svg>
                        Actions
                    </div>
                </div>
            </div>

            <!-- Table Body -->
            <div class="divide-y divide-white/10 dark:divide-zinc-700/20">
                @foreach($roles as $role)
                <div class="px-8 py-6 hover:bg-white/20 dark:hover:bg-zinc-700/20 transition-all duration-300 group">
                    <div class="grid grid-cols-4 gap-6 items-center">
                        <!-- Role Name -->
                        <div class="flex items-center">
                            <div class="relative">
                                <div class="w-4 h-4 rounded-full mr-4 shadow-lg animate-pulse
                                    {{ $role->name === 'superadmin' ? 'bg-gradient-to-r from-red-500 to-red-600' : 
                                       ($role->name === 'supervisor' ? 'bg-gradient-to-r from-yellow-500 to-yellow-600' : 'bg-gradient-to-r from-green-500 to-green-600') }}">
                                </div>
                                <div class="absolute inset-0 w-4 h-4 rounded-full mr-4 opacity-30 animate-ping
                                    {{ $role->name === 'superadmin' ? 'bg-red-500' : 
                                       ($role->name === 'supervisor' ? 'bg-yellow-500' : 'bg-green-500') }}">
                                </div>
                            </div>
                            <span class="font-semibold text-zinc-900 dark:text-white text-lg capitalize group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors duration-300">{{ $role->name }}</span>
                        </div>
                        
                        <!-- Permissions Count -->
                        <div>
                            <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-green-100 to-yellow-100 text-green-800 dark:from-green-900/50 dark:to-yellow-900/50 dark:text-green-200 shadow-lg backdrop-blur-sm border border-green-200/30 dark:border-green-700/30 hover:shadow-xl transition-all duration-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                {{ $role->permissions->count() }} permissions
                            </div>
                        </div>
                        
                        <!-- Created Date -->
                        <div class="text-zinc-600 dark:text-zinc-300 font-medium">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                                </svg>
                                {{ $role->created_at->format('M d, Y') }}
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex space-x-3">
                            @can('role.assign')
                            <button wire:click="managePermissions({{ $role->id }})" 
                                    class="group relative p-3 rounded-xl bg-gradient-to-r from-green-500/20 to-green-600/20 hover:from-green-500/30 hover:to-green-600/30 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 transition-all duration-300 transform hover:scale-110 shadow-lg hover:shadow-xl backdrop-blur-sm border border-green-200/30 dark:border-green-700/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-zinc-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    Manage Permissions
                                </div>
                            </button>
                            @endcan
                            
                            @can('role.edit')
                            <button wire:click="editRole({{ $role->id }})" 
                                    class="group relative p-3 rounded-xl bg-gradient-to-r from-yellow-500/20 to-yellow-600/20 hover:from-yellow-500/30 hover:to-yellow-600/30 text-yellow-600 hover:text-yellow-700 dark:text-yellow-400 dark:hover:text-yellow-300 transition-all duration-300 transform hover:scale-110 shadow-lg hover:shadow-xl backdrop-blur-sm border border-yellow-200/30 dark:border-yellow-700/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-zinc-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    Edit Role
                                </div>
                            </button>
                            @endcan
                            
                            @can('role.delete')
                            @if(!in_array($role->name, ['superadmin', 'supervisor', 'student']))
                            <button wire:click="deleteRole({{ $role->id }})" 
                                    wire:confirm="Are you sure you want to delete this role?"
                                    class="group relative p-3 rounded-xl bg-gradient-to-r from-red-500/20 to-red-600/20 hover:from-red-500/30 hover:to-red-600/30 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-all duration-300 transform hover:scale-110 shadow-lg hover:shadow-xl backdrop-blur-sm border border-red-200/30 dark:border-red-700/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-zinc-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    Delete Role
                                </div>
                            </button>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="px-8 py-6 bg-gradient-to-r from-white/20 to-zinc-50/20 dark:from-zinc-800/20 dark:to-zinc-900/20 border-t border-white/20 dark:border-zinc-700/20 backdrop-blur-sm">
                {{ $roles->links() }}
            </div>
        </div>
    </div>

    <!-- Create Role Modal with Glassmorphism -->
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-zinc-500/75 dark:bg-zinc-900/75 backdrop-blur-sm transition-opacity animate-fade-in" wire:click="closeModals"></div>
            
            <div class="inline-block align-bottom backdrop-blur-xl bg-white/90 dark:bg-zinc-800/90 rounded-3xl border border-white/30 dark:border-zinc-700/30 shadow-2xl text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-slide-up">
                <div class="px-8 py-6">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-green-600 to-yellow-600 bg-clip-text text-transparent flex items-center">
                            <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create New Role
                        </h3>
                        <p class="text-zinc-600 dark:text-zinc-300 mt-2">Add a new role to the system with custom permissions</p>
                    </div>

                    <div class="mb-6">
                        <flux:input wire:model="roleName" label="Role Name" placeholder="Enter role name" />
                    </div>

                    <div class="flex justify-end space-x-3">
                        <flux:button wire:click="closeModals" variant="ghost" class="hover:bg-zinc-100 dark:hover:bg-zinc-700">Cancel</flux:button>
                        <flux:button wire:click="createRole" variant="primary" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 shadow-lg hover:shadow-xl">Create Role</flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Role Modal with Glassmorphism -->
    @if($showEditModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-zinc-500/75 dark:bg-zinc-900/75 backdrop-blur-sm transition-opacity animate-fade-in" wire:click="closeModals"></div>
            
            <div class="inline-block align-bottom backdrop-blur-xl bg-white/90 dark:bg-zinc-800/90 rounded-3xl border border-white/30 dark:border-zinc-700/30 shadow-2xl text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-slide-up">
                <div class="px-8 py-6">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-green-600 to-yellow-600 bg-clip-text text-transparent flex items-center">
                            <svg class="w-6 h-6 mr-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Role
                        </h3>
                        <p class="text-zinc-600 dark:text-zinc-300 mt-2">Update role information and settings</p>
                    </div>

                    <div class="mb-6">
                        <flux:input wire:model="roleName" label="Role Name" placeholder="Enter role name" />
                    </div>

                    <div class="flex justify-end space-x-3">
                        <flux:button wire:click="closeModals" variant="ghost" class="hover:bg-zinc-100 dark:hover:bg-zinc-700">Cancel</flux:button>
                        <flux:button wire:click="updateRole" variant="primary" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 shadow-lg hover:shadow-xl">Update Role</flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Permissions Modal with Advanced Glassmorphism -->
    @if($showPermissionModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-zinc-500/75 dark:bg-zinc-900/75 backdrop-blur-sm transition-opacity animate-fade-in" wire:click="closeModals"></div>
            
            <div class="inline-block align-bottom backdrop-blur-xl bg-white/90 dark:bg-zinc-800/90 rounded-3xl border border-white/30 dark:border-zinc-700/30 shadow-2xl text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full animate-slide-up">
                <div class="px-8 py-6">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-green-600 to-yellow-600 bg-clip-text text-transparent flex items-center">
                            <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                            </svg>
                            Manage Permissions
                        </h3>
                        <p class="text-zinc-600 dark:text-zinc-300 mt-2">
                            Assign permissions to <span class="font-semibold bg-gradient-to-r from-green-600 to-yellow-600 bg-clip-text text-transparent">{{ $selectedRole?->name ?? 'role' }}</span>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 max-h-96 overflow-y-auto mb-6 p-6 backdrop-blur-sm bg-gradient-to-br from-white/20 to-zinc-50/20 dark:from-zinc-700/20 dark:to-zinc-800/20 rounded-2xl border border-white/20 dark:border-zinc-600/20 shadow-inner">
                        @foreach($permissions as $permission)
                        <label class="flex items-center p-4 rounded-xl hover:bg-white/30 dark:hover:bg-zinc-600/30 transition-all duration-200 cursor-pointer group backdrop-blur-sm border border-transparent hover:border-green-200/30 dark:hover:border-green-700/30 shadow-sm hover:shadow-md">
                            <input type="checkbox" 
                                   wire:model="rolePermissions" 
                                   value="{{ $permission->id }}"
                                   class="w-5 h-5 text-green-600 bg-white/50 border-zinc-300 rounded focus:ring-green-500 focus:ring-2 dark:bg-zinc-700/50 dark:border-zinc-600 transition-all duration-200">
                            <span class="ml-3 text-sm font-medium text-zinc-700 dark:text-zinc-200 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors duration-200">
                                {{ $permission->name }}
                            </span>
                            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="flex justify-end space-x-3">
                        <flux:button wire:click="closeModals" variant="ghost" class="hover:bg-zinc-100 dark:hover:bg-zinc-700">Cancel</flux:button>
                        <flux:button wire:click="updatePermissions" variant="primary" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 shadow-lg hover:shadow-xl">Update Permissions</flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slide-up {
    from { 
        opacity: 0;
        transform: translateY(20px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>
</main>