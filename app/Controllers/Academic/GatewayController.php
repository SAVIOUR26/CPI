<?php

namespace App\Controllers\Academic;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Session;
use App\Core\Upload;
use App\Models\AcademicApplication;
use App\Models\AcademicProgramme;
use App\Models\Setting;

class GatewayController extends Controller
{
    /** Options from the client's admission form (docs/content/academic/cpi_online_application_form.html). */
    public const INTAKES = ['January', 'May', 'August', 'October'];
    public const STUDY_SESSIONS = [
        'Face-to-face / In class — Morning',
        'Face-to-face / In class — Evening / Weekend',
        'Online — Distance learning',
        'Online — At my own convenient time',
    ];
    public const TITLES = ['Mr.', 'Mrs.', 'Miss', 'Ms.', 'Dr.', 'Rev.'];
    public const SUPPORT_NEEDS = ['Hearing', 'Speaking', 'Seeing', 'Physical', 'Other', 'None'];

    /** Uploads: field => [label, multiple?, images only?] */
    public const DOCUMENTS = [
        'passport_photo' => ['Passport photograph', false, true],
        'national_id' => ['National ID / Passport', false, false],
        'uace_slip' => ['UACE certificate / result slip', false, false],
        'uce_slip' => ['UCE certificate / result slip', false, false],
        'certificates' => ['Academic certificates', true, false],
        'transcripts' => ['Academic transcripts', true, false],
        'other_docs' => ['Other supporting documents', true, false],
    ];
    private const MAX_FILE_BYTES = 5 * 1024 * 1024;
    public const MAX_FILES_PER_FIELD = 5;

    public function index(Request $request): void
    {
        $programmes = AcademicProgramme::published();
        $this->view('academic.index', [
            'pageTitle' => 'Academic Programmes — Certificate, Diploma & Degree | CPI',
            'metaDescription' => 'Certificate, Diploma and Bachelor\'s Degree programmes offered through Crawford Professionals Institute\'s university partnerships in Uganda.',
            'programmes' => $programmes,
            'groups' => AcademicProgramme::grouped($programmes),
            'levels' => AcademicProgramme::levels(),
        ]);
    }

    public function show(Request $request): void
    {
        $programme = $this->findPublished((int) $request->param('programme'));
        $this->view('academic.show', [
            'pageTitle' => $programme['title'] . ' — CPI Academic Programmes',
            'metaDescription' => (string) $programme['summary'],
            'programme' => $programme,
            'level' => AcademicProgramme::levels()[$programme['award_level']],
            'pathway' => AcademicProgramme::pathway($programme),
        ]);
    }

    public function showApply(Request $request): void
    {
        $programme = $this->findPublished((int) $request->param('programme'));
        $this->view('academic.apply', [
            'pageTitle' => 'Apply — ' . $programme['title'],
            'noindex' => true,
            'programme' => $programme,
            'level' => AcademicProgramme::levels()[$programme['award_level']],
            'maxFileBytes' => $this->maxFileBytes(),
            'maxTotalBytes' => self::iniBytes((string) ini_get('post_max_size')),
            'maxFileCount' => (int) ini_get('max_file_uploads'),
        ]);
    }

    /** The smaller of our own per-file cap and the server's upload_max_filesize. */
    private function maxFileBytes(): int
    {
        $server = self::iniBytes((string) ini_get('upload_max_filesize'));
        return $server > 0 ? min(self::MAX_FILE_BYTES, $server) : self::MAX_FILE_BYTES;
    }

    private static function iniBytes(string $value): int
    {
        $value = trim($value);
        $n = (int) $value;
        return match (strtolower(substr($value, -1))) {
            'g' => $n * 1024 ** 3,
            'm' => $n * 1024 ** 2,
            'k' => $n * 1024,
            default => $n,
        };
    }

    public function submitApply(Request $request): void
    {
        $programmeId = (int) $request->param('programme');
        $back = '/academic/apply/' . $programmeId;

        // Over post_max_size, PHP drops the whole body (CSRF token included) — explain rather than show a session error.
        if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->flash('error', 'Your attachments were too large to send together (the server accepts up to '
                . round(self::iniBytes((string) ini_get('post_max_size')) / 1048576) . 'MB in total). Please attach smaller scans and try again.');
            $this->redirect($back);
        }
        $this->verifyCsrf($request);
        $programme = $this->findPublished($programmeId);

        $in = $request->all();
        $form = $this->collectForm($in, $programme);
        $errors = $this->validateForm($form, $in);
        if ($errors) {
            $this->failBack($back, $in, $errors, 'Please correct the highlighted fields. For security, any attachments need to be selected again.');
        }

        $stored = [];
        try {
            $documents = $this->storeDocuments($stored, $form);
        } catch (\RuntimeException $e) {
            foreach ($stored as $path) {
                @unlink(Upload::absolutePath($path));
            }
            $this->failBack($back, $in, [], $e->getMessage() . ' Please re-select your attachments.');
        }

        $personal = $form['personal'];
        $applicantName = trim($personal['other_names'] . ' ' . $personal['surname']);
        // A signed-in applicant applying with their own email sees the application in My Admission straight away.
        $signedIn = Auth::user();
        $id = AcademicApplication::insert([
            'user_id' => $signedIn && strtolower($signedIn['email']) === $personal['email'] ? (int) $signedIn['id'] : null,
            'programme_id' => $programmeId,
            'applicant_name' => $applicantName,
            'email' => $personal['email'],
            'phone' => $personal['phone'],
            'form_data' => json_encode($form, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'documents' => json_encode($documents, JSON_UNESCAPED_SLASHES),
            'status' => 'submitted',
        ]);
        $applicationNo = sprintf('CPI-APP-%s-%05d', date('Y'), $id);
        AcademicApplication::update($id, ['application_no' => $applicationNo]);

        $this->notify($applicationNo, $id, $applicantName, $personal['email'], $programme, $form);

        Session::put('last_application', [
            'application_no' => $applicationNo,
            'programme' => $programme['title'],
            'awarding_body' => $programme['awarding_body'],
            'name' => ($personal['title'] ? $personal['title'] . ' ' : '') . $applicantName,
            'email' => $personal['email'],
            'intake' => $form['programme']['intake'],
            'study_session' => $form['programme']['study_session'],
            'file_count' => count($documents, COUNT_RECURSIVE) - count(array_filter($documents, 'is_array'))
                + count(array_filter($form['qualifications'], fn ($q) => !empty($q['document']))),
            'submitted_at' => date('Y-m-d H:i'),
        ]);
        $this->redirect('/academic/application-received');
    }

    public function received(Request $request): void
    {
        $receipt = Session::get('last_application');
        if (!$receipt) {
            $this->redirect('/academic');
        }
        $this->view('academic.received', [
            'pageTitle' => 'Application received — CPI',
            'noindex' => true,
            'receipt' => $receipt,
        ]);
    }

    private function findPublished(int $id): array
    {
        $programme = AcademicProgramme::withCourse($id);
        if (!$programme || $programme['status'] !== 'published') {
            $this->abort(404, 'Programme not found.');
        }
        return $programme;
    }

    private function collectForm(array $in, array $programme): array
    {
        $text = fn ($v, int $max = 190): string => mb_substr(trim(is_scalar($v) ? (string) $v : ''), 0, $max);
        $rows = function ($list, array $keys, int $limit) use ($text): array {
            $out = [];
            foreach (array_slice(is_array($list) ? $list : [], 0, $limit, true) as $i => $row) {
                $row = is_array($row) ? $row : [];
                $clean = [];
                foreach ($keys as $k) {
                    $clean[$k] = $text($row[$k] ?? '');
                }
                $out[$i] = $clean;
            }
            return $out;
        };
        $p = is_array($in['personal'] ?? null) ? $in['personal'] : [];
        $support = array_values(array_intersect(self::SUPPORT_NEEDS, is_array($p['support'] ?? null) ? $p['support'] : []));

        $nonEmpty = fn (array $list, string ...$keys) => array_values(array_filter($list, function ($r) use ($keys) {
            foreach ($keys as $k) {
                if (($r[$k] ?? '') !== '') {
                    return true;
                }
            }
            return false;
        }));

        return [
            'programme' => [
                'title' => $programme['title'],
                'level' => $programme['award_level'],
                'awarding_body' => $programme['awarding_body'],
                'intake' => in_array($in['intake'] ?? '', self::INTAKES, true) ? $in['intake'] : '',
                'study_session' => in_array($in['study_session'] ?? '', self::STUDY_SESSIONS, true) ? $in['study_session'] : '',
            ],
            'personal' => [
                'title' => in_array($p['title'] ?? '', self::TITLES, true) ? $p['title'] : '',
                'surname' => $text($p['surname'] ?? '', 80),
                'other_names' => $text($p['other_names'] ?? '', 120),
                'gender' => in_array($p['gender'] ?? '', ['Male', 'Female'], true) ? $p['gender'] : '',
                'dob' => $text($p['dob'] ?? '', 10),
                'nationality' => $text($p['nationality'] ?? '', 80),
                'country' => $text($p['country'] ?? '', 80),
                'phone' => $text($p['phone'] ?? '', 30),
                'email' => strtolower($text($p['email'] ?? '')),
                'city' => $text($p['city'] ?? '', 80),
                'address' => $text($p['address'] ?? '', 500),
                'support' => $support,
            ],
            'sponsors' => $nonEmpty($rows($in['sponsors'] ?? [], ['name', 'phone', 'nationality', 'relationship'], 2), 'name', 'phone'),
            'uace' => [
                'year' => $text($in['uace']['year'] ?? '', 4),
                'school' => $text($in['uace']['school'] ?? ''),
                'subjects' => $nonEmpty($rows($in['uace']['subjects'] ?? [], ['subject', 'level', 'grade'], 7), 'subject', 'grade'),
            ],
            'uce' => [
                'year' => $text($in['uce']['year'] ?? '', 4),
                'school' => $text($in['uce']['school'] ?? ''),
                'subjects' => $nonEmpty($rows($in['uce']['subjects'] ?? [], ['subject', 'grade'], 10), 'subject', 'grade'),
            ],
            // Kept with original row keys so uploaded qualification documents can be matched up.
            'qualifications' => array_filter($rows($in['qualifications'] ?? [], ['institution', 'qualification', 'year'], 8), fn ($r) => implode('', $r) !== ''),
            'declaration' => [
                'accepted' => !empty($in['declaration']),
                'signature_name' => $text($in['signature_name'] ?? '', 150),
                'date' => date('Y-m-d'),
            ],
        ];
    }

    private function validateForm(array $form, array $in): array
    {
        $errors = [];
        $p = $form['personal'];
        foreach (['surname' => 'Surname', 'other_names' => 'Other names', 'nationality' => 'Nationality', 'phone' => 'Telephone'] as $key => $label) {
            if ($p[$key] === '') {
                $errors["personal.$key"][] = "$label is required.";
            }
        }
        if ($p['gender'] === '') {
            $errors['personal.gender'][] = 'Please select your gender.';
        }
        if (!filter_var($p['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['personal.email'][] = 'Please enter a valid email address.';
        }
        $dob = \DateTime::createFromFormat('!Y-m-d', $p['dob']);
        if (!$dob || $dob->format('Y-m-d') !== $p['dob'] || $dob > new \DateTime('-10 years') || $dob < new \DateTime('-100 years')) {
            $errors['personal.dob'][] = 'Please enter a valid date of birth.';
        }
        foreach (['uace', 'uce'] as $exam) {
            $year = $form[$exam]['year'];
            if ($year !== '' && (!ctype_digit($year) || (int) $year < 1950 || (int) $year > (int) date('Y'))) {
                $errors["$exam.year"][] = 'Please enter a valid year.';
            }
        }
        if (!$form['declaration']['accepted']) {
            $errors['declaration'][] = 'You must accept the declaration to submit your application.';
        }
        return $errors;
    }

    /** @param string[] $stored collects every stored path so a failure can clean up */
    private function storeDocuments(array &$stored, array &$form): array
    {
        $documents = [];
        foreach (self::DOCUMENTS as $field => [$label, $multiple, $imagesOnly]) {
            $files = $this->uploadedFiles($field);
            if (count($files) > ($multiple ? self::MAX_FILES_PER_FIELD : 1)) {
                throw new \RuntimeException("Too many files for \"$label\" (maximum " . self::MAX_FILES_PER_FIELD . ').');
            }
            foreach ($files as $file) {
                $path = $this->storeOne($file, $label, $imagesOnly);
                $stored[] = $path;
                if ($multiple) {
                    $documents[$field][] = $path;
                } else {
                    $documents[$field] = $path;
                }
            }
        }
        foreach ($this->uploadedFiles('qualification_docs') as $row => $file) {
            if (!isset($form['qualifications'][$row])) {
                continue;
            }
            $path = $this->storeOne($file, 'Qualification document', false);
            $stored[] = $path;
            $form['qualifications'][$row]['document'] = $path;
        }
        $form['qualifications'] = array_values($form['qualifications']);
        return $documents;
    }

    private function storeOne(array $file, string $label, bool $imagesOnly): string
    {
        if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            throw new \RuntimeException("\"$label\" is larger than the " . round($this->maxFileBytes() / 1048576, 1) . 'MB limit per file.');
        }
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = $imagesOnly ? ['jpg', 'jpeg', 'png'] : ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed, true)) {
            throw new \RuntimeException("\"$label\" must be a " . strtoupper(implode('/', $allowed)) . ' file.');
        }
        // The extension decides how the file is served later, so make sure the content matches it.
        if ($file['error'] === UPLOAD_ERR_OK && class_exists(\finfo::class) && is_uploaded_file((string) $file['tmp_name'])) {
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
            $expected = $ext === 'pdf' ? ['application/pdf', 'application/x-pdf'] : ['image/jpeg', 'image/pjpeg', 'image/png'];
            if ($mime !== false && !in_array($mime, $expected, true)) {
                throw new \RuntimeException("\"$label\" does not look like a valid " . strtoupper($ext) . ' file.');
            }
        }
        try {
            return Upload::store($file, 'academic-applications', $this->maxFileBytes());
        } catch (\RuntimeException $e) {
            throw new \RuntimeException("\"$label\": " . $e->getMessage());
        }
    }

    /** Normalises $_FILES for single and multiple (name="x[]") inputs; keeps array keys. */
    private function uploadedFiles(string $field): array
    {
        $f = $_FILES[$field] ?? null;
        if (!$f) {
            return [];
        }
        if (!is_array($f['name'])) {
            return $f['error'] === UPLOAD_ERR_NO_FILE ? [] : [$f];
        }
        $out = [];
        foreach ($f['name'] as $key => $name) {
            if ($f['error'][$key] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $out[$key] = ['name' => $name, 'type' => $f['type'][$key], 'tmp_name' => $f['tmp_name'][$key],
                          'error' => $f['error'][$key], 'size' => $f['size'][$key]];
        }
        return $out;
    }

    private function failBack(string $to, array $input, array $errors, string $message): void
    {
        unset($input['_csrf']);
        Session::flashInput($input);
        if ($errors) {
            Session::flash('errors', $errors);
        }
        $this->flash('error', $message);
        $this->redirect($to);
    }

    private function notify(string $applicationNo, int $id, string $name, string $email, array $programme, array $form): void
    {
        $supportEmail = Setting::get('support_email', 'info@crawfordinstitute.online');
        $details = '<p><strong>Application:</strong> ' . e($applicationNo) . '<br><strong>Programme:</strong> ' . e($programme['title'])
            . ($programme['awarding_body'] ? '<br><strong>Awarding body:</strong> ' . e($programme['awarding_body']) : '')
            . ($form['programme']['intake'] ? '<br><strong>Preferred intake:</strong> ' . e($form['programme']['intake']) : '')
            . ($form['programme']['study_session'] ? '<br><strong>Study session:</strong> ' . e($form['programme']['study_session']) : '')
            . '</p>';

        Mailer::send(
            $supportEmail,
            'CPI Admissions',
            "New application $applicationNo: " . $programme['title'],
            '<p>New academic application from ' . e($name) . ' (' . e($email) . ', ' . e($form['personal']['phone']) . ').</p>'
            . $details . '<p><a href="' . e(url('/admin/academic/applications/' . $id)) . '">Review the application</a></p>'
        );
        Mailer::send(
            $email,
            $name,
            "Application received ($applicationNo) — Crawford Professionals Institute",
            '<p>Dear ' . e($name) . ',</p><p>Thank you for applying to Crawford Professionals Institute. We have received your application.</p>'
            . $details
            . '<p>Please keep your application number for future reference. Our Admissions team will review your application against the '
            . 'programme\'s admission requirements and contact you with the outcome and, if successful, your admission and registration information.</p>'
            . '<p>Please keep your original documents — you may be asked to present them for verification.</p>'
            . '<p>Crawford Professionals Institute — Admissions</p>'
        );
    }
}
