<section class="section">
  <div class="container">
    <div class="form-card wide">
      <p class="eyebrow"><?= e(ucfirst($programme['award_level'])) ?> Programme</p>
      <h1>Apply — <?= e($programme['title']) ?></h1>
      <p><?= e($programme['summary']) ?></p>
      <?php if ($programme['entry_requirements']): ?>
        <p><strong>Entry requirements:</strong> <?= nl2br(e($programme['entry_requirements'])) ?></p>
      <?php endif; ?>

      <form method="post" action="/academic/apply/<?= (int) $programme['id'] ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
          <label>Full name</label>
          <input type="text" name="applicant_name" value="<?= e(old('applicant_name')) ?>" required>
          <?php foreach (field_errors('applicant_name') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= e(old('email')) ?>" required>
          <?php foreach (field_errors('email') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="tel" name="phone" value="<?= e(old('phone')) ?>">
        </div>
        <div class="form-group">
          <label>Supporting documents (transcripts, IDs — PDF/image/zip)</label>
          <input type="file" name="documents" accept=".pdf,.jpg,.jpeg,.png,.zip">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Submit Application</button>
      </form>
    </div>
  </div>
</section>
