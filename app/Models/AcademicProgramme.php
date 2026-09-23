<?php

namespace App\Models;

use App\Core\Model;

class AcademicProgramme extends Model
{
    protected static string $table = 'academic_programmes';

    private const SELECT = 'SELECT ap.*, c.title, c.slug, c.summary, c.description, c.duration_note AS course_duration,
                                   c.status, c.category_id, cat.slug AS category_slug, cat.name AS category_name
                            FROM academic_programmes ap
                            JOIN courses c ON c.id = ap.course_id
                            LEFT JOIN course_categories cat ON cat.id = c.category_id';

    private const ORDER = ' ORDER BY FIELD(ap.award_level, "certificate", "diploma", "degree", "postgraduate"), ap.sort_order, c.title';

    /**
     * Award levels, in progression order. Descriptions and "suitable for" lists are
     * the client's own copy (docs/content/academic/ACADEMIC SYSTEM.pdf).
     */
    public static function levels(): array
    {
        return [
            'certificate' => [
                'label' => 'Certificate', 'plural' => 'Certificate Programmes', 'icon' => 'fa-certificate',
                'blurb' => 'Foundation-level academic and professional programmes designed to develop essential knowledge and practical competencies.',
                'suits' => ['Gain foundational professional knowledge', 'Develop practical skills', 'Enter a particular field of work', 'Improve their existing skills', 'Prepare for further academic study'],
            ],
            'diploma' => [
                'label' => 'Diploma', 'plural' => 'Diploma Programmes', 'icon' => 'fa-scroll',
                'blurb' => 'More advanced theoretical knowledge and practical professional skills for employment, career development or further academic progression.',
                'suits' => ['Develop specialized professional skills', 'Improve their employment opportunities', 'Advance their careers', 'Upgrade from certificate-level education', 'Prepare for further academic progression'],
            ],
            'degree' => [
                'label' => "Bachelor's Degree", 'plural' => "Bachelor's Degree Programmes", 'icon' => 'fa-graduation-cap',
                'blurb' => 'University degree programmes designed to develop advanced academic knowledge, professional competence, analytical ability and career readiness.',
                'suits' => ['Develop advanced knowledge', 'Build professional competence', 'Progress in their careers', 'Strengthen their academic qualifications', 'Prepare for further professional or postgraduate education'],
            ],
            'postgraduate' => [
                'label' => 'Postgraduate', 'plural' => 'Postgraduate Programmes', 'icon' => 'fa-user-graduate',
                'blurb' => 'Advanced programmes for graduates and professionals seeking specialization, career advancement or further academic development.',
                'suits' => ['Specialize in their field', 'Advance into senior roles', 'Pursue further academic development'],
            ],
        ];
    }

    public static function published(): array
    {
        return static::query(self::SELECT . ' WHERE c.status = "published"' . self::ORDER);
    }

    public static function listAll(): array
    {
        return static::query(self::SELECT . self::ORDER);
    }

    public static function withCourse(int $id): ?array
    {
        $rows = static::query(self::SELECT . ' WHERE ap.id = ?', [$id]);
        return $rows[0] ?? null;
    }

    /** The field of study without the award prefix, e.g. "Public Health". */
    public static function field(array $programme): string
    {
        return (string) preg_replace("/^(Certificate|Diploma|Bachelor's Degree|Bachelor of|Postgraduate Diploma|Master's Degree|Master of)\s+(in|of)?\s*/i", '', $programme['title']);
    }

    /** The same field of study at every level, e.g. Certificate → Diploma → Degree in Public Health. */
    public static function pathway(array $programme): array
    {
        $field = strtolower(self::field($programme));
        return array_values(array_filter(self::published(), fn ($p) => strtolower(self::field($p)) === $field));
    }

    public static function grouped(array $programmes): array
    {
        $groups = [];
        foreach ($programmes as $p) {
            $groups[$p['award_level']][] = $p;
        }
        return $groups;
    }
}
