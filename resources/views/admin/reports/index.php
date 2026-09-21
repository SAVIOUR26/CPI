<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Reports</h1></div>

<div class="grid-2">
  <div>
    <h2>Enrolments by Month</h2>
    <div class="table-card" style="margin-bottom:24px">
      <table>
        <thead><tr><th>Month</th><th>Enrolments</th></tr></thead>
        <tbody>
        <?php foreach ($enrolmentsByMonth as $r): ?><tr><td><?= e($r['ym']) ?></td><td><?= (int) $r['c'] ?></td></tr><?php endforeach; ?>
        <?php if (!$enrolmentsByMonth): ?><tr><td colspan="2">No data yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <h2>Revenue by Month</h2>
    <div class="table-card">
      <table>
        <thead><tr><th>Month</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($revenueByMonth as $r): ?><tr><td><?= e($r['ym']) ?></td><td><?= money($r['total']) ?></td></tr><?php endforeach; ?>
        <?php if (!$revenueByMonth): ?><tr><td colspan="2">No data yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <h2>Top Courses by Enrolment</h2>
    <div class="table-card">
      <table>
        <thead><tr><th>Course</th><th>Enrolments</th></tr></thead>
        <tbody>
        <?php foreach ($topCourses as $r): ?><tr><td><?= e($r['title']) ?></td><td><?= (int) $r['enrolments'] ?></td></tr><?php endforeach; ?>
        <?php if (!$topCourses): ?><tr><td colspan="2">No data yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
