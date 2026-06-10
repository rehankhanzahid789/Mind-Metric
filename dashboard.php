<?php
$page = 'Dashboard';
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();

$pdo = db();
$stmt = $pdo->prepare('SELECT COUNT(*) c, COALESCE(MAX(percentage),0) best, COALESCE(AVG(percentage),0) avg FROM results WHERE user_id = ?');
$stmt->execute([$user['id']]);
$agg = $stmt->fetch();

$latest = $pdo->prepare('SELECT r.*, t.completed_at FROM results r JOIN tests t ON t.id=r.test_id WHERE r.user_id=? ORDER BY r.created_at DESC LIMIT 5');
$latest->execute([$user['id']]);
$latest = $latest->fetchAll();

$history = $pdo->prepare('SELECT created_at, percentage, iq_estimate FROM results WHERE user_id=? ORDER BY created_at ASC LIMIT 20');
$history->execute([$user['id']]);
$history = $history->fetchAll();

// Category averages from latest 10 results
$cats = $pdo->prepare('SELECT category_breakdown FROM results WHERE user_id=? ORDER BY created_at DESC LIMIT 10');
$cats->execute([$user['id']]);
$catRows = $cats->fetchAll();
$catAgg = [];
foreach ($catRows as $r) {
    $b = json_decode($r['category_breakdown'], true) ?: [];
    foreach ($b as $k=>$v) { $catAgg[$k][] = (float)$v; }
}
$catFinal = [];
foreach ($catAgg as $k=>$arr) { $catFinal[$k] = round(array_sum($arr)/count($arr), 1); }

require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div>
        <h1>Welcome back, <?= sanitize(explode(' ', $user['name'])[0]) ?>.</h1>
        <p>Pick up where you left off, or start a new 30-question assessment to refresh your cognitive baseline.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <a class="btn" href="test.php">Start new test</a>
            <a class="btn btn-secondary" href="history.php">View history</a>
        </div>
    </div>
    <aside class="hero-card">
        <div class="hero-stat"><?= $agg['c'] ? round($agg['avg']) : '—' ?>%</div>
        <div class="hero-stat-label">Rolling average score</div>
        <div class="hero-meta">
            <div><strong><?= (int)$agg['c'] ?></strong><br>Tests taken</div>
            <div><strong><?= $agg['c'] ? round($agg['best']) : 0 ?>%</strong><br>Best score</div>
        </div>
    </aside>
</section>

<section class="section">
    <div class="section-title"><span>Snapshot</span></div>
    <div class="grid grid-4">
        <div class="card"><h3>Tests taken</h3><div class="big"><?= (int)$agg['c'] ?></div><div class="sub">Lifetime</div></div>
        <div class="card"><h3>Best score</h3><div class="big"><?= $agg['c'] ? round($agg['best'],1) : 0 ?>%</div><div class="sub">Personal best</div></div>
        <div class="card"><h3>Average</h3><div class="big"><?= $agg['c'] ? round($agg['avg'],1) : 0 ?>%</div><div class="sub">All attempts</div></div>
        <div class="card"><h3>Latest IQ</h3><div class="big"><?= $latest ? (int)$latest[0]['iq_estimate'] : '—' ?></div><div class="sub">Standardized estimate</div></div>
    </div>
</section>

<section class="section">
    <div class="grid grid-2">
        <div class="card chart-card">
            <div class="section-title"><span>Performance trend</span></div>
            <canvas id="trendChart"></canvas>
        </div>
        <div class="card chart-card">
            <div class="section-title"><span>Category strengths</span></div>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-title"><span>Latest results</span><a href="history.php">All history →</a></div>
    <div class="card" style="padding:0;overflow:hidden">
        <table class="table">
            <thead><tr><th>Date</th><th>Score</th><th>IQ</th><th>Time</th><th></th></tr></thead>
            <tbody>
            <?php if (!$latest): ?>
                <tr><td colspan="5" style="padding:24px;text-align:center;color:var(--muted)">No tests yet. <a href="test.php">Take your first assessment →</a></td></tr>
            <?php else: foreach ($latest as $r): ?>
                <tr>
                    <td><?= sanitize(date('M j, Y · g:ia', strtotime($r['completed_at']))) ?></td>
                    <td><strong><?= round($r['percentage'],1) ?>%</strong> · <?= (int)$r['raw_score'] ?>/<?= (int)$r['total_questions'] ?></td>
                    <td><?= (int)$r['iq_estimate'] ?></td>
                    <td><?= gmdate('i:s', (int)$r['time_taken_seconds']) ?></td>
                    <td><a href="results.php?id=<?= (int)$r['test_id'] ?>">View</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
window.MM_TREND = <?= json_encode(array_map(fn($r)=>[
    'date'=>date('M j',strtotime($r['created_at'])),
    'pct'=>(float)$r['percentage'],
    'iq'=>(int)$r['iq_estimate'],
],$history)) ?>;
window.MM_CATS = <?= json_encode($catFinal) ?>;
</script>
<script src="assets/js/charts.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
