// MindMetric test runner
(function(){
    const T = window.MM_TEST;
    if (!T) return;
    const host = document.getElementById('qHost');
    const bar = document.getElementById('progressBar');
    const timer = document.getElementById('timer');
    const prev = document.getElementById('prevBtn');
    const next = document.getElementById('nextBtn');

    const answers = {};
    let idx = 0;
    let remaining = T.remaining;
    let submitting = false;

    function fmt(s){
        s = Math.max(0,s|0);
        const m = String(Math.floor(s/60)).padStart(2,'0');
        const ss = String(s%60).padStart(2,'0');
        return m+':'+ss;
    }
    function tick(){
        timer.textContent = fmt(remaining);
        if (remaining <= 0) { submit(true); return; }
        remaining--;
    }
    tick();
    setInterval(tick, 1000);

    function render(){
        const q = T.questions[idx];
        const total = T.questions.length;
        bar.style.width = (((idx+1)/total)*100).toFixed(1)+'%';
        const sel = answers[q.id];
        const opts = [['A',q.option_a],['B',q.option_b],['C',q.option_c],['D',q.option_d]];
        host.innerHTML = `
            <div class="q-meta">Question ${idx+1} of ${total} · ${q.category}</div>
            <div class="q-text">${escapeHtml(q.question_text)}</div>
            <div class="choices">
                ${opts.map(([k,v])=>`
                    <div class="choice ${sel===k?'selected':''}" data-k="${k}">
                        <div class="choice-letter">${k}</div>
                        <div>${escapeHtml(v)}</div>
                    </div>`).join('')}
            </div>
        `;
        host.querySelectorAll('.choice').forEach(el=>{
            el.addEventListener('click', ()=>{
                const k = el.dataset.k;
                answers[q.id] = k;
                saveAnswer(q.id, k);
                render();
            });
        });
        prev.disabled = idx===0;
        next.textContent = (idx===total-1) ? 'Submit test' : 'Next →';
    }
    function escapeHtml(s){return String(s).replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]))}

    function saveAnswer(qid, ans){
        fetch('api/save-answer.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({csrf:T.csrf, test_id:T.testId, question_id:qid, answer:ans})
        }).catch(()=>{});
    }

    function submit(auto){
        if (submitting) return; submitting = true;
        fetch('api/submit-test.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({csrf:T.csrf, test_id:T.testId})
        }).then(r=>r.json()).then(j=>{
            if (j.redirect) location.href = j.redirect;
        });
    }

    prev.addEventListener('click', ()=>{ if (idx>0){ idx--; render(); }});
    next.addEventListener('click', ()=>{
        if (idx < T.questions.length-1) { idx++; render(); }
        else if (confirm('Submit your assessment for scoring?')) submit(false);
    });

    render();
})();
