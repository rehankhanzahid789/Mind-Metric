<?php
$page = 'Results';
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$id = (int)($_GET['id'] ?? 0);
$pdo = db();
$stmt = $pdo->prepare('SELECT r.*, t.started_at, t.completed_at FROM results r JOIN tests t ON t.id=r.test_id WHERE r.test_id=? AND r.user_id=?');
$stmt->execute([$id, $user['id']]);
$r = $stmt->fetch();
if (!$r) { http_response_code(404); die('Result not found.'); }
$breakdown = json_decode($r['category_breakdown'], true) ?: [];
require __DIR__ . '/includes/header.php';
?>
<section class="score-hero">
    <div>
        <div class="label">Estimated IQ</div>
        <div class="iq"><?= (int)$r['iq_estimate'] ?></div>
        <div style="margin-top:10px;font-size:14px;color:#bba88a">Based on <?= (int)$r['raw_score'] ?>/<?= (int)$r['total_questions'] ?> correct in <?= gmdate('i:s',(int)$r['time_taken_seconds']) ?></div>
    </div>
    <div class="stats">
        <div class="stat"><div class="k">Score</div><div class="v"><?= round($r['percentage'],1) ?>%</div></div>
        <div class="stat"><div class="k">Correct</div><div class="v"><?= (int)$r['raw_score'] ?>/<?= (int)$r['total_questions'] ?></div></div>
        <div class="stat"><div class="k">Time</div><div class="v"><?= gmdate('i:s',(int)$r['time_taken_seconds']) ?></div></div>
        <div class="stat"><div class="k">Date</div><div class="v" style="font-size:16px"><?= date('M j, Y', strtotime($r['completed_at'])) ?></div></div>
    </div>
</section>

<section class="section">
    <div class="section-title"><span>Category breakdown</span></div>
    <div class="grid grid-2">
        <div class="card">
            <?php foreach ($breakdown as $cat => $pct): ?>
                <div style="margin-bottom:14px">
                    <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px">
                        <strong><?= sanitize($cat) ?></strong><span><?= (float)$pct ?>%</span>
                    </div>
                    <div class="progress"><div style="width:<?= (float)$pct ?>%"></div></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card chart-card">
            <canvas id="resultChart"></canvas>
        </div>
    </div>
    <div style="margin-top:20px;display:flex;gap:12px">
        <a class="btn" href="test.php?new=1">Take another test</a>
        <a class="btn btn-secondary" href="dashboard.php">Back to dashboard</a>
    </div>
</section>

<script>
window.MM_RESULT = <?= json_encode($breakdown) ?>;
</script>
<script src="assets/js/charts.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
