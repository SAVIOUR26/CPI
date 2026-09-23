<?php /** @var array $courses */ ?>
<?php \App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'Corporate training',
  'title' => 'Request training for your organization',
  'lead' => 'Tell us what your team needs and we\'ll come back with a tailored proposal and quote.',
  'crumbs' => [['label' => 'Corporate Training', 'href' => '/corporate-training'], ['label' => 'Request']],
  'stats' => [['icon' => 'fa-people-group', 'text' => 'Any group size'], ['icon' => 'fa-house-laptop', 'text' => 'In-person, online or hybrid'], ['icon' => 'fa-file-invoice', 'text' => 'Free proposal & quote']],
]); ?>
<section class="section">
  <div class="container">
    <div class="form-card wide">
      <h2 style="font-size:1.5rem">Your training request</h2>
      <p>Tell us what you need — "train our 50 staff in M&amp;E" or "leadership training for our district health
        team" — and our team will get back to you with a proposal.</p>

      <form method="post" action="/corporate/request">
        <?= csrf_field() ?>
        <div class="grid-2">
          <div class="form-group">
            <label>Organization name</label>
            <input type="text" name="organization_name" value="<?= e(old('organization_name')) ?>" required>
            <?php foreach (field_errors('organization_name') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
          </div>
          <div class="form-group">
            <label>Your name</label>
            <input type="text" name="contact_name" value="<?= e(old('contact_name')) ?>" required>
            <?php foreach (field_errors('contact_name') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="contact_email" value="<?= e(old('contact_email')) ?>" required>
            <?php foreach (field_errors('contact_email') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="contact_phone" value="<?= e(old('contact_phone')) ?>">
          </div>
        </div>

        <div class="form-group">
          <label>What do you need training on?</label>
          <input type="text" name="topic" placeholder="e.g. Monitoring &amp; Evaluation for our district health team" value="<?= e(old('topic')) ?>" required>
          <?php foreach (field_errors('topic') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
        </div>

        <div class="form-group">
          <label>Related course from our catalogue (optional)</label>
          <select name="course_id">
            <option value="">— None / not sure —</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid-2">
          <div class="form-group">
            <label>Number of staff</label>
            <input type="number" name="headcount" min="1" value="<?= e(old('headcount')) ?>">
          </div>
          <div class="form-group">
            <label>Preferred mode</label>
            <select name="mode">
              <option value="in_person">In person</option>
              <option value="online">Online</option>
              <option value="hybrid">Hybrid</option>
            </select>
          </div>
          <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" value="<?= e(old('location')) ?>">
          </div>
          <div class="form-group">
            <label>Preferred dates</label>
            <input type="text" name="preferred_dates" placeholder="e.g. Late November 2026" value="<?= e(old('preferred_dates')) ?>">
          </div>
        </div>

        <div class="form-group">
          <label>Budget guidance (optional)</label>
          <input type="text" name="budget_note" value="<?= e(old('budget_note')) ?>">
        </div>

        <div class="form-group">
          <label>Anything else we should know?</label>
          <textarea name="message"><?= e(old('message')) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
      </form>
    </div>
  </div>
</section>
