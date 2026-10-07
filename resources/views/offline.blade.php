<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0B3A66"><meta name="mobile-web-app-capable" content="yes"><link rel="manifest" href="/manifest.webmanifest"><title>Zazu · Mobile</title>
<style>
:root{color-scheme:dark;font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif;--bg:#0B3A66;--surface:#102C44;--surface2:#163A56;--ink:#F1F7FC;--muted:#A4BACC;--line:#365A75;--blue:#0B63CE;--blue2:#123C5F;--blue3:#1D78E5;--teal:#0E8F83;--teal2:#0F3B3A;--success:#6FD19A;--warn:#E5B96C}
*{box-sizing:border-box}body{margin:0;background:linear-gradient(160deg,var(--bg),#082D4F 100%);color:var(--ink);min-height:100vh}
main{width:min(1120px,100%);margin:auto;padding:18px 16px 52px}
header{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px}.brand{display:flex;align-items:center;gap:10px}.mark{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;background:var(--blue);color:#fff;font-weight:850}h1{font-size:1.25rem;margin:0}.state{font-size:.84rem;color:var(--muted);border:1px solid var(--line);background:var(--surface);border-radius:999px;padding:7px 10px}
.hero{padding:20px;border:1px solid var(--line);border-radius:16px;background:linear-gradient(135deg,var(--surface),var(--surface2) 72%);color:var(--ink);margin-bottom:12px}.hero h2{margin:0 0 5px;font-size:1.35rem;color:var(--ink)}.hero p{margin:0;color:var(--muted);line-height:1.5}.hero-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
nav{display:grid;grid-template-columns:repeat(5,1fr);gap:7px;margin:12px 0}.tab{border:1px solid var(--line);background:var(--surface);color:var(--muted);border-radius:9px;padding:10px 8px;font-weight:750}.tab.active{background:var(--blue);border-color:var(--blue);color:#fff}
.grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.card,.panel{border:1px solid var(--line);background:var(--surface);border-radius:13px;box-shadow:0 4px 14px rgba(34,67,98,.07)}.card{padding:14px}.eyebrow{font-size:.84rem;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}.value{font-size:1.65rem;font-weight:850;margin-top:4px}.muted{color:var(--muted);font-size:.92rem}.panel{padding:16px}.panel h2{margin:0 0 12px;font-size:1rem}
.list{display:grid;gap:7px}.row{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:11px;border:1px solid var(--line);background:var(--surface2);border-radius:10px}.row strong{display:block}.row span{display:block;color:var(--muted);font-size:.9rem;margin-top:3px}.row button{flex:0 0 auto}
.button{border:1px solid var(--blue);border-radius:9px;padding:10px 13px;background:var(--blue);color:#fff;font-weight:800}.button.secondary{background:var(--surface);border-color:var(--line);color:var(--ink)}.button[hidden]{display:none}.install-note{margin-top:8px;color:var(--muted);font-size:.84rem}
.form{display:grid;gap:10px;max-width:720px}.form label{display:grid;gap:5px;font-size:.9rem;font-weight:750}.form input,.form select,.form textarea{width:100%;border:1px solid #AFC3D4;background:var(--surface2);color:var(--ink);border-radius:8px;padding:10px 11px;font:inherit}.form textarea{min-height:90px;resize:vertical}.form-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:4px}.notice{padding:12px;border-left:4px solid var(--blue);background:var(--blue2);border-radius:8px;color:var(--ink);font-size:.92rem}.empty{padding:30px 10px;text-align:center;color:var(--muted)}
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
<button class="button" id="new-customer">New customer</button><button class="button secondary" id="new-job">New job</button><button class="button secondary" id="new-quote" disabled title="Quote editing will be enabled in a later offline domain release">Quotes stay read-only offline</button><button class="button secondary" id="pair-zazu">Connect to business</button><button class="button secondary" id="install-zazu" hidden>Install Zazu</button>
</div>
</section>
<nav aria-label="Zazu mobile sections">
<button class="tab active" data-tab="overview">Overview</button><button class="tab" data-tab="customers">Customers</button><button class="tab" data-tab="jobs">Jobs</button><button class="tab" data-tab="quotes">Quotes</button><button class="tab" data-tab="services">Services</button><button class="tab" data-tab="preparation">Preparation</button><button class="tab" data-tab="purchasing">Purchasing</button>
</nav>
<section id="content"></section>
</main>
<script>
const DB='zazu-phone-workspace',STORE='workspace',QUEUE='mutations';let data=null,tab='overview';
const blank=()=>({version:new Date().toISOString(),business:{id:'local',name:'My Zazu Business',currency:'ZAR'},customers:[],events:[],quotes:[],capabilities:[],preparations:[],suppliers:[],purchase_orders:[]});
function openDb(){return new Promise((ok,no)=>{const r=indexedDB.open(DB,3);r.onupgradeneeded=()=>{if(!r.result.objectStoreNames.contains(STORE))r.result.createObjectStore(STORE);if(!r.result.objectStoreNames.contains(QUEUE)){const s=r.result.createObjectStore(QUEUE,{keyPath:'id'});s.createIndex('status','status');s.createIndex('created_at','created_at')}};r.onsuccess=()=>ok(r.result);r.onerror=()=>no(r.error)})}
async function requestPersistentStorage(){if(navigator.storage?.persist){try{await navigator.storage.persist()}catch(e){}}}
async function save(){const db=await openDb();return new Promise((ok,no)=>{const tx=db.transaction(STORE,'readwrite');tx.objectStore(STORE).put(data,'current');tx.oncomplete=ok;tx.onerror=()=>no(tx.error)})}
async function load(){const db=await openDb();return new Promise((ok,no)=>{const q=db.transaction(STORE).objectStore(STORE).get('current');q.onsuccess=()=>ok(q.result||null);q.onerror=()=>no(q.error)})}
async function queueMutation(type,action,payload){const db=await openDb();const mutation={id:crypto.randomUUID(),entity_type:type,action,payload,created_at:new Date().toISOString(),status:'pending'};return new Promise((ok,no)=>{const tx=db.transaction(QUEUE,'readwrite');tx.objectStore(QUEUE).put(mutation);tx.oncomplete=async()=>{await refreshQueueState();ok(mutation)};tx.onerror=()=>no(tx.error)})}
async function pendingMutations(){const db=await openDb();return new Promise((ok,no)=>{const q=db.transaction(QUEUE).objectStore(QUEUE).index('status').getAll('pending');q.onsuccess=()=>ok((q.result||[]).sort((a,b)=>String(a.created_at).localeCompare(String(b.created_at))));q.onerror=()=>no(q.error)})}
async function refreshQueueState(){const pending=await pendingMutations();const failed=pending.filter(m=>Boolean(m.last_error)).length;const waiting=pending.length-failed;document.getElementById('queue-state').textContent=failed?waiting+' pending · '+failed+' failed':waiting+' pending';return pending.length}
async function syncFetch(path,options={}){if(!data?.sync?.token)return null;const headers={Accept:'application/json',Authorization:'Bearer '+data.sync.token,...(options.headers||{})};return fetch(path,{...options,headers})}
async function pairPhone(){const code=window.prompt('Enter the 6-digit pairing code shown in Zazu on the owner PC.');if(!code)return;const r=await fetch('/api/sync/provision',{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify({pairing_code:code,device_name:'Zazu phone',device_type:'phone'})});if(!r.ok){document.getElementById('state').textContent='Pairing failed';return}const result=await r.json();data.sync={token:result.token,device:result.device};applyBootstrap(result.bootstrap);data.local_dirty=false;await save();document.getElementById('state').textContent='Connected to Zazu';await pushPending();render()}
function applyBootstrap(bootstrap){if(!bootstrap)return;data.business=bootstrap.business||data.business;data.capabilities=(bootstrap.catalogue||[]).map(x=>({...x.record,local_id:x.local_id,id:x.server_id}));const targets={customer:data.customers,job:data.events,quote:data.quotes,service:data.capabilities,preparation:data.preparations};data.suppliers=(bootstrap.suppliers||[]).map(x=>({...x.record,local_id:x.local_id,id:x.server_id}));data.purchase_orders=(bootstrap.purchase_orders||[]).map(x=>({...x.record,local_id:x.local_id,id:x.server_id,items:(x.items||[]).map(item=>({...item.record,local_id:item.local_id,id:item.server_id}))}));for(const item of bootstrap.selected||[]){const target=targets[item.type];if(!target)continue;const record={...item.record,local_id:item.local_id,id:item.server_id};if(item.type==='preparation'){const parent=data.events.find(e=>Number(e.id)===Number(record.event_id));if(parent)record.event_local_id=parent.local_id;}const existing=target.findIndex(x=>x.local_id===item.local_id||x.id===item.server_id);if(existing>=0)target[existing]=record;else target.push(record)}}
async function pushPending(){if(!navigator.onLine||!data?.sync?.token)return;const mutations=await pendingMutations();if(!mutations.length)return;const r=await syncFetch('/api/sync/push',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({mutations:mutations.map(m=>({id:m.id,entity_type:m.entity_type,entity_id:m.payload?.local_id||m.payload?.server_id||m.id,operation:m.action,payload:m.payload||{}}))})});if(!r||!r.ok){document.getElementById('state').textContent='Offline changes waiting to sync';return;}const result=await r.json();const recorded=new Map((result.recorded||[]).map(x=>[x.id,x]));const db=await openDb();await new Promise((ok,no)=>{const tx=db.transaction(QUEUE,'readwrite');const store=tx.objectStore(QUEUE);mutations.forEach(m=>{const remote=recorded.get(m.id);if(!remote)return;if(remote.status==='applied'){store.delete(m.id)}else{store.put({...m,server_sequence:remote.sequence,server_status:remote.status,last_sent_at:new Date().toISOString(),last_error:remote.error||'Server rejected this offline change'})}});tx.oncomplete=ok;tx.onerror=()=>no(tx.error)});data.local_dirty=(await pendingMutations()).length>0;await save();await refreshQueueState();if(!data.local_dirty){try{const bootstrap=await syncFetch('/api/sync/bootstrap');if(bootstrap?.ok){applyBootstrap(await bootstrap.json());await save();}}catch(e){}}document.getElementById('state').textContent=data.local_dirty?'Offline changes waiting to sync':'Saved locally · server receipt recorded';}
const esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
function header(){document.getElementById('business-name').textContent=data.business.name;document.getElementById('hero-copy').textContent=data.customers.length+' customers · '+data.events.length+' jobs · '+data.quotes.length+' quotes · '+data.capabilities.length+' services stored on this phone.'}
function render(){header();document.querySelectorAll('.tab').forEach(b=>b.classList.toggle('active',b.dataset.tab===tab));const c=document.getElementById('content');
if(tab==='overview')c.innerHTML='<section class="grid"><div class="card"><div class="eyebrow">Customers</div><div class="value">'+data.customers.length+'</div><div class="muted">Contacts you can use offline</div></div><div class="card"><div class="eyebrow">Jobs</div><div class="value">'+data.events.length+'</div><div class="muted">Work stored locally</div></div><div class="card"><div class="eyebrow">Quotes</div><div class="value">'+data.quotes.length+'</div><div class="muted">Commercial records</div></div><div class="card"><div class="eyebrow">Services</div><div class="value">'+data.capabilities.length+'</div><div class="muted">Your catalogue</div></div></section><section class="panel" style="margin-top:12px"><div class="notice">This workspace runs from this phone's local storage. The PC is only used to seed the first copy while it is available; once loaded, the phone does not need the PC to reopen or edit these records.</div><div class="install-note">Install Zazu to the home screen so it opens like an app.</div></section>';
if(tab==='customers') list('Customers',data.customers,'customer');
if(tab==='jobs') list('Jobs',data.events,'job');
if(tab==='quotes') list('Quotes',data.quotes,'quote');
if(tab==='services') list('Services',data.capabilities,'service');
if(tab==='preparation') list('Preparation',data.preparations,'preparation');
if(tab==='purchasing') purchasingList();
}
function showReceipt(index){const order=data.purchase_orders[index];const item=(order.items||[])[0];if(!item){document.getElementById('state').textContent='This purchase order has no receivable lines';return;}document.getElementById('content').innerHTML='<section class="panel"><h2>Receive '+esc(order.reference||'purchase order')+'</h2><form class="form" id="receipt-form"><label>'+esc(item.description||'Item')+'<input id="f-received" value="'+esc(item.quantity||'0')+'" inputmode="decimal" required></label><div class="notice">Receiving is queued locally and applied to inventory when this phone reconnects.</div><div class="form-actions"><button class="button" type="submit">Save receipt</button><button class="button secondary" type="button" id="cancel-receipt">Cancel</button></div></form></section>';document.getElementById('cancel-receipt').onclick=purchasingList;document.getElementById('receipt-form').onsubmit=async e=>{e.preventDefault();if(!item.local_id){document.getElementById('state').textContent='This purchase order line has no local identity; reconnect once to refresh it';return;}const received=f('f-received');if(!received||Number(received)<=0){document.getElementById('state').textContent='Enter a positive received quantity';return;}const localId=crypto.randomUUID();await queueMutation('purchase_receipt','create',{local_id:localId,server_id:null,record:{purchase_order_local_id:order.local_id,received_quantities:{[item.local_id]:received}}});data.local_dirty=true;await save();document.getElementById('state').textContent='Receipt saved on phone · awaiting sync';purchasingList();};}
function purchasingList(){document.getElementById('content').innerHTML='<section class="panel"><h2>Purchasing</h2><div class="form-actions"><button class="button" onclick="newRecord(\'purchase_order\')">New purchase order</button></div><div class="list" style="margin-top:12px">'+(data.purchase_orders.length?data.purchase_orders.map((x,i)=>'<div class="row"><div><strong>'+esc(x.reference||'Purchase order')+'</strong><span>'+esc(x.status||'draft')+' · '+esc(x.currency||data.business.currency||'ZAR')+' '+esc(x.total_amount||'0.00')+'</span></div><div class="form-actions"><button class="button secondary" onclick="showReceipt('+i+')" '+((x.items||[]).length&&x.status==='ordered'?'':'disabled')+'>Receive</button></div></div>').join(''):'<div class="empty">No purchase orders stored locally.</div>')+'</div></section>'}
function list(title,items,type){document.getElementById('content').innerHTML='<section class="panel"><h2>'+title+'</h2><div class="list">'+(items.length?items.map((x,i)=>'<div class="row"><div><strong>'+esc(x.name||x.title||x.reference||'Untitled')+'</strong><span>'+esc(x.phone||x.event_date||x.event_name||x.description||x.status||'Local record')+'</span></div><button class="button secondary" onclick="editRecord(\\''+type+'\\','+i+')">Open</button></div>').join(''):'<div class="empty">Nothing here yet. Use the action above to create your first '+type+'.</div>')+'</div></section>'}
function form(type,index){const edit=index!==undefined;let x=edit?({customer:data.customers,job:data.events,quote:data.quotes,service:data.capabilities,preparation:data.preparations,purchase_order:data.purchase_orders}[type][index]):{};let title=(edit?'Edit ':'New ')+type;
let fields=type==='purchase_order'?'<label>Supplier<select id="f-supplier" required>'+data.suppliers.map((s,i)=>'<option value="'+esc(s.local_id||'')+'">'+esc(s.name||('Supplier '+(i+1)))+'</option>').join('')+'</select></label><label>Job<select id="f-event"><option value="">No job</option>'+data.events.map((e,i)=>'<option value="'+esc(e.local_id||'')+'">'+esc(e.name||e.reference||('Job '+(i+1)))+'</option>').join('')+'</select></label><label>Description<input id="f-description" required value="'+esc(x.items?.[0]?.description||'')+'"></label><label>Quantity<input id="f-quantity" required value="'+esc(x.items?.[0]?.quantity||'1')+'" inputmode="decimal"></label><label>Unit<input id="f-unit" value="'+esc(x.items?.[0]?.unit||'units')+'"></label><label>Unit price<input id="f-price" required value="'+esc(x.items?.[0]?.unit_price||'0.00')+'" inputmode="decimal"></label><label>Expected date<input id="f-date" value="'+esc(x.expected_at||'')+'" type="date"></label><label>Status<select id="f-status"><option value="draft">Draft</option><option value="ordered">Ordered</option></select></label><label>Notes<textarea id="f-notes">'+esc(x.notes||'')+'</textarea></label>':type==='preparation'?'<label>Job<select id="f-event" required>'+data.events.map((e,i)=>'<option value="'+esc(e.local_id||'')+'">'+esc(e.name||e.reference||('Job '+(i+1)))+'</option>').join('')+'</select></label><label>Task<input id="f-name" value="'+esc(x.title||'')+'" required></label><label>Category<input id="f-category" value="'+esc(x.category||'')+'"></label><label>Quantity<input id="f-quantity" value="'+esc(x.quantity??'')+'" inputmode="decimal"></label><label>Unit<input id="f-unit" value="'+esc(x.unit||'')+'"></label><label>Due date<input id="f-date" value="'+esc(x.due_date||'')+'" type="date"></label><label>Status<select id="f-status"><option value="open">Open</option><option value="blocked">Blocked</option><option value="ready">Ready</option></select></label><label>Notes<textarea id="f-notes">'+esc(x.notes||'')+'</textarea></label>':type==='customer'?'<label>Name<input id="f-name" value="'+esc(x.name)+'" required></label><label>Phone<input id="f-phone" value="'+esc(x.phone)+'" inputmode="tel"></label><label>Email<input id="f-email" value="'+esc(x.email)+'" type="email"></label>':type==='job'?'<label>Job name<input id="f-name" value="'+esc(x.name)+'" required></label><label>Event date<input id="f-date" value="'+esc(x.event_date||'')+'" type="date"></label><label>Customer<input id="f-customer" value="'+esc(x.customer_name||'')+'"></label><label>Notes<textarea id="f-notes">'+esc(x.notes)+'</textarea></label>':type==='quote'?'<label>Quote reference<input id="f-name" value="'+esc(x.reference||'')+'"></label><label>Job<input id="f-event" value="'+esc(x.event_name||'')+'"></label><label>Status<select id="f-status"><option>draft</option><option>sent</option><option>accepted</option></select></label>':'<label>Service name<input id="f-name" value="'+esc(x.name)+'" required></label><label>Description<textarea id="f-description">'+esc(x.description)+'</textarea></label>';
document.getElementById('content').innerHTML='<section class="panel"><h2>'+title+'</h2><form class="form" id="record-form">'+fields+'<div class="form-actions"><button class="button" type="submit">Save on phone</button><button class="button secondary" type="button" id="cancel-form">Cancel</button></div></form></section>';
document.getElementById('record-form').onsubmit=async e=>{e.preventDefault();let target={customer:data.customers,job:data.events,quote:data.quotes,service:data.capabilities,preparation:data.preparations,purchase_order:data.purchase_orders}[type];let n={...x};if(type==='customer'){n.name=f('f-name');n.phone=f('f-phone');n.email=f('f-email')}if(type==='job'){n.name=f('f-name');n.event_date=f('f-date');n.customer_name=f('f-customer');n.notes=f('f-notes');n.status=n.status||'draft'}if(type==='quote'){n.reference=f('f-name')||'Quote '+(target.length+1);n.event_name=f('f-event');n.status=document.getElementById('f-status').value;n.currency='ZAR'}if(type==='service'){n.name=f('f-name');n.description=f('f-description');n.active=true}if(type==='preparation'){n.title=f('f-name');n.category=f('f-category');n.quantity=f('f-quantity');n.unit=f('f-unit');n.due_date=f('f-date');n.status=document.getElementById('f-status').value;n.notes=f('f-notes');n.event_local_id=f('f-event')}
if(type==='purchase_order'){n.supplier_local_id=f('f-supplier');n.event_local_id=f('f-event')||null;n.currency=data.business.currency||'ZAR';n.expected_at=f('f-date');n.notes=f('f-notes');n.status=document.getElementById('f-status').value;n.items=[{id:x.items?.[0]?.id||null,local_id:x.items?.[0]?.local_id||crypto.randomUUID(),description:f('f-description'),quantity:f('f-quantity'),unit:f('f-unit'),unit_price:f('f-price')}];n.lines=n.items.map(({id,...line})=>line);n.total_amount=(Number(n.items[0].quantity||0)*Number(n.items[0].unit_price||0)).toFixed(2)}n.local_id=n.local_id||crypto.randomUUID();if(type==='preparation'&&!n.event_local_id&&data.events[0])n.event_local_id=data.events[0].local_id;n.updated_at=new Date().toISOString();if(edit)target[index]=n;else target.push(n);data.local_dirty=true;await queueMutation(type,edit?'update':'create',{local_id:n.local_id,server_id:n.id??null,record:n});await save();tab=type==='customer'?'customers':type==='job'?'jobs':type==='quote'?'quotes':type==='preparation'?'preparation':type==='purchase_order'?'purchasing':'services';render();document.getElementById('state').textContent='Saved on phone · awaiting sync'};
document.getElementById('cancel-form').onclick=render}
function f(id){return document.getElementById(id)?.value?.trim()||''}
function editRecord(type,i){form(type,i)}
function newRecord(type){form(type)}
document.querySelectorAll('.tab').forEach(b=>b.onclick=()=>{tab=b.dataset.tab;render()});
document.getElementById('new-customer').onclick=()=>newRecord('customer');document.getElementById('new-job').onclick=()=>newRecord('job');document.getElementById('new-quote').onclick=()=>{document.getElementById('state').textContent='Quote editing requires a connected Zazu business for now'};document.getElementById('pair-zazu').onclick=pairPhone;
let deferredInstallPrompt = null;

function setState(){
    document.getElementById('state').textContent = navigator.onLine ? 'Phone workspace · local' : 'Phone workspace · offline';
}

window.addEventListener('online', async ()=>{setState();await pushPending();});
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
    if(data){data.suppliers=data.suppliers||[];data.purchase_orders=data.purchase_orders||[];}

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
        data.suppliers=[];data.purchase_orders=[];
        data.local_dirty = false;
        await save();
        document.getElementById('state').textContent = 'Phone workspace ready';
    }

    await requestPersistentStorage();
    setState();
    await refreshQueueState();
    if (data.sync?.token && navigator.onLine) { try { const r=await syncFetch('/api/sync/bootstrap'); if(r?.ok && !data.local_dirty){ applyBootstrap(await r.json()); await save(); } await pushPending(); } catch(e) {} }
    render();
}

if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(()=>{});
boot();
</script>
</body>
</html>