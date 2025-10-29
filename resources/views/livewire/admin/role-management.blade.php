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
        // Authorization removed as requested
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
        // Authorization removed as requested
        
        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name',
        ]);
        
        Role::create(['name' => $this->roleName]);
        
        $this->reset(['roleName', 'showCreateModal']);
        session()->flash('message', 'Role created successfully!');
    }
    
    public function editRole($roleId)
    {
        // Authorization removed as requested
        
        $this->selectedRole = Role::findOrFail($roleId);
        $this->roleName = $this->selectedRole->name;
        $this->showEditModal = true;
    }
    
    public function updateRole()
    {
        // Authorization removed as requested
        
        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name,' . $this->selectedRole->id,
        ]);
        
        $this->selectedRole->update(['name' => $this->roleName]);
        
        $this->reset(['roleName', 'showEditModal', 'selectedRole']);
        session()->flash('message', 'Role updated successfully!');
    }
    
    public function deleteRole($roleId)
    {
        // Authorization removed as requested
        
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
        // Authorization removed as requested
        
        $this->selectedRole = Role::with('permissions')->findOrFail($roleId);
        $this->rolePermissions = $this->selectedRole->permissions->pluck('id')->toArray();
        $this->showPermissionModal = true;
    }
    
    public function updatePermissions()
    {
        // Authorization removed as requested
        
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
<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 rounded-xl">
    <x-slot name="title">Role Management</x-slot>
    
    <div class="p-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4 sm:p-6 lg:p-8 mb-6 lg:mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-zinc-900 dark:text-white">
                        Role Management
                    </h1>
                    <p class="text-zinc-600 dark:text-zinc-300 mt-2 text-sm sm:text-base lg:text-lg">Manage system roles and permissions</p>
                </div>
                
                <flux:button wire:click="$set('showCreateModal', true)" variant="primary" class="w-full sm:w-auto !bg-blue-600 hover:!bg-blue-700 !text-white flex items-center justify-center text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Role
                </flux:button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="mb-4 sm:mb-6 bg-green-100 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-3 sm:p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-green-800 dark:text-green-200 font-medium text-sm sm:text-base">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 sm:mb-6 bg-red-100 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-3 sm:p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-red-800 dark:text-red-200 font-medium text-sm sm:text-base">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Roles Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
            <!-- Desktop Table Header -->
            <div class="hidden lg:block bg-zinc-50 dark:bg-zinc-700 px-4 sm:px-6 lg:px-8 py-4 lg:py-6 border-b border-zinc-200 dark:border-zinc-600">
                <div class="grid grid-cols-4 gap-4 lg:gap-6 font-semibold text-zinc-700 dark:text-zinc-200 text-xs sm:text-sm uppercase tracking-wider">
                    <div>Role Name</div>
                    <div>Permissions</div>
                    <div>Created</div>
                    <div>Actions</div>
                </div>
            </div>

            <!-- Table Body -->
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach($roles as $role)
                <!-- Desktop Layout -->
                <div class="hidden lg:block px-4 sm:px-6 lg:px-8 py-4 lg:py-6 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200">
                    <div class="grid grid-cols-4 gap-4 lg:gap-6 items-center">
                        <div class="font-semibold text-zinc-900 dark:text-white capitalize">{{ $role->name }}</div>
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $role->permissions->count() }} permissions
                            </span>
                        </div>
                        <div class="text-zinc-600 dark:text-zinc-300">{{ $role->created_at->format('M d, Y') }}</div>
                        <div class="flex space-x-2">
                            <flux:button wire:click="managePermissions({{ $role->id }})" variant="primary" class="!bg-green-600 hover:!bg-green-700 !text-white text-sm">
                                Permissions
                            </flux:button>
                            <flux:button wire:click="editRole({{ $role->id }})" variant="primary" class="!bg-yellow-600 hover:!bg-yellow-700 !text-white text-sm">
                                Edit
                            </flux:button>
                            @if(!in_array($role->name, ['superadmin', 'supervisor', 'student']))
                            <flux:button wire:click="deleteRole({{ $role->id }})" wire:confirm="Are you sure you want to delete this role?" variant="danger" class="!bg-red-600 hover:!bg-red-700 !text-white text-sm">
                                Delete
                            </flux:button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Mobile Card Layout -->
                <div class="lg:hidden p-4 sm:p-6 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-zinc-900 dark:text-white capitalize text-lg">{{ $role->name }}</div>
                            <div class="flex space-x-2">
                                <flux:button wire:click="managePermissions({{ $role->id }})" variant="primary" class="!bg-green-600 hover:!bg-green-700 !text-white text-sm">
                                    Permissions
                                </flux:button>
                                <flux:button wire:click="editRole({{ $role->id }})" variant="primary" class="!bg-yellow-600 hover:!bg-yellow-700 !text-white text-sm">
                                    Edit
                                </flux:button>
                                @if(!in_array($role->name, ['superadmin', 'supervisor', 'student']))
                                <flux:button wire:click="deleteRole({{ $role->id }})" wire:confirm="Are you sure you want to delete this role?" variant="danger" class="!bg-red-600 hover:!bg-red-700 !text-white text-sm">
                                    Delete
                                </flux:button>
                                @endif
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="text-zinc-600 dark:text-zinc-300">
                                <span class="font-medium">Permissions:</span> {{ $role->permissions->count() }}
                            </div>
                            <div class="text-zinc-600 dark:text-zinc-300">
                                <span class="font-medium">Created:</span> {{ $role->created_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="px-4 sm:px-6 lg:px-8 py-4 lg:py-6 bg-zinc-50 dark:bg-zinc-700 border-t border-zinc-200 dark:border-zinc-600">
                {{ $roles->links() }}
            </div>
        </div>

    <!-- Create Role Modal -->
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click="closeModals">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 transition-opacity"></div>
            
            <!-- Modal Content -->
            <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10" wire:click.stop>
                <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                    <div class="mb-6">
                        <h3 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-white">Create New Role</h3>
                        <p class="text-zinc-600 dark:text-zinc-300 mt-2 text-sm sm:text-base">Add a new role to the system with custom permissions</p>
                    </div>
 
                    <div class="mb-6">
                        <flux:input 
                            wire:model.defer="roleName" 
                            label="Role Name" 
                            placeholder="Enter role name"
                            class="!border-zinc-300 dark:!border-zinc-600 !bg-white dark:!bg-zinc-700 !text-zinc-900 dark:!text-zinc-100"
                        />
                        @error('roleName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
 
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                        <flux:button type="button" wire:click="closeModals" variant="outline" class="!border-zinc-300 dark:!border-zinc-600 !text-zinc-700 dark:!text-zinc-300 hover:!bg-zinc-50 dark:hover:!bg-zinc-700">
                            Cancel
                        </flux:button>
                        <flux:button type="button" wire:click="createRole" variant="primary" class="!bg-blue-600 hover:!bg-blue-700 !text-white">
                            Create Role
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Role Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click="closeModals">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 transition-opacity"></div>
            
            <!-- Modal Content -->
            <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10" wire:click.stop>
                <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                    <div class="mb-6">
                        <h3 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-white">Edit Role</h3>
                        <p class="text-zinc-600 dark:text-zinc-300 mt-2 text-sm sm:text-base">Update role information and settings</p>
                    </div>
 
                    <div class="mb-6">
                        <flux:input 
                            wire:model.defer="roleName" 
                            label="Role Name" 
                            placeholder="Enter role name"
                            class="!border-zinc-300 dark:!border-zinc-600 !bg-white dark:!bg-zinc-700 !text-zinc-900 dark:!text-zinc-100"
                        />
                        @error('roleName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
 
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                        <flux:button type="button" wire:click="closeModals" variant="outline" class="!border-zinc-300 dark:!border-zinc-600 !text-zinc-700 dark:!text-zinc-300 hover:!bg-zinc-50 dark:hover:!bg-zinc-700">
                            Cancel
                        </flux:button>
                        <flux:button type="button" wire:click="updateRole" variant="primary" class="!bg-blue-600 hover:!bg-blue-700 !text-white">
                            Update Role
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Permissions Modal -->
    @if($showPermissionModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click="closeModals">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 transition-opacity"></div>
            
            <!-- Modal Content -->
            <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full relative z-10" wire:click.stop>
                <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                    <div class="mb-6">
                        <h3 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-white">Manage Permissions</h3>
                        <p class="text-zinc-600 dark:text-zinc-300 mt-2 text-sm sm:text-base">
                            Assign permissions to <span class="font-semibold">{{ $selectedRole?->name ?? 'role' }}</span>
                        </p>
                    </div>
 
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 max-h-64 sm:max-h-96 overflow-y-auto mb-6 p-4 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        @foreach($permissions as $permission)
                        <label class="flex items-center p-3 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-600 cursor-pointer transition-colors duration-200">
                            <input type="checkbox" wire:model.defer="rolePermissions" value="{{ $permission->id }}" class="w-4 h-4 text-blue-600 bg-white dark:bg-zinc-600 border-zinc-300 dark:border-zinc-500 rounded focus:ring-blue-500">
                            <span class="ml-3 text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $permission->name }}</span>
                        </label>
                        @endforeach
                    </div>
 
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                        <flux:button type="button" wire:click="closeModals" variant="outline" class="!border-zinc-300 dark:!border-zinc-600 !text-zinc-700 dark:!text-zinc-300 hover:!bg-zinc-50 dark:hover:!bg-zinc-700">
                            Cancel
                        </flux:button>
                        <flux:button type="button" wire:click="updatePermissions" variant="primary" class="!bg-blue-600 hover:!bg-blue-700 !text-white">
                            Update Permissions
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</main>
