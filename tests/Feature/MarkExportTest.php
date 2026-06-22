<?php

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Mark;
use App\Models\Student;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function seedMarkExportFixture(User $user): array
{
    Permission::create([
        'name' => 'view marks',
        'guard_name' => 'web',
        'module' => 'mark',
    ]);

    $user->givePermissionTo('view marks');

    $department = Department::create([
        'department_name' => 'Computer Science',
    ]);

    $academicYear = AcademicYear::create([
        'year' => '2026/2027',
        'is_current' => true,
    ]);

    $courseA = Course::create([
        'course_code' => 'CSC101',
        'title' => 'Intro to Computing',
        'semester' => 1,
        'user_id' => $user->id,
    ]);

    $courseB = Course::create([
        'course_code' => 'MTH102',
        'title' => 'Discrete Math',
        'semester' => 1,
        'user_id' => $user->id,
    ]);

    $assessmentA = Assessment::create([
        'course_id' => $courseA->id,
        'title' => 'Midterm',
        'max_score' => 100,
        'weight' => 40,
    ]);

    $assessmentB = Assessment::create([
        'course_id' => $courseB->id,
        'title' => 'Final',
        'max_score' => 100,
        'weight' => 60,
    ]);

    $studentA = Student::create([
        'student_id' => 'STU001',
        'full_name' => 'Alpha Student',
        'department_id' => $department->id,
    ]);

    $studentB = Student::create([
        'student_id' => 'STU002',
        'full_name' => 'Beta Student',
        'department_id' => $department->id,
    ]);

    $enrollmentA = Enrollment::create([
        'student_id' => $studentA->student_id,
        'course_id' => $courseA->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $enrollmentB = Enrollment::create([
        'student_id' => $studentB->student_id,
        'course_id' => $courseB->id,
        'academic_year_id' => $academicYear->id,
    ]);

    Mark::create([
        'enrollment_id' => $enrollmentA->id,
        'assessment_id' => $assessmentA->id,
        'user_id' => $user->id,
        'score' => 86.5,
        'is_locked' => false,
    ]);

    Mark::create([
        'enrollment_id' => $enrollmentB->id,
        'assessment_id' => $assessmentB->id,
        'user_id' => $user->id,
        'score' => 74.25,
        'is_locked' => true,
    ]);

    return [
        'course_id' => $courseA->id,
        'assessment_id' => $assessmentA->id,
    ];
}

test('csv export returns only rows matching selected filters', function () {
    $user = User::factory()->create();
    $filters = seedMarkExportFixture($user);

    actingAs($user);
    $response = get(route('marks.export.csv', $filters));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect($content)
        ->toContain('Student ID')
        ->toContain('STU001')
        ->not->toContain('STU002');
});

test('pdf export returns valid pdf content and honors filters', function () {
    $user = User::factory()->create();
    $filters = seedMarkExportFixture($user);

    actingAs($user);
    $response = get(route('marks.export.pdf', $filters));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');

    $content = $response->getContent();

    expect($content)
        ->toStartWith('%PDF-1.4')
        ->toContain('STU001')
        ->not->toContain('STU002');
});