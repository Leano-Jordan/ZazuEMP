<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#245A9A"><meta name="mobile-web-app-capable" content="yes"><link rel="manifest" href="/manifest.webmanifest"><title>Zazu · Mobile</title>
<style>
:root{color-scheme:light;font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif;--bg:#EAF1F8;--surface:#F9FBFD;--surface2:#E2ECF5;--ink:#183047;--muted:#5D7185;--line:#C5D5E2;--blue:#2563EB;--blue2:#DBEAFE;--blue3:#1D4ED8;--teal:#0F766E;--teal2:#CCFBF1;--success:#137333;--warn:#92400E}
*{box-sizing:border-box}body{margin:0;background:linear-gradient(145deg,#EEF4FA,#F8FAFD 55%,#E4EEF7);color:var(--ink);min-height:100vh}
main{width:min(1120px,100%);margin:auto;padding:18px 16px 52px}
header{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px}.brand{display:flex;align-items:center;gap:10px}.mark{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;background:var(--blue);color:#fff;font-weight:850}h1{font-size:1.25rem;margin:0}.state{font-size:.84rem;color:var(--muted);border:1px solid var(--line);background:var(--surface);border-radius:999px;padding:7px 10px}
.hero{padding:20px;border:1px solid #B8CCDD;border-radius:16px;background:linear-gradient(135deg,#DDEBF7,#F9FBFD 72%);margin-bottom:12px}.hero h2{margin:0 0 5px;font-size:1.35rem}.hero p{margin:0;color:var(--muted);line-height:1.5}.hero-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
nav{display:grid;grid-template-columns:repeat(5,1fr);gap:7px;margin:12px 0}.tab{border:1px solid var(--line);background:var(--surface);color:var(--muted);border-radius:9px;padding:10px 8px;font-weight:750}.tab.active{background:var(--blue);border-color:var(--blue);color:#fff}
.grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.card,.panel{border:1px solid var(--line);background:var(--surface);border-radius:13px;box-shadow:0 4px 14px rgba(34,67,98,.07)}.card{padding:14px}.eyebrow{font-size:.84rem;text-transform:uppercase;letter-spacing:.08em;color:#54789B}.value{font-size:1.65rem;font-weight:850;margin-top:4px}.muted{color:var(--muted);font-size:.92rem}.panel{padding:16px}.panel h2{margin:0 0 12px;font-size:1rem}
.list{display:grid;gap:7px}.row{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:11px;border:1px solid var(--line);background:var(--surface2);border-radius:10px}.row strong{display:block}.row span{display:block;color:var(--muted);font-size:.9rem;margin-top:3px}.row button{flex:0 0 auto}
.button{border:1px solid var(--blue);border-radius:9px;padding:10px 13px;background:var(--blue);color:#fff;font-weight:800}.button.secondary{background:var(--surface);border-color:var(--line);color:var(--ink)}.button[hidden]{display:none}.install-note{margin-top:8px;color:var(--muted);font-size:.84rem}
.form{display:grid;gap:10px;max-width:720px}.form label{display:grid;gap:5px;font-size:.9rem;font-weight:750}.form input,.form select,.form textarea{width:100%;border:1px solid #AFC3D4;background:var(--surface2);color:var(--ink);border-radius:8px;padding:10px 11px;font:inherit}.form textarea{min-height:90px;resize:vertical}.form-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:4px}.notice{padding:12px;border-left:4px solid var(--blue);background:var(--blue2);border-radius:8px;color:#31536F;font-size:.92rem}.empty{padding:30px 10px;text-align:center;color:var(--muted)}
@media(max-width:760px){main{padding:12px 11px 36px}.grid{grid-template-columns:repeat(2,1fr)}nav{grid-template-columns:repeat(3,1fr)}.hero{padding:16px}}
@media(max-width:430px){nav{grid-template-columns:repeat(2,1fr)}.row{align-items:flex-start}.grid{gap:8px}.card{padding:12px}}
</style>
</head>
<body>
<main>
<header><div class="brand"><span class="mark">Z</span><h1>Zazu EMP</h1></div><span class="state" id="state">Local phone workspace</span><span class="state" id="queue-state">0 pending</span></header>
<section class="hero">
<h2 id="business-name">Run Zazu from your phone</h2>
<p id="hero-copy">Your phone can hold a local Zazu workspace. No laptop is required for everyday mobile operation.</p>
<div class="hero-actions">
<button class="button" id="new-customer">New customer</button><button class="button secondary" id="new-job">New job</button><button class="button secondary" id="new-quote">New quote</button><button class="button secondary" id="install-zazu" hidden>Install Zazu</button>
</div>
</section>
<nav aria-label="Zazu mobile sections">
<button class="tab active" data-tab="overview">Overview</button><button class="tab" data-tab="customers">Customers</button><button class="tab" data-tab="jobs">Jobs</button><button class="tab" data-tab="quotes">Quotes</button><button class="tab" data-tab="services">Services</button>
</nav>
<section id="content"></section>
</main>
<script>
const DB='zazu-phone-workspace',STORE='workspace',QUEUE='mutations';let data=null,tab='overview';
const blank=()=>({version:new Date().toISOString(),business:{id:'local',name:'My Zazu Business',currency:'ZAR'},customers:[],events:[],quotes:[],capabilities:[]});
function openDb(){return new Promise((ok,no)=>{const r=indexedDB.open(DB,3);r.onupgradeneeded=()=>{if(!r.result.objectStoreNames.contains(STORE))r.result.createObjectStore(STORE);if(!r.result.objectStoreNames.contains(QUEUE)){const s=r.result.createObjectStore(QUEUE,{keyPath:'id'});s.createIndex('status','status');s.createIndex('created_at','created_at')}};r.onsuccess=()=>ok(r.result);r.onerror=()=>no(r.error)})}
async function save(){const db=await openDb();return new Promise((ok,no)=>{const tx=db.transaction(STORE,'readwrite');tx.objectStore(STORE).put(data,'current');tx.oncomplete=ok;tx.onerror=()=>no(tx.error)})}
async function load(){const db=await openDb();return new Promise((ok,no)=>{const q=db.transaction(STORE).objectStore(STORE).get('current');q.onsuccess=()=>ok(q.result||null);q.onerror=()=>no(q.error)})}
async function queueMutation(type,action,payload){const db=await openDb();const mutation={id:crypto.randomUUID(),entity_type:type,action,payload,created_at:new Date().toISOString(),status:'pending'};return new Promise((ok,no)=>{const tx=db.transaction(QUEUE,'readwrite');tx.objectStore(QUEUE).put(mutation);tx.oncomplete=async()=>{await refreshQueueState();ok(mutation)};tx.onerror=()=>no(tx.error)})}
async function pendingMutations(){const db=await openDb();return new Promise((ok,no)=>{const q=db.transaction(QUEUE).objectStore(QUEUE).index('status').getAll('pending');q.onsuccess=()=>ok(q.result||[]);q.onerror=()=>no(q.error)})}
async function refreshQueueState(){const count=(await pendingMutations()).length;document.getElementById('queue-state').textContent=count+' pending';return count}
const esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
function header(){document.getElementById('business-name').textContent=data.business.name;document.getElementById('hero-copy').textContent=data.customers.length+' customers · '+data.events.length+' jobs · '+data.quotes.length+' quotes · '+data.capabilities.length+' services stored on this phone.'}
function render(){header();document.querySelectorAll('.tab').forEach(b=>b.classList.toggle('active',b.dataset.tab===tab));const c=document.getElementById('content');
if(tab==='overview')c.innerHTML='<section class="grid"><div class="card"><div class="eyebrow">Customers</div><div class="value">'+data.customers.length+'</div><div class="muted">Contacts you can use offline</div></div><div class="card"><div class="eyebrow">Jobs</div><div class="value">'+data.events.length+'</div><div class="muted">Work stored locally</div></div><div class="card"><div class="eyebrow">Quotes</div><div class="value">'+data.quotes.length+'</div><div class="muted">Commercial records</div></div><div class="card"><div class="eyebrow">Services</div><div class="value">'+data.capabilities.length+'</div><div class="muted">Your catalogue</div></div></section><section class="panel" style="margin-top:12px"><div class="notice">This workspace runs from this phone's local storage. The PC is only used to seed the first copy while it is available; once loaded, the phone does not need the PC to reopen or edit these records.</div><div class="install-note">Install Zazu to the home screen so it opens like an app.</div></section>';
if(tab==='customers') list('Customers',data.customers,'customer');
if(tab==='jobs') list('Jobs',data.events,'job');
if(tab==='quotes') list('Quotes',data.quotes,'quote');
if(tab==='services') list('Services',data.capabilities,'service');
}
function list(title,items,type){document.getElementById('content').innerHTML='<section class="panel"><h2>'+title+'</h2><div class="list">'+(items.length?items.map((x,i)=>'<div class="row"><div><strong>'+esc(x.name||x.reference||'Untitled')+'</strong><span>'+esc(x.phone||x.event_date||x.event_name||x.description||x.status||'Local record')+'</span></div><button class="button secondary" onclick="editRecord(\\''+type+'\\','+i+')">Open</button></div>').join(''):'<div class="empty">Nothing here yet. Use the action above to create your first '+type+'.</div>')+'</div></section>'}
function form(type,index){const edit=index!==undefined;let x=edit?({customer:data.customers,job:data.events,quote:data.quotes,service:data.capabilities}[type][index]):{};let title=(edit?'Edit ':'New ')+type;
let fields=type==='customer'?'<label>Name<input id="f-name" value="'+esc(x.name)+'" required></label><label>Phone<input id="f-phone" value="'+esc(x.phone)+'" inputmode="tel"></label><label>Email<input id="f-email" value="'+esc(x.email)+'" type="email"></label>':type==='job'?'<label>Job name<input id="f-name" value="'+esc(x.name)+'" required></label><label>Event date<input id="f-date" value="'+esc(x.event_date||'')+'" type="date"></label><label>Customer<input id="f-customer" value="'+esc(x.customer_name||'')+'"></label><label>Notes<textarea id="f-notes">'+esc(x.notes)+'</textarea></label>':type==='quote'?'<label>Quote reference<input id="f-name" value="'+esc(x.reference||'')+'"></label><label>Job<input id="f-event" value="'+esc(x.event_name||'')+'"></label><label>Status<select id="f-status"><option>draft</option><option>sent</option><option>accepted</option></select></label>':'<label>Service name<input id="f-name" value="'+esc(x.name)+'" required></label><label>Description<textarea id="f-description">'+esc(x.description)+'</textarea></label>';
document.getElementById('content').innerHTML='<section class="panel"><h2>'+title+'</h2><form class="form" id="record-form">'+fields+'<div class="form-actions"><button class="button" type="submit">Save on phone</button><button class="button secondary" type="button" id="cancel-form">Cancel</button></div></form></section>';
document.getElementById('record-form').onsubmit=async e=>{e.preventDefault();let target={customer:data.customers,job:data.events,quote:data.quotes,service:data.capabilities}[type];let n={...x};if(type==='customer'){n.name=f('f-name');n.phone=f('f-phone');n.email=f('f-email')}if(type==='job'){n.name=f('f-name');n.event_date=f('f-date');n.customer_name=f('f-customer');n.notes=f('f-notes');n.status=n.status||'draft'}if(type==='quote'){n.reference=f('f-name')||'Quote '+(target.length+1);n.event_name=f('f-event');n.status=document.getElementById('f-status').value;n.currency='ZAR'}if(type==='service'){n.name=f('f-name');n.description=f('f-description');n.active=true}n.local_id=n.local_id||crypto.randomUUID();n.updated_at=new Date().toISOString();if(edit)target[index]=n;else target.push(n);data.local_dirty=true;await queueMutation(type,edit?'update':'create',{local_id:n.local_id,server_id:n.id??null,record:n});await save();tab=type==='customer'?'customers':type==='job'?'jobs':type==='quote'?'quotes':'services';render();document.getElementById('state').textContent='Saved on phone'};
document.getElementById('cancel-form').onclick=render}
function f(id){return document.getElementById(id)?.value?.trim()||''}
function editRecord(type,i){form(type,i)}
function newRecord(type){form(type)}
document.querySelectorAll('.tab').forEach(b=>b.onclick=()=>{tab=b.dataset.tab;render()});
document.getElementById('new-customer').onclick=()=>newRecord('customer');document.getElementById('new-job').onclick=()=>newRecord('job');document.getElementById('new-quote').onclick=()=>newRecord('quote');
let deferredInstallPrompt = null;

function setState(){
    document.getElementById('state').textContent = navigator.onLine ? 'Phone workspace · local' : 'Phone workspace · offline';
}

window.addEventListener('online', setState);
window.addEventListener('offline', setState);

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    document.getElementById('install-zazu').hidden = false;
});

document.getElementById('install-zazu').addEventListener('click', async () => {
    if (!deferredInstallPrompt) return;
    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    document.getElementById('install-zazu').hidden = true;
});

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    document.getElementById('install-zazu').hidden = true;
    document.getElementById('state').textContent = 'Zazu installed on phone';
});

async function boot(){
    data = await load();

    if (!data) {
        try {
            const r = await fetch('/offline/bootstrap', {
                headers: {Accept:'application/json'},
                credentials:'same-origin'
            });

            if (r.ok) {
                data = await r.json();
                data.local_dirty = false;
                await save();
                document.getElementById('state').textContent = 'Phone copy ready';
            }
        } catch (e) {}
    }

    if (!data) {
        data = blank();
        data.local_dirty = false;
        await save();
        document.getElementById('state').textContent = 'Phone workspace ready';
    }

    setState();
    await refreshQueueState();
    render();
}

if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(()=>{});
boot();
</script>
</body>
</html>