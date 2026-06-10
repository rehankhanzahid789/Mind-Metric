<?php
$page = 'Welcome';
require_once __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div>
        <h1>Measure how you think — with precision.</h1>
        <p>MindMetric is a professional cognitive assessment platform. Take validated reasoning tests, track category-level performance, and benchmark your IQ estimate over time.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <a class="btn" href="register.php">Create free account</a>
            <a class="btn btn-secondary" href="login.php">Sign in</a>
        </div>
    </div>
    <aside class="hero-card">
        <div class="hero-stat">30</div>
        <div class="hero-stat-label">Questions per assessment</div>
        <div class="hero-meta">
            <div><strong>20 min</strong><br>Timed session</div>
            <div><strong>5</strong><br>Reasoning categories</div>
            <div><strong>100</strong><br>Item question bank</div>
            <div><strong>IQ</strong><br>Standardized estimate</div>
        </div>
    </aside>
</section>

<section class="section">
    <div class="section-title"><span>What you get</span></div>
    <div class="grid grid-3">
        <div class="card">
            <h3>Adaptive item pool</h3>
            <div>Each attempt draws a fresh 30-item sample across pattern, numerical, verbal, logical and analytical reasoning.</div>
        </div>
        <div class="card">
            <h3>Category analytics</h3>
            <div>See where you excel and where to train, with per-category scoring and historical trend charts.</div>
        </div>
        <div class="card">
            <h3>Secure &amp; private</h3>
            <div>Hashed credentials, CSRF-protected forms, session regeneration and prepared statements throughout.</div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
