<?php

/** @var \App\Core\Router $router */

use App\Controllers\Auth\AuthController;
use App\Controllers\Auth\PasswordResetController;
use App\Controllers\Learner\EnrollmentController;
use App\Controllers\Learner\PaymentController;
use App\Controllers\Public\CorporateRequestController;
use App\Controllers\Public\CourseController;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\PillarController;
use App\Controllers\Public\VerifyController;
use App\Controllers\Public\WebhookController;
use App\Core\Request;

// ── Public site ─────────────────────────────────────────────────────────
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [HomeController::class, 'about']);
$router->get('/contact', [HomeController::class, 'contact']);

$router->get('/professional-training', fn(Request $r) => (new PillarController())->forSlug('professional-training'));
$router->get('/capacity-building', fn(Request $r) => (new PillarController())->forSlug('capacity-building'));
$router->get('/corporate-training', fn(Request $r) => (new PillarController())->forSlug('corporate-training'));

$router->get('/courses', [CourseController::class, 'index']);
$router->get('/courses/{slug}', [CourseController::class, 'show']);

$router->get('/corporate/request', [CorporateRequestController::class, 'create']);
$router->post('/corporate/request', [CorporateRequestController::class, 'store']);

$router->get('/verify', [VerifyController::class, 'form']);
$router->get('/verify/lookup', [VerifyController::class, 'lookup']);
$router->get('/verify/{code}', [VerifyController::class, 'show']);

$router->post('/webhooks/flutterwave', [WebhookController::class, 'flutterwave']);

// ── Auth ─────────────────────────────────────────────────────────────────
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [PasswordResetController::class, 'showForgot']);
$router->post('/forgot-password', [PasswordResetController::class, 'sendLink']);
$router->get('/reset-password', [PasswordResetController::class, 'showReset']);
$router->post('/reset-password', [PasswordResetController::class, 'reset']);

// ── Enrolment + payment (self-service, requires login) ────────────────────
$router->post('/enroll/{intake}', [EnrollmentController::class, 'enroll']);
$router->get('/learner/pay/{invoice}', [PaymentController::class, 'show']);
$router->post('/learner/pay/{invoice}/flutterwave', [PaymentController::class, 'initiateFlutterwave']);
$router->get('/learner/pay/flutterwave/callback', [PaymentController::class, 'flutterwaveCallback']);
$router->post('/learner/pay/{invoice}/bank-transfer', [PaymentController::class, 'bankTransfer']);

require __DIR__ . '/portals.php';
