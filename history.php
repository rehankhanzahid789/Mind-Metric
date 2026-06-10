<?php
$page = 'History';
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$rows = db()->prepare('SELECT r.*, t.completed_at FROM results r JOIN tests t ON t.id=r.test_id WHERE r.user_id=? ORDER BY r.created_at DESC');
$rows->execute([$user['id']]);
$rows = $rows->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section>
    <div class="section-title"><span>All test attempts</span></div>
    <div class="card" style="padding:0;overflow:hidden">
        <table class="table">
            <thead><tr><th>Date</th><th>Score</th><th>Correct</th><th>IQ</th><th>Time</th><th></th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" style="padding:24px;text-align:center;color:var(--muted)">No attempts yet.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= date('M j, Y · g:ia', strtotime($r['completed_at'])) ?></td>
                    <td><strong><?= round($r['percentage'],1) ?>%</strong></td>
                    <td><?= (int)$r['raw_score'] ?>/<?= (int)$r['total_questions'] ?></td>
                    <td><?= (int)$r['iq_estimate'] ?></td>
                    <td><?= gmdate('i:s', (int)$r['time_taken_seconds']) ?></td>
                    <td><a href="results.php?id=<?= (int)$r['test_id'] ?>">Detail →</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
