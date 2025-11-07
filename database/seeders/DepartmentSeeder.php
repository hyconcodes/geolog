<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Computer Science',
            'Information Technology',
            'Software Engineering',
            'Cybersecurity',
            'Data Science',
            'Computer Engineering',
            'Information Systems',
            'Artificial Intelligence',
            'Biochemistry',
            'Microbiology',
            'Industrial Chemistry',
            'Physics',
            'Mathematics',
            'Statistics',
            'Geology',
            'Geophysics',
            'Environmental Science',
            'Civil Engineering',
            'Mechanical Engineering',
            'Electrical/Electronics Engineering',
            'Chemical Engineering'
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['name' => $department]);
        }

        $this->command->info('Departments seeded successfully!');
    }
}
