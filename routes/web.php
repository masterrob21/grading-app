<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseUserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::view('/settings', 'setting.index')->name('settings.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('can:view users');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('can:create users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('can:create users');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('can:view users');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('can:edit users');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('can:edit users');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('can:delete users');

    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index')->middleware('can:view departments');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create')->middleware('can:create departments');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store')->middleware('can:create departments');
    Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit')->middleware('can:edit departments');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update')->middleware('can:edit departments');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy')->middleware('can:delete departments');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index')->middleware('can:view students');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create')->middleware('can:create students');
    Route::get('/students/sample-csv', [StudentController::class, 'downloadSampleCsv'])->name('students.sample_csv');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store')->middleware('can:create students');
    Route::post('/students/bulk-upload', [StudentController::class, 'bulkUpload'])->name('students.bulk_upload');
    Route::get('/students/lookup/{student_id}', [StudentController::class, 'showByStudentId'])->name('students.lookup');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show')->middleware('can:view students');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit')->middleware('can:edit students');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update')->middleware('can:edit students');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy')->middleware('can:delete students');
    // Route::get('/students/{student_id}', [StudentController::class, 'showByStudentId'])->name('students.show_by_id');

    Route::get('/academic-years', [AcademicYearController::class, 'index'])->name('academic_years.index')->middleware('can:view academic years');
    Route::get('/academic-years/create', [AcademicYearController::class, 'create'])->name('academic_years.create')->middleware('can:create academic years');
    Route::post('/academic-years', [AcademicYearController::class, 'store'])->name('academic_years.store')->middleware('can:create academic years');
    Route::get('/academic-years/{academic_year}/edit', [AcademicYearController::class, 'edit'])->name('academic_years.edit')->middleware('can:edit academic years');
    Route::put('/academic-years/{academic_year}', [AcademicYearController::class, 'update'])->name('academic_years.update')->middleware('can:edit academic years');
    Route::delete('/academic-years/{academic_year}', [AcademicYearController::class, 'destroy'])->name('academic_years.destroy')->middleware('can:delete academic years');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index')->middleware('can:view courses');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create')->middleware('can:create courses');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store')->middleware('can:create courses');
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit')->middleware('can:edit courses');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update')->middleware('can:edit courses');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy')->middleware('can:delete courses');

    Route::get('/course-users', [CourseUserController::class, 'index'])->name('course_users.index')->middleware('can:view course users');
    Route::get('/course-users/create', [CourseUserController::class, 'create'])->name('course_users.create')->middleware('can:create course users');
    Route::post('/course-users', [CourseUserController::class, 'store'])->name('course_users.store')->middleware('can:create course users');
    Route::get('/course-users/{courseUser}/edit', [CourseUserController::class, 'edit'])->name('course_users.edit')->middleware('can:edit course users');
    Route::put('/course-users/{courseUser}', [CourseUserController::class, 'update'])->name('course_users.update')->middleware('can:edit course users');
    Route::delete('/course-users/{courseUser}', [CourseUserController::class, 'destroy'])->name('course_users.destroy')->middleware('can:delete course users');

    Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index')->middleware('can:view enrollments');
    Route::get('/enrollments/create', [EnrollmentController::class, 'create'])->name('enrollments.create')->middleware('can:create enrollments');
    Route::get('/enrollments/sample-csv', [EnrollmentController::class, 'downloadSampleCsv'])->name('enrollments.sample_csv');
    Route::post('/enrollments/bulk-upload', [EnrollmentController::class, 'bulkUpload'])->name('enrollments.bulk_upload');
    Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store')->middleware('can:create enrollments');
    Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
    Route::get('/enrollments/{enrollment}/edit', [EnrollmentController::class, 'edit'])->name('enrollments.edit')->middleware('can:edit enrollments');
    Route::put('/enrollments/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollments.update')->middleware('can:edit enrollments');
    Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy')->middleware('can:delete enrollments');

    Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index')->middleware('can:view assessments');
    Route::get('/assessments/create', [AssessmentController::class, 'create'])->name('assessments.create')->middleware('can:create assessments');
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store')->middleware('can:create assessments');
    Route::get('/assessments/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
    Route::get('/assessments/{assessment}/edit', [AssessmentController::class, 'edit'])->name('assessments.edit')->middleware('can:edit assessments');
    Route::put('/assessments/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update')->middleware('can:edit assessments');
    Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy')->middleware('can:delete assessments');

    Route::get('/marks', [MarkController::class, 'index'])->name('marks.index')->middleware('can:view marks');
    Route::get('/marks/create', [MarkController::class, 'create'])->name('marks.create')->middleware('can:create marks');
    Route::get('/marks/sample-csv', [MarkController::class, 'downloadSampleCsv'])->name('marks.sample_csv');
    Route::post('/marks/bulk-upload', [MarkController::class, 'bulkUpload'])->name('marks.bulk_upload');
    Route::post('/marks', [MarkController::class, 'store'])->name('marks.store')->middleware('can:create marks');
    Route::get('/marks/{mark}/edit', [MarkController::class, 'edit'])->name('marks.edit')->middleware('can:edit marks');
    Route::put('/marks/{mark}', [MarkController::class, 'update'])->name('marks.update')->middleware('can:edit marks');
    Route::delete('/marks/{mark}', [MarkController::class, 'destroy'])->name('marks.destroy')->middleware('can:delete marks');
    Route::post('/marks/{mark}/request-edit', [MarkController::class, 'requestEdit'])->name('marks.request_edit');

    Route::get('/mark-sheet', [MarkController::class, 'showMarkSheet'])->name('mark.sheet.index');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('can:view roles');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('can:create roles');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('can:create roles');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('can:edit roles');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('can:edit roles');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('can:delete roles');

    Route::get('/role-permissions', [RoleController::class, 'permissionsIndex'])->name('roles.permissions.index')->middleware('can:view role permissions');
    Route::put('/role-permissions/{role}', [RoleController::class, 'permissionsUpdate'])->name('roles.permissions.update')->middleware('can:edit role permissions');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';