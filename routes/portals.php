<?php

/** @var \App\Core\Router $router */

use App\Controllers\Academic\GatewayController as AcademicGateway;
use App\Controllers\Admin\AcademicController as AdminAcademic;
use App\Controllers\Admin\AnnouncementController as AdminAnnouncement;
use App\Controllers\Admin\CalendarController as AdminCalendar;
use App\Controllers\Auth\AccountController as Account;
use App\Controllers\Admin\CertificateController as AdminCertificate;
use App\Controllers\Admin\CorporateRequestController as AdminCorporateRequest;
use App\Controllers\Admin\CourseController as AdminCourse;
use App\Controllers\Admin\DashboardController as AdminDashboard;
use App\Controllers\Admin\OrganizationController as AdminOrganization;
use App\Controllers\Admin\PaymentController as AdminPayment;
use App\Controllers\Admin\ReportController as AdminReport;
use App\Controllers\Admin\UserController as AdminUser;
use App\Controllers\Corporate\PortalController as CorporatePortal;
use App\Controllers\Learner\CourseRoomController;
use App\Controllers\Learner\DashboardController as LearnerDashboard;
use App\Controllers\Learner\PortalController as LearnerPortal;
use App\Controllers\Lecturer\DashboardController as LecturerDashboard;
use App\Controllers\Lecturer\IntakeController as LecturerIntake;

// ── Learner portal ─────────────────────────────────────────────────────
$router->get('/learner', [LearnerDashboard::class, 'index']);
$router->get('/learner/courses', [LearnerDashboard::class, 'courses']);
$router->get('/learner/certificates', [LearnerDashboard::class, 'certificates']);
$router->get('/learner/certificates/{code}/download', [LearnerDashboard::class, 'downloadCertificate']);
$router->get('/learner/fees', [LearnerDashboard::class, 'fees']);
$router->get('/learner/profile', [Account::class, 'show']);
$router->post('/learner/profile', [Account::class, 'updateProfile']);
$router->post('/learner/profile/password', [Account::class, 'updatePassword']);
$router->get('/learner/announcements', [LearnerPortal::class, 'announcements']);
$router->get('/learner/calendar', [LearnerPortal::class, 'calendar']);
$router->get('/learner/results', [LearnerPortal::class, 'results']);
$router->get('/learner/admission', [LearnerPortal::class, 'admission']);
$router->get('/learner/admission/{application}/letter', [LearnerPortal::class, 'admissionLetter']);
$router->get('/learner/admission/{application}/files/{key}', [LearnerPortal::class, 'admissionFile']);

$router->get('/learner/courses/{intake}', [CourseRoomController::class, 'show']);
$router->get('/learner/materials/{material}/download', [CourseRoomController::class, 'downloadMaterial']);
$router->post('/learner/courses/{intake}/discussion', [CourseRoomController::class, 'postDiscussion']);
$router->post('/learner/assignments/{assignment}/submit', [CourseRoomController::class, 'submitAssignment']);
$router->get('/learner/quizzes/{quiz}', [CourseRoomController::class, 'showQuiz']);
$router->post('/learner/quizzes/{quiz}/start', [CourseRoomController::class, 'startQuiz']);
$router->get('/learner/quizzes/{quiz}/attempt/{attempt}', [CourseRoomController::class, 'showAttempt']);
$router->post('/learner/quizzes/{quiz}/attempt/{attempt}', [CourseRoomController::class, 'submitAttempt']);

// ── Lecturer portal ─────────────────────────────────────────────────────
$router->get('/lecturer', [LecturerDashboard::class, 'index']);
$router->get('/lecturer/account', [Account::class, 'show']);
$router->post('/lecturer/account', [Account::class, 'updateProfile']);
$router->post('/lecturer/account/password', [Account::class, 'updatePassword']);
$router->get('/lecturer/classes/{intake}', [LecturerIntake::class, 'show']);
$router->post('/lecturer/classes/{intake}/materials', [LecturerIntake::class, 'addMaterial']);
$router->post('/lecturer/classes/{intake}/assignments', [LecturerIntake::class, 'createAssignment']);
$router->post('/lecturer/classes/{intake}/quizzes', [LecturerIntake::class, 'createQuiz']);
$router->post('/lecturer/classes/{intake}/attendance', [LecturerIntake::class, 'markAttendance']);
$router->post('/lecturer/classes/{intake}/grades', [LecturerIntake::class, 'recordGrade']);
$router->post('/lecturer/classes/{intake}/discussion', [LecturerIntake::class, 'postDiscussion']);
$router->post('/lecturer/classes/{intake}/announcements', [LecturerIntake::class, 'postAnnouncement']);
$router->post('/lecturer/announcements/{announcement}/delete', [LecturerIntake::class, 'deleteAnnouncement']);
$router->get('/lecturer/assignments/{assignment}/submissions', [LecturerIntake::class, 'assignmentSubmissions']);
$router->get('/lecturer/submissions/{submission}/file', [LecturerIntake::class, 'submissionFile']);
$router->post('/lecturer/submissions/{submission}/grade', [LecturerIntake::class, 'gradeSubmission']);
$router->get('/lecturer/quizzes/{quiz}', [LecturerIntake::class, 'showQuiz']);
$router->post('/lecturer/quizzes/{quiz}/questions', [LecturerIntake::class, 'addQuestion']);

// ── Corporate portal ────────────────────────────────────────────────────
$router->get('/corporate/portal', [CorporatePortal::class, 'index']);
$router->get('/corporate/portal/account', [Account::class, 'show']);
$router->post('/corporate/portal/account', [Account::class, 'updateProfile']);
$router->post('/corporate/portal/account/password', [Account::class, 'updatePassword']);
$router->get('/corporate/portal/cohorts', [CorporatePortal::class, 'cohorts']);
$router->get('/corporate/portal/invoices', [CorporatePortal::class, 'invoices']);

// ── Academic programmes (certificate / diploma / degree via university partners) ──
$router->get('/academic', [AcademicGateway::class, 'index']);
$router->get('/academic/programmes/{programme}', [AcademicGateway::class, 'show']);
$router->get('/academic/apply/{programme}', [AcademicGateway::class, 'showApply']);
$router->post('/academic/apply/{programme}', [AcademicGateway::class, 'submitApply']);
$router->get('/academic/application-received', [AcademicGateway::class, 'received']);

// ── Admin portal ─────────────────────────────────────────────────────────
$router->get('/admin', [AdminDashboard::class, 'index']);
$router->get('/admin/account', [Account::class, 'show']);
$router->post('/admin/account', [Account::class, 'updateProfile']);
$router->post('/admin/account/password', [Account::class, 'updatePassword']);

$router->get('/admin/courses', [AdminCourse::class, 'index']);
$router->get('/admin/courses/create', [AdminCourse::class, 'create']);
$router->post('/admin/courses', [AdminCourse::class, 'store']);
$router->get('/admin/courses/{course}', [AdminCourse::class, 'edit']);
$router->post('/admin/courses/{course}', [AdminCourse::class, 'update']);
$router->post('/admin/courses/{course}/intakes', [AdminCourse::class, 'addIntake']);
$router->post('/admin/intakes/{intake}/status', [AdminCourse::class, 'updateIntakeStatus']);

$router->get('/admin/corporate-requests', [AdminCorporateRequest::class, 'index']);
$router->get('/admin/corporate-requests/{request}', [AdminCorporateRequest::class, 'show']);
$router->post('/admin/corporate-requests/{request}/status', [AdminCorporateRequest::class, 'updateStatus']);
$router->post('/admin/corporate-requests/{request}/quote', [AdminCorporateRequest::class, 'uploadQuote']);
$router->get('/admin/corporate-requests/{request}/quote-file', [AdminCorporateRequest::class, 'quoteFile']);
$router->post('/admin/corporate-requests/{request}/convert', [AdminCorporateRequest::class, 'convert']);

$router->get('/admin/organizations', [AdminOrganization::class, 'index']);
$router->post('/admin/organizations', [AdminOrganization::class, 'store']);
$router->get('/admin/organizations/{org}', [AdminOrganization::class, 'show']);
$router->post('/admin/organizations/{org}/contact', [AdminOrganization::class, 'setContact']);
$router->post('/admin/organizations/{org}/bulk-enrol', [AdminOrganization::class, 'bulkEnrol']);

$router->get('/admin/payments', [AdminPayment::class, 'index']);
$router->get('/admin/payments/{payment}/proof', [AdminPayment::class, 'proof']);
$router->post('/admin/payments/{payment}/confirm', [AdminPayment::class, 'confirm']);
$router->post('/admin/payments/{payment}/reject', [AdminPayment::class, 'reject']);

$router->get('/admin/certificates', [AdminCertificate::class, 'index']);
$router->get('/admin/certificates/roster/{intake}', [AdminCertificate::class, 'rosterFor']);
$router->post('/admin/certificates/roster/{intake}', [AdminCertificate::class, 'issue']);
$router->post('/admin/certificates/{certificate}/revoke', [AdminCertificate::class, 'revoke']);

$router->get('/admin/academic/programmes', [AdminAcademic::class, 'programmes']);
$router->post('/admin/academic/programmes', [AdminAcademic::class, 'createProgramme']);
$router->get('/admin/academic/programmes/create', [AdminAcademic::class, 'newProgramme']);
$router->get('/admin/academic/programmes/{programme}', [AdminAcademic::class, 'editProgramme']);
$router->post('/admin/academic/programmes/{programme}', [AdminAcademic::class, 'updateProgramme']);
$router->get('/admin/academic/applications', [AdminAcademic::class, 'applications']);
$router->get('/admin/academic/applications/{application}', [AdminAcademic::class, 'showApplication']);
$router->get('/admin/academic/applications/{application}/documents', [AdminAcademic::class, 'applicationDocuments']);
$router->get('/admin/academic/applications/{application}/files/{key}', [AdminAcademic::class, 'applicationFile']);
$router->post('/admin/academic/applications/{application}/decide', [AdminAcademic::class, 'decide']);
$router->post('/admin/academic/fees', [AdminAcademic::class, 'addFee']);

$router->get('/admin/announcements', [AdminAnnouncement::class, 'index']);
$router->post('/admin/announcements', [AdminAnnouncement::class, 'store']);
$router->post('/admin/announcements/{announcement}/delete', [AdminAnnouncement::class, 'destroy']);
$router->get('/admin/calendar', [AdminCalendar::class, 'index']);
$router->post('/admin/calendar/events', [AdminCalendar::class, 'storeEvent']);
$router->post('/admin/calendar/events/{event}/delete', [AdminCalendar::class, 'destroyEvent']);
$router->post('/admin/calendar/slots', [AdminCalendar::class, 'storeSlot']);
$router->post('/admin/calendar/slots/{slot}/delete', [AdminCalendar::class, 'destroySlot']);

$router->get('/admin/users', [AdminUser::class, 'index']);
$router->post('/admin/users', [AdminUser::class, 'create']);
$router->post('/admin/users/{user}/roles', [AdminUser::class, 'updateRoles']);
$router->post('/admin/users/{user}/status', [AdminUser::class, 'toggleStatus']);

$router->get('/admin/reports', [AdminReport::class, 'index']);
