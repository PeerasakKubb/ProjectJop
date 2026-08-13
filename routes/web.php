<?php

use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\ArchitectureController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\RfidCardController;
use App\Http\Controllers\SensorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| หน้าบ้าน (Public)
|--------------------------------------------------------------------------
*/
Route::get('/', [FrontController::class, 'home'])->name('home');
Route::get('/features', [FrontController::class, 'features'])->name('features');
Route::get('/about', [FrontController::class, 'about'])->name('about');
Route::get('/courses', [FrontController::class, 'courses'])->name('courses.public');
Route::get('/courses/{course}', [FrontController::class, 'courseShow'])->name('courses.public.show');

/*
|--------------------------------------------------------------------------
| Redirect path เก่า → /admin
|--------------------------------------------------------------------------
*/
Route::redirect('/dashboard', '/admin/dashboard');
Route::redirect('/architecture', '/admin/architecture');
Route::redirect('/attendance', '/admin/attendance');
Route::redirect('/devices', '/admin/devices');
Route::redirect('/sensors', '/admin/sensors');
Route::redirect('/rfid-cards', '/admin/rfid-cards');
Route::redirect('/notifications', '/admin/notifications');
Route::redirect('/profile', '/admin/profile');
Route::redirect('/main', '/admin/dashboard');
Route::get('/main/{path}', function (string $path) {
    return redirect('/admin/'.$path, 301);
})->where('path', '.*');

/*
|--------------------------------------------------------------------------
| หลังบ้าน (/admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/architecture', [ArchitectureController::class, 'index'])->name('architecture');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
    Route::get('/attendance/today', [AttendanceController::class, 'today'])->name('attendance.today');

    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices/turn-on-all', [DeviceController::class, 'turnOnAll'])->name('devices.turn-on-all');
    Route::post('/devices/turn-off-all', [DeviceController::class, 'turnOffAll'])->name('devices.turn-off-all');
    Route::post('/devices/{device}/toggle', [DeviceController::class, 'toggle'])->name('devices.toggle');

    Route::get('/sensors', [SensorController::class, 'index'])->name('sensors.index');
    Route::get('/sensors/latest', [SensorController::class, 'latest'])->name('sensors.latest');
    Route::get('/sensors/chart', [SensorController::class, 'chart'])->name('sensors.chart');
    Route::post('/sensors', [SensorController::class, 'store'])->name('sensors.store');

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/rfid-cards', [RfidCardController::class, 'index'])->name('rfid.index');
        Route::post('/rfid-cards', [RfidCardController::class, 'store'])->name('rfid.store');
        Route::delete('/rfid-cards/{rfidCard}', [RfidCardController::class, 'destroy'])->name('rfid.destroy');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/test', [NotificationController::class, 'test'])->name('notifications.test');

        Route::get('/courses/{course}/progress', [ProgressController::class, 'index'])->name('progress.index');
    });

    Route::resource('courses', CourseController::class);

    Route::get('/courses/{course}/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
    Route::post('/courses/{course}/lessons', [LessonController::class, 'store'])->name('lessons.store');
    Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/lessons/{lesson}/progress', [LessonController::class, 'updateProgress'])->name('lessons.progress');
    Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::delete('/courses/{course}/enroll', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

    Route::post('/courses/{course}/ai/lesson', [AiController::class, 'generateLesson'])->name('ai.lesson');
    Route::post('/courses/{course}/ai/exam', [AiController::class, 'generateExam'])->name('ai.exam');
    Route::post('/exams/{exam}/ai/questions', [AiController::class, 'appendQuestions'])->name('ai.exam.append');

    Route::get('/courses/{course}/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/courses/{course}/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/courses/{course}/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/questions', [ExamController::class, 'storeQuestion'])->name('exams.questions.store');
    Route::delete('/questions/{question}', [ExamController::class, 'destroyQuestion'])->name('exams.questions.destroy');
    Route::post('/exams/{exam}/publish', [ExamController::class, 'togglePublish'])->name('exams.publish');
    Route::get('/exams/{exam}/take', [ExamController::class, 'take'])->name('exams.take');
    Route::post('/exams/{exam}/submit', [ExamController::class, 'submit'])->name('exams.submit');
    Route::get('/exam-attempts/{attempt}/result', [ExamController::class, 'result'])->name('exams.result');

    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SiteSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SiteSettingController::class, 'update'])->name('settings.update');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/telegram-code', [ProfileController::class, 'generateTelegramCode'])->name('profile.telegram.code');
    Route::delete('/profile/telegram', [ProfileController::class, 'unlinkTelegram'])->name('profile.telegram.unlink');
    Route::post('/profile/line-code', [ProfileController::class, 'generateLineCode'])->name('profile.line.code');
    Route::delete('/profile/line', [ProfileController::class, 'unlinkLine'])->name('profile.line.unlink');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
