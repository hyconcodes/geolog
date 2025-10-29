<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Student Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold mb-2">Welcome, {{ Auth::user()->name }}!</h3>
                        <p class="text-gray-600 dark:text-gray-400">Matric No: {{ Auth::user()->matric_no }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Email: {{ Auth::user()->email }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Academic Information -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg">
                            <div class="flex items-center mb-4">
                                <div class="bg-blue-500 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold ml-3">Academic Records</h4>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">View your courses, grades, and academic progress.</p>
                        </div>

                        <!-- Assignments -->
                        <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-lg">
                            <div class="flex items-center mb-4">
                                <div class="bg-green-500 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold ml-3">Assignments</h4>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">Submit assignments and track deadlines.</p>
                        </div>

                        <!-- Schedule -->
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-6 rounded-lg">
                            <div class="flex items-center mb-4">
                                <div class="bg-purple-500 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold ml-3">Class Schedule</h4>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">View your class timetable and upcoming events.</p>
                        </div>

                        <!-- Announcements -->
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-6 rounded-lg">
                            <div class="flex items-center mb-4">
                                <div class="bg-yellow-500 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold ml-3">Announcements</h4>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">Stay updated with important notices and news.</p>
                        </div>

                        <!-- Library -->
                        <div class="bg-red-50 dark:bg-red-900/20 p-6 rounded-lg">
                            <div class="flex items-center mb-4">
                                <div class="bg-red-500 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold ml-3">Library</h4>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">Access digital resources and library services.</p>
                        </div>

                        <!-- Support -->
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-lg">
                            <div class="flex items-center mb-4">
                                <div class="bg-indigo-500 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 110 19.5 9.75 9.75 0 010-19.5z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold ml-3">Student Support</h4>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">Get help with academic and personal matters.</p>
                        </div>
                    </div>

                    <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-lg font-semibold mb-2">Quick Actions</h4>
                        <div class="flex flex-wrap gap-3">
                            <flux:button variant="primary" class="!bg-blue-600 hover:!bg-blue-700 !text-white">
                                View Grades
                            </flux:button>
                            <flux:button variant="outline" class="!border-gray-300 dark:!border-gray-600 !text-gray-700 dark:!text-gray-300">
                                Submit Assignment
                            </flux:button>
                            <flux:button variant="outline" class="!border-gray-300 dark:!border-gray-600 !text-gray-700 dark:!text-gray-300">
                                Contact Supervisor
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>