<?php
$page = 'Take Test';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_login();
$user = current_user();
$pdo = db();

// Start a new test on GET if no in-progress exists, or resume
$stmt = $pdo->prepare('SELECT id FROM tests WHERE user_id=? AND status="in_progress" ORDER BY id DESC LIMIT 1');
$stmt->execute([$user['id']]);
$existing = $stmt->fetch();

if ($existing && isset($_GET['new'])) {
    $pdo->prepare('UPDATE tests SET status="expired" WHERE id=?')->execute([$existing['id']]);
    $existing = null;
}

if (!$existing) {
    $pdo->beginTransaction();
    $pdo->prepare('INSERT INTO tests (user_id) VALUES (?)')->execute([$user['id']]);
    $testId = (int)$pdo->lastInsertId();
    $qs = $pdo->query('SELECT id FROM questions ORDER BY RAND() LIMIT 30')->fetchAll(PDO::FETCH_COLUMN);
    $ins = $pdo->prepare('INSERT INTO test_attempts (test_id, question_id, position) VALUES (?,?,?)');
    foreach ($qs as $i => $qid) $ins->execute([$testId, $qid, $i+1]);
    $pdo->commit();
} else {
    $testId = (int)$existing['id'];
}

// Fetch questions for this test
$qstmt = $pdo->prepare('SELECT q.id, q.category, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, ta.position
    FROM test_attempts ta JOIN questions q ON q.id=ta.question_id
    WHERE ta.test_id=? ORDER BY ta.position');
$qstmt->execute([$testId]);
$questions = $qstmt->fetchAll();

$tstart = $pdo->prepare('SELECT UNIX_TIMESTAMP(started_at) s FROM tests WHERE id=?');
$tstart->execute([$testId]);
$startedAt = (int)$tstart->fetch()['s'];
$elapsed = time() - $startedAt;
$remaining = max(0, 1200 - $elapsed); // 20 min

require __DIR__ . '/includes/header.php';
?>
<div class="test-shell">
    <div class="test-top">
        <div>
            <div style="font-size:13px;text-transform:uppercase;letter-spacing:1.5px;color:var(--ink-soft)">Assessment in progress</div>
            <div style="font-size:22px;font-weight:700;margin-top:4px">MindMetric Cognitive Battery</div>
        </div>
        <div class="timer" id="timer">00:00</div>
    </div>
    <div class="progress"><div id="progressBar" style="width:0%"></div></div>
    <div id="qHost"></div>
    <div class="test-nav">
        <button class="btn btn-secondary" id="prevBtn" type="button">← Previous</button>
        <button class="btn" id="nextBtn" type="button">Next →</button>
    </div>
</div>

<script>
window.MM_TEST = {
    testId: <?= $testId ?>,
    csrf: <?= json_encode(csrf_token()) ?>,
    remaining: <?= $remaining ?>,
    questions: <?= json_encode($questions) ?>
};
</script>
<script src="assets/js/test.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
