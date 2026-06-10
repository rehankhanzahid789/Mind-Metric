<?php
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
require_login();

$user = current_user();
$data = json_decode(file_get_contents('php://input'), true) ?: [];
if (!hash_equals(csrf_token(), $data['csrf'] ?? '')) {
    http_response_code(419); echo json_encode(['error'=>'csrf']); exit;
}
$testId = (int)($data['test_id'] ?? 0);
$pdo = db();

$t = $pdo->prepare('SELECT * FROM tests WHERE id=? AND user_id=?');
$t->execute([$testId, $user['id']]);
$test = $t->fetch();
if (!$test) { echo json_encode(['error'=>'not_found']); exit; }

if ($test['status'] === 'completed') {
    echo json_encode(['ok'=>true,'redirect'=>"results.php?id=$testId"]); exit;
}

// Score
$rows = $pdo->prepare('SELECT q.category, a.is_correct
    FROM test_attempts ta
    JOIN questions q ON q.id = ta.question_id
    LEFT JOIN answers a ON a.test_id = ta.test_id AND a.question_id = ta.question_id
    WHERE ta.test_id = ?');
$rows->execute([$testId]);
$rows = $rows->fetchAll();

$total = count($rows);
$correct = 0;
$cat = [];
foreach ($rows as $r) {
    $c = $r['category'];
    if (!isset($cat[$c])) $cat[$c] = ['ok'=>0,'tot'=>0];
    $cat[$c]['tot']++;
    if ((int)$r['is_correct'] === 1) { $cat[$c]['ok']++; $correct++; }
}
$pct = $total ? round(($correct/$total)*100, 2) : 0;
$breakdown = [];
foreach ($cat as $name=>$v) {
    $breakdown[$name] = $v['tot'] ? round(($v['ok']/$v['tot'])*100, 1) : 0;
}

// Simple IQ estimate: mean 100, sd 15, scaled by performance
$z = ($pct - 50) / 15; // pct of 50 ~ 100 IQ
$iq = max(55, min(160, (int)round(100 + $z * 15)));

$elapsed = time() - strtotime($test['started_at']);
$elapsed = max(0, min(1200, $elapsed));

$pdo->beginTransaction();
$pdo->prepare('UPDATE tests SET completed_at=NOW(), time_taken_seconds=?, status="completed" WHERE id=?')
    ->execute([$elapsed, $testId]);
$pdo->prepare('INSERT INTO results (test_id, user_id, raw_score, total_questions, percentage, iq_estimate, category_breakdown, time_taken_seconds)
    VALUES (?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE raw_score=VALUES(raw_score), total_questions=VALUES(total_questions), percentage=VALUES(percentage),
        iq_estimate=VALUES(iq_estimate), category_breakdown=VALUES(category_breakdown), time_taken_seconds=VALUES(time_taken_seconds)')
    ->execute([$testId, $user['id'], $correct, $total, $pct, $iq, json_encode($breakdown), $elapsed]);
$pdo->commit();

echo json_encode(['ok'=>true,'redirect'=>"results.php?id=$testId"]);
