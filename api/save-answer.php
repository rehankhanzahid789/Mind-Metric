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
$qid    = (int)($data['question_id'] ?? 0);
$ans    = strtoupper((string)($data['answer'] ?? ''));
if (!in_array($ans, ['A','B','C','D'], true)) { echo json_encode(['error'=>'bad_answer']); exit; }

$pdo = db();
$check = $pdo->prepare('SELECT t.id FROM tests t JOIN test_attempts ta ON ta.test_id=t.id WHERE t.id=? AND t.user_id=? AND ta.question_id=? AND t.status="in_progress"');
$check->execute([$testId, $user['id'], $qid]);
if (!$check->fetch()) { echo json_encode(['error'=>'not_found']); exit; }

$cor = $pdo->prepare('SELECT correct_answer FROM questions WHERE id=?');
$cor->execute([$qid]);
$correct = $cor->fetch()['correct_answer'];
$ok = ($correct === $ans) ? 1 : 0;

$pdo->prepare('INSERT INTO answers (test_id, question_id, selected_answer, is_correct) VALUES (?,?,?,?)
    ON DUPLICATE KEY UPDATE selected_answer=VALUES(selected_answer), is_correct=VALUES(is_correct)')
    ->execute([$testId, $qid, $ans, $ok]);

echo json_encode(['ok'=>true]);
