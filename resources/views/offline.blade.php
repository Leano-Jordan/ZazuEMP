<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#123B6D"><link rel="manifest" href="/manifest.webmanifest"><title>Zazu · Phone Workspace</title>
<style>
:root{color-scheme:dark;font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif;background:#081321;color:#E8F1FC}
*{box-sizing:border-box}body{margin:0;background:linear-gradient(145deg,#081321,#0D1D31 55%,#102B48);min-height:100vh}
main{width:min(1080px,100%);margin:auto;padding:22px 16px 48px}header{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:20px}
.brand{display:flex;gap:10px;align-items:center}.mark{display:grid;place-items:center;width:38px;height:38px;border-radius:11px;background:#2E6FBE;color:white;font-weight:800}
h1{font-size:1.3rem;margin:0}.state{font-size:.78rem;color:#AFC7E2;border:1px solid #294A6B;border-radius:999px;padding:7px 10px}
.hero,.panel{background:#102038;border:1px solid #23476B;border-radius:18px;box-shadow:0 14px 32px #050B14;padding:18px}
.hero{margin-bottom:14px;background:linear-gradient(135deg,#12345A,#102038 68%)}.hero h2{margin:0 0 6px;font-size:1.25rem}.hero p{margin:0;color:#AFC2D8;line-height:1.5}
nav{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:14px 0}.tab{border:1px solid #294A6B;background:#102038;color:#C8DBEF;border-radius:11px;padding:11px 8px;font-weight:700}.tab.active{background:#1D4F86;border-color:#4E8DCE;color:#fff}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.card{padding:14px;border:1px solid #24486B;background:#11243A;border-radius:14px}.eyebrow{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#7FA8D2}.value{font-size:1.7rem;font-weight:800;margin-top:4px}.muted{color:#9EB5CD}
.list{display:grid;gap:8px}.row{padding:12px;border:1px solid #254B70;background:#0F2035;border-radius:12px}.row strong{display:block}.row span{display:block;color:#9EB5CD;font-size:.88rem;margin-top:3px}
.empty{padding:28px 10px;text-align:center;color:#9EB5CD}.offline-note{margin-top:14px;color:#AFC7E2;font-size:.84rem}
@media(max-width:650px){main{padding:14px 12px 32px}header{align-items:flex-start}.state{font-size:.7rem}.grid{grid-template-columns:1fr}nav{grid-template-columns:repeat(2,1fr)}.hero,.panel{border-radius:15px;padding:15px}}
</style>
</head>
<body>
<main>
<header><div class="brand"><span class="mark">Z</span><h1>Zazu EMP</h1></div><span class="state" id="state">Phone workspace</span></header>
<section class="hero"><h2 id="business-name">Your Zazu workspace</h2><p id="hero-copy">Preparing the phone workspace…</p><div class="offline-note">This phone keeps a local working copy. It does not need the PC to open this workspace after preparation.</div></section>
<nav aria-label="Phone workspace sections">
<button class="tab active" data-tab="overview">Overview</button><button class="tab" data-tab="customers">Customers</button><button class="tab" data-tab="events">Jobs</button><button class="tab" data-tab="quotes">Quotes</button>
</nav>
<section id="content"></section>
</main>
<script>
var DB='zazu-phone-workspace',STORE='snapshot',data=null;
function openDb(){return new Promise(function(resolve,reject){var r=indexedDB.open(DB,1);r.onupgradeneeded=function(){r.result.createObjectStore(STORE)};r.onsuccess=function(){resolve(r.result)};r.onerror=function(){reject(r.error)}})}
function saveSnapshot(value){return openDb().then(function(db){return new Promise(function(resolve,reject){var tx=db.transaction(STORE,'readwrite');tx.objectStore(STORE).put(value,'current');tx.oncomplete=resolve;tx.onerror=function(){reject(tx.error)}})})}
function loadSnapshot(){return openDb().then(function(db){return new Promise(function(resolve,reject){var tx=db.transaction(STORE,'readonly');var r=tx.objectStore(STORE).get('current');r.onsuccess=function(){resolve(r.result||null)};r.onerror=function(){reject(r.error)}})})}
function esc(value){return String(value==null?'':value).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]})}
function render(tab){
document.querySelectorAll('.tab').forEach(function(b){b.classList.toggle('active',b.dataset.tab===tab)});
var c=document.getElementById('content');
if(!data){c.innerHTML='<section class="panel empty">No local workspace is stored on this phone yet.<br><br>Open Zazu on the PC while connected and use <b>Prepare this phone</b>.</section>';return}
document.getElementById('business-name').textContent=data.business.name||'Zazu workspace';
document.getElementById('hero-copy').textContent=data.customers.length+' customers · '+data.events.length+' jobs · '+data.quotes.length+' quotes stored locally.';
if(tab==='overview')c.innerHTML='<section class="grid"><div class="card"><div class="eyebrow">Customers</div><div class="value">'+data.customers.length+'</div><div class="muted">Available on this phone</div></div><div class="card"><div class="eyebrow">Jobs</div><div class="value">'+data.events.length+'</div><div class="muted">Available on this phone</div></div><div class="card"><div class="eyebrow">Quotes</div><div class="value">'+data.quotes.length+'</div><div class="muted">Available on this phone</div></div><div class="card"><div class="eyebrow">Services</div><div class="value">'+data.capabilities.length+'</div><div class="muted">Available on this phone</div></div></section>';
if(tab==='customers')c.innerHTML='<section class="panel"><h2>Customers</h2><div class="list">'+data.customers.map(function(x){return '<div class="row"><strong>'+esc(x.name)+'</strong><span>'+esc(x.phone||x.email||x.notes||'No contact details')+'</span></div>'}).join('')+' </div></section>';
if(tab==='events')c.innerHTML='<section class="panel"><h2>Jobs</h2><div class="list">'+data.events.map(function(x){return '<div class="row"><strong>'+esc(x.name)+'</strong><span>'+esc(x.event_date||'Unscheduled')+' · '+esc(x.customer_name||'No customer')+' · '+esc(x.status||'draft')+'</span></div>'}).join('')+'</div></section>';
if(tab==='quotes')c.innerHTML='<section class="panel"><h2>Quotes</h2><div class="list">'+data.quotes.map(function(x){return '<div class="row"><strong>'+esc(x.reference||'Quote')+'</strong><span>'+esc(x.event_name||'No job')+' · '+esc(x.status||'draft')+'</span></div>'}).join('')+'</div></section>';
}
function prepare(){fetch('/offline/bootstrap',{headers:{Accept:'application/json'},credentials:'same-origin'}).then(function(r){if(!r.ok)throw new Error('bootstrap failed');return r.json()}).then(function(value){data=value;return saveSnapshot(value)}).then(function(){render('overview');document.getElementById('state').textContent='Saved on this phone'}).catch(function(){return loadSnapshot().then(function(value){data=value;render('overview');document.getElementById('state').textContent=data?'Offline':'Not prepared'})})}
if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(function(){});
document.querySelectorAll('.tab').forEach(function(b){b.addEventListener('click',function(){render(b.dataset.tab)})});prepare();
</script>
</body>
</html>