// MindMetric — vanilla canvas charts using palette colors
(function(){
    const PALETTE = {
        bg: '#FCD6AA', surface:'#D6E4FA', ink:'#151511',
        accent:'#F8BC7C', accentStrong:'#B77E3F'
    };

    function setupCanvas(c){
        const dpr = window.devicePixelRatio || 1;
        const w = c.clientWidth, h = c.clientHeight;
        c.width = w*dpr; c.height = h*dpr;
        const ctx = c.getContext('2d');
        ctx.scale(dpr,dpr);
        return {ctx,w,h};
    }

    function lineChart(canvas, data, valueKey){
        if (!canvas) return;
        const {ctx,w,h} = setupCanvas(canvas);
        ctx.clearRect(0,0,w,h);
        const pad = {l:36,r:14,t:18,b:26};
        const cw = w-pad.l-pad.r, ch = h-pad.t-pad.b;
        ctx.strokeStyle = PALETTE.ink; ctx.lineWidth=1;
        ctx.strokeRect(pad.l, pad.t, cw, ch);
        if (!data.length){
            ctx.fillStyle = '#888'; ctx.font='13px sans-serif';
            ctx.fillText('No data yet — take a test to see your trend.', pad.l+10, pad.t+ch/2);
            return;
        }
        const vals = data.map(d=>d[valueKey]);
        const max = Math.max(100, ...vals), min = 0;
        // grid
        ctx.strokeStyle = '#15151122'; ctx.beginPath();
        for (let i=1;i<4;i++){ const y = pad.t + (ch*i/4); ctx.moveTo(pad.l,y); ctx.lineTo(pad.l+cw,y); }
        ctx.stroke();
        // line
        ctx.strokeStyle = PALETTE.accentStrong; ctx.lineWidth=2.5; ctx.beginPath();
        data.forEach((d,i)=>{
            const x = pad.l + (data.length===1?cw/2:(cw*i/(data.length-1)));
            const y = pad.t + ch - ((d[valueKey]-min)/(max-min))*ch;
            if (i===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
        });
        ctx.stroke();
        // points
        ctx.fillStyle = PALETTE.ink;
        data.forEach((d,i)=>{
            const x = pad.l + (data.length===1?cw/2:(cw*i/(data.length-1)));
            const y = pad.t + ch - ((d[valueKey]-min)/(max-min))*ch;
            ctx.beginPath(); ctx.arc(x,y,4,0,Math.PI*2); ctx.fill();
        });
        // x labels (sparse)
        ctx.fillStyle = PALETTE.ink; ctx.font='11px sans-serif'; ctx.textAlign='center';
        const step = Math.max(1, Math.floor(data.length/5));
        data.forEach((d,i)=>{
            if (i%step!==0 && i!==data.length-1) return;
            const x = pad.l + (data.length===1?cw/2:(cw*i/(data.length-1)));
            ctx.fillText(d.date, x, pad.t+ch+16);
        });
        // y axis labels
        ctx.textAlign='right';
        [0,50,100].forEach(v=>{
            const y = pad.t + ch - (v/max)*ch;
            ctx.fillText(v+'%', pad.l-6, y+3);
        });
    }

    function radarChart(canvas, dataObj){
        if (!canvas) return;
        const {ctx,w,h} = setupCanvas(canvas);
        ctx.clearRect(0,0,w,h);
        const keys = Object.keys(dataObj);
        if (!keys.length){
            ctx.fillStyle = '#888'; ctx.font='13px sans-serif';
            ctx.fillText('Take a test to populate category data.', 20, h/2);
            return;
        }
        const cx=w/2, cy=h/2+4, R=Math.min(w,h)/2 - 36;
        ctx.strokeStyle = PALETTE.ink; ctx.lineWidth=1;
        // rings
        ctx.strokeStyle = '#15151133';
        for (let r=1;r<=4;r++){
            ctx.beginPath();
            keys.forEach((_,i)=>{
                const a = -Math.PI/2 + i*(Math.PI*2/keys.length);
                const x = cx + Math.cos(a)*(R*r/4);
                const y = cy + Math.sin(a)*(R*r/4);
                if (i===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
            });
            ctx.closePath(); ctx.stroke();
        }
        // axes & labels
        ctx.fillStyle = PALETTE.ink; ctx.font='11px sans-serif';
        keys.forEach((k,i)=>{
            const a = -Math.PI/2 + i*(Math.PI*2/keys.length);
            const x = cx + Math.cos(a)*R;
            const y = cy + Math.sin(a)*R;
            ctx.strokeStyle = '#15151133'; ctx.beginPath(); ctx.moveTo(cx,cy); ctx.lineTo(x,y); ctx.stroke();
            ctx.textAlign = Math.cos(a)>0.3?'left':(Math.cos(a)<-0.3?'right':'center');
            const lx = cx + Math.cos(a)*(R+14), ly = cy + Math.sin(a)*(R+14);
            ctx.fillText(k.length>16?k.slice(0,14)+'…':k, lx, ly);
        });
        // shape
        ctx.fillStyle = PALETTE.accent + 'cc';
        ctx.strokeStyle = PALETTE.accentStrong; ctx.lineWidth=2;
        ctx.beginPath();
        keys.forEach((k,i)=>{
            const a = -Math.PI/2 + i*(Math.PI*2/keys.length);
            const v = Math.max(0, Math.min(100, dataObj[k]))/100;
            const x = cx + Math.cos(a)*R*v;
            const y = cy + Math.sin(a)*R*v;
            if (i===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
        });
        ctx.closePath(); ctx.fill(); ctx.stroke();
    }

    if (window.MM_TREND) lineChart(document.getElementById('trendChart'), window.MM_TREND, 'pct');
    if (window.MM_CATS)  radarChart(document.getElementById('categoryChart'), window.MM_CATS);
    if (window.MM_RESULT) radarChart(document.getElementById('resultChart'), window.MM_RESULT);
})();
