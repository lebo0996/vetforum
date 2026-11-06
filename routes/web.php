<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\ModeratorController; // <-- Importante: Asegúrate de importar tu nuevo controlador

// -----------------------------------------------------
// Rutas Públicas
// -----------------------------------------------------
Route::get('/', [ForumController::class, 'home'])->name('home');
Route::get('/foro', [ForumController::class, 'index'])->name('forum.index');
Route::get('/medico/{user}', [UserController::class, 'show'])->name('users.public');
Route::get('/directorio-veterinarios', [UserController::class, 'directory'])->name('users.directory');

// -----------------------------------------------------
// Autenticación (Solo invitados)
// -----------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});

// Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// -----------------------------------------------------
// Rutas Protegidas (Solo Usuarios Autenticados)
// -----------------------------------------------------
Route::middleware('auth')->group(function () {
    
    // Muro Social (Privado, solo accesible con sesión iniciada)
    Route::get('/muro', [StatusController::class, 'index'])->name('statuses.index');

    // -----------------------------------------------------
    // Rutas de Verificación de Correo (Candado)
    // -----------------------------------------------------
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('statuses.index')->with('success', '¡Tu correo ha sido verificado con éxito!');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', '¡Enlace de verificación reenviado!');
    })->middleware(['throttle:6,1'])->name('verification.send');


    // -----------------------------------------------------
    // Rutas protegidas que exigen correo verificado (->middleware('verified'))
    // -----------------------------------------------------
    Route::middleware('verified')->group(function () {

        // Foro (Creación, guardado, edición, comentarios y likes)
        Route::get('/foro/crear', [ForumController::class, 'create'])->name('forum.create');
        Route::post('/foro/publicar', [ForumController::class, 'storePost'])->name('forum.storePost');
        Route::get('/foro/{post}/editar', [ForumController::class, 'edit'])->name('forum.edit');
        Route::put('/foro/{post}', [ForumController::class, 'update'])->name('forum.update');
        Route::post('/foro/{post}/comentar', [ForumController::class, 'storeComment'])->name('forum.storeComment');
        Route::post('/foro/{post}/like', [ForumController::class, 'toggleLike'])->name('forum.like');
        Route::post('/poll-options/{option}/vote', [PollController::class, 'vote'])->name('polls.vote');

        // Muro Social (Publicar, republicar, likes, comentarios, editar y eliminar)
        Route::post('/muro', [StatusController::class, 'store'])->name('statuses.store');
        Route::post('/statuses/{status}/repost', [StatusController::class, 'repost'])->name('statuses.repost');
        Route::post('/muro/{status}/like', [StatusController::class, 'toggleLike'])->name('statuses.like');
        Route::post('/muro/{status}/comentar', [StatusController::class, 'storeComment'])->name('statuses.comment');
        Route::delete('/muro/{status}', [StatusController::class, 'destroy'])->name('statuses.destroy');
        Route::put('/statuses/{status}', [StatusController::class, 'update'])->name('statuses.update');

        // Acción de Seguir/Dejar de seguir a un colega
        Route::post('/usuarios/{user}/seguir', [UserController::class, 'toggleFollow'])->name('users.follow');

        // Chat / Mensajes Privados
        Route::get('/mensajes', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/mensajes/{user}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/mensajes/{user}', [ChatController::class, 'store'])->name('chat.store');
        Route::get('/chat/{user}/fetch', [ChatController::class, 'fetch'])->name('chat.fetch');
        Route::post('/chat/{user}/typing', [ChatController::class, 'typing'])->name('chat.typing');

        // Historias (Stories 24h)
        Route::post('/historias', [App\Http\Controllers\StoryController::class, 'store'])->name('stories.store');
        Route::post('/historias/{story}/vistas', [App\Http\Controllers\StoryController::class, 'markAsSeen'])->name('stories.seen');
        Route::post('/historias/{story}/like', [App\Http\Controllers\StoryController::class, 'toggleLike'])->name('stories.like');

        // Reportes
        Route::post('/report/{user}', [App\Http\Controllers\ReportController::class, 'store'])->name('reports.store');
    });

    // Perfil de Usuario Propio
    Route::get('/mi-perfil', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/mi-perfil', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Marcar Notificaciones como leídas
    Route::post('/notificaciones/marcar-leidas', function (Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
        return back();
    })->name('notifications.read');
    
    // -----------------------------------------------------
    // Panel de Administración (Solo Admins)
    // -----------------------------------------------------
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        
        // Categorías del Foro
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.delete');
        
        // Moderación de Posts y Comentarios
        Route::post('/posts/{post}/pin', [AdminController::class, 'togglePinPost'])->name('posts.pin');
        Route::delete('/posts/{post}', [AdminController::class, 'deletePost'])->name('posts.delete');
        Route::delete('/comments/{comment}', [AdminController::class, 'deleteComment'])->name('comments.delete');
        
        // Gestión de Usuarios
        Route::post('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
        Route::post('/users/{user}/verify', [AdminController::class, 'toggleVerify'])->name('users.verify');
        
        // Insignias (Badges)
        Route::post('/badges', [AdminController::class, 'storeBadge'])->name('badges.store');
        Route::put('/badges/{badge}', [AdminController::class, 'updateBadge'])->name('badges.update');
        Route::delete('/badges/{badge}', [AdminController::class, 'destroyBadge'])->name('badges.destroy');
        Route::post('/users/{user}/badges', [AdminController::class, 'assignBadge'])->name('badges.assign');

        // Especialidades
        Route::post('/specialties', [AdminController::class, 'storeSpecialty'])->name('specialties.store');
        Route::delete('/specialties/{specialty}', [AdminController::class, 'deleteSpecialty'])->name('specialties.delete');
        
        // Reportes y Moderación de Contenido
        Route::patch('/reportes/{report}/resolver', [AdminController::class, 'resolveReport'])->name('reports.resolve');
        Route::delete('/reportes/{report}', [AdminController::class, 'deleteReport'])->name('reports.destroy');
        Route::delete('/reportes/{report}/status', [AdminController::class, 'deleteReportedStatus'])->name('reports.deleteStatus');

        // Sanciones de Usuarios
        Route::post('/usuarios/{user}/suspender', [AdminController::class, 'suspendUser'])->name('users.suspend');
        Route::patch('/usuarios/{user}/banear', [AdminController::class, 'toggleBanUser'])->name('users.ban');
    });

}); // <-- Aquí se cierra correctamente el grupo Route::middleware('auth')

// -----------------------------------------------------
// Panel de Moderación (Accesible para Admins y Moderadores)
// -----------------------------------------------------
Route::middleware(['auth', 'moderator'])->prefix('moderacion')->name('moderator.groups.')->group(function () {
    Route::get('/', [ModeratorController::class, 'index'])->name('index');
    
    // Gestión de Reportes y Contenido
    Route::patch('/reportes/{report}/resolver', [ModeratorController::class, 'resolveReport'])->name('reports.resolve');
    Route::delete('/reportes/{report}/status', [ModeratorController::class, 'deleteReportedStatus'])->name('reports.deleteStatus');

    // Sanciones a Usuarios (Suspensiones y Baneos)
    Route::post('/usuarios/{user}/suspender', [ModeratorController::class, 'suspendUser'])->name('users.suspend');
    Route::patch('/usuarios/{user}/banear', [ModeratorController::class, 'toggleBanUser'])->name('users.ban');
});

// -----------------------------------------------------
// Ruta Comodín del Foro (Debe ir estrictamente al final)
// -----------------------------------------------------
Route::get('/foro/{slug}', [ForumController::class, 'show'])->name('forum.show');