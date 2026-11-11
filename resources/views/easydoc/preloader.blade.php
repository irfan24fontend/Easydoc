{{-- Stethoscope preloader injected into HTML responses by EasyDocInject middleware. --}}
<style>
#ed-preloader{position:fixed;inset:0;z-index:99999;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(135deg,#ecfeff,#f0fdf4);transition:opacity .45s ease,visibility .45s ease}
#ed-preloader.ed-hide{opacity:0;visibility:hidden}
#ed-preloader svg{width:150px;height:150px;overflow:visible}
.ed-steth{transform-origin:50% 8%;animation:edSwing 2.2s ease-in-out infinite}
.ed-ecg{stroke-dasharray:120;stroke-dashoffset:120;animation:edEcg 1.6s linear infinite}
.ed-chest{transform-origin:center;transform-box:fill-box;animation:edBeat 1.1s ease-in-out infinite}
.ed-brand{margin-top:14px;font:700 20px/1.2 system-ui,sans-serif;color:#0f766e;letter-spacing:.5px}
.ed-sub{margin-top:4px;font:500 13px system-ui,sans-serif;color:#64748b}
@keyframes edSwing{0%,100%{transform:rotate(-5deg)}50%{transform:rotate(5deg)}}
@keyframes edEcg{to{stroke-dashoffset:-120}}
@keyframes edBeat{0%,100%{transform:scale(1)}15%{transform:scale(1.18)}30%{transform:scale(1)}45%{transform:scale(1.12)}}
@media (prefers-reduced-motion:reduce){.ed-steth,.ed-chest,.ed-ecg{animation:none}}

#ed-rx-preloader{position:fixed;inset:0;z-index:100000;display:grid;place-items:center;padding:20px;background:linear-gradient(145deg,#ecfeff 0%,#f0fdf4 52%,#fff7ed 100%);opacity:0;visibility:hidden;pointer-events:none;transition:opacity .25s ease,visibility .25s ease}
#ed-rx-preloader.ed-rx-active{opacity:1;visibility:visible;pointer-events:auto}
.ed-rx-card{width:min(470px,100%);padding:24px 22px 20px;text-align:center;background:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.95);border-radius:26px;box-shadow:0 24px 70px rgba(15,118,110,.17);backdrop-filter:blur(12px)}
.ed-rx-scene{display:block;width:min(410px,88vw);height:auto;margin:0 auto}
.ed-rx-ground{stroke:#cbd5e1;stroke-width:3;stroke-linecap:round}
.ed-rx-trail{stroke:#94a3b8;stroke-width:3;stroke-linecap:round;stroke-dasharray:10 10;animation:ed-rx-trail 1.1s linear infinite}
.ed-rx-doctor-bob{animation:ed-rx-bob .34s ease-in-out infinite}
.ed-rx-patient-bob{animation:ed-rx-bob .31s ease-in-out -.13s infinite}
.ed-rx-doctor-leg-a,.ed-rx-doctor-leg-b,.ed-rx-patient-leg-a,.ed-rx-patient-leg-b,.ed-rx-doctor-arm,.ed-rx-patient-arm-a,.ed-rx-patient-arm-b{transform-box:view-box}
.ed-rx-doctor-leg-a{transform-origin:108px 122px;animation:ed-rx-leg .32s ease-in-out infinite}
.ed-rx-doctor-leg-b{transform-origin:119px 122px;animation:ed-rx-leg .32s ease-in-out -.16s infinite}
.ed-rx-doctor-arm{transform-origin:128px 87px;animation:ed-rx-arm .34s ease-in-out infinite}
.ed-rx-patient-leg-a{transform-origin:286px 130px;animation:ed-rx-leg .3s ease-in-out -.14s infinite}
.ed-rx-patient-leg-b{transform-origin:302px 130px;animation:ed-rx-leg .3s ease-in-out infinite}
.ed-rx-patient-arm-a{transform-origin:285px 94px;animation:ed-rx-arm .3s ease-in-out -.14s infinite}
.ed-rx-patient-arm-b{transform-origin:307px 94px;animation:ed-rx-arm .3s ease-in-out infinite}
.ed-rx-title{margin:4px 0 0;font:800 21px/1.25 system-ui,sans-serif;color:#0f766e}
.ed-rx-copy{margin:7px 0 0;font:500 13px/1.5 system-ui,sans-serif;color:#64748b}
@keyframes ed-rx-bob{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
@keyframes ed-rx-leg{0%,100%{transform:rotate(24deg)}50%{transform:rotate(-28deg)}}
@keyframes ed-rx-arm{0%,100%{transform:rotate(-13deg)}50%{transform:rotate(18deg)}}
@keyframes ed-rx-trail{to{stroke-dashoffset:-40}}
@media (prefers-reduced-motion:reduce){.ed-rx-trail,.ed-rx-doctor-bob,.ed-rx-patient-bob,.ed-rx-doctor-leg-a,.ed-rx-doctor-leg-b,.ed-rx-patient-leg-a,.ed-rx-patient-leg-b,.ed-rx-doctor-arm,.ed-rx-patient-arm-a,.ed-rx-patient-arm-b{animation:none!important}}
</style>
<div id="ed-preloader" role="status" aria-label="Loading">
  <svg viewBox="0 0 120 130" fill="none" stroke="#0f766e" stroke-width="5" stroke-linecap="round" stroke-linejoin="round">
    <g class="ed-steth">
      <circle cx="30" cy="10" r="5" fill="#0f766e"/>
      <circle cx="90" cy="10" r="5" fill="#0f766e"/>
      <path d="M30 14 V42 a30 30 0 0 0 60 0 V14"/>
      <path d="M60 72 V86 a18 18 0 0 0 36 0 V78"/>
      <g class="ed-chest">
        <circle cx="96" cy="70" r="9" fill="#14b8a6" stroke="#0f766e"/>
        <circle cx="96" cy="70" r="3" fill="#fff" stroke="none"/>
      </g>
    </g>
    <path class="ed-ecg" d="M5 118 H35 L43 104 L52 126 L61 108 L68 118 H115" stroke="#ef4444" stroke-width="3.5"/>
  </svg>
  <div class="ed-brand">EasyDoc</div>
  <div class="ed-sub">Loading, please wait...</div>
</div>
<script>
(function(){
  var start=Date.now(), el=document.getElementById('ed-preloader');
  function hide(){var wait=Math.max(0,700-(Date.now()-start));setTimeout(function(){el&&el.classList.add('ed-hide');setTimeout(function(){el&&el.remove()},500)},wait)}
  if(document.readyState==='complete')hide();else window.addEventListener('load',hide);
  setTimeout(hide,6000); // safety
})();
</script>
<div id="ed-rx-preloader" aria-hidden="true">
  <div class="ed-rx-card" role="status" aria-live="polite">
    <svg class="ed-rx-scene" viewBox="0 0 410 190" role="img" aria-label="A doctor runs with a syringe while a nervous patient runs away">
      <path class="ed-rx-trail" d="M24 57h32M43 75h24M352 47h27M365 66h20" fill="none"/>
      <path class="ed-rx-ground" d="M35 160h340" fill="none"/>

      <g class="ed-rx-doctor-bob">
        <g class="ed-rx-doctor-leg-a" fill="none" stroke="#1d4ed8" stroke-width="11" stroke-linecap="round" stroke-linejoin="round">
          <path d="M107 119l-9 25-20 9"/><path d="M78 153l-8 2" stroke="#0f172a" stroke-width="7"/>
        </g>
        <g class="ed-rx-doctor-leg-b" fill="none" stroke="#2563eb" stroke-width="11" stroke-linecap="round" stroke-linejoin="round">
          <path d="M119 119l16 18 20-2"/><path d="M154 135l8-2" stroke="#0f172a" stroke-width="7"/>
        </g>
        <path d="M96 81q16-10 31 0l8 38q-20 13-42 0z" fill="#fff" stroke="#94a3b8" stroke-width="2.5"/>
        <path d="M109 81l8 12 8-12-2 34h-13z" fill="#0c8c84"/>
        <path d="M108 89q9 6 17 0M116 93v15q0 12 10 11" fill="none" stroke="#0f766e" stroke-width="2.5" stroke-linecap="round"/>
        <g class="ed-rx-doctor-arm" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M127 88l15-19 10-9" stroke="#fff" stroke-width="11"/>
          <path d="M141 70l11-10" stroke="#f4c7a1" stroke-width="8"/>
          <circle cx="153" cy="59" r="4.5" fill="#f4c7a1" stroke="none"/>
          <path d="M153 57l17-17m-5 3 7 7m-14-1 7 7m-3-13 7-7" stroke="#0c8c84" stroke-width="3"/>
          <path d="M171 39l8-8" stroke="#64748b" stroke-width="2"/>
          <path d="M166 46l4 4" stroke="#fff" stroke-width="2"/>
        </g>
        <path d="M97 91l-15 10-10-8" fill="none" stroke="#fff" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M73 93l-5-4" fill="none" stroke="#f4c7a1" stroke-width="7" stroke-linecap="round"/>
        <path d="M97 49q1-25 24-23 18 2 19 21-7-7-15-11-10 10-28 13z" fill="#334155"/>
        <circle cx="116" cy="53" r="22" fill="#f4c7a1"/>
        <path d="M95 49q3-24 25-23 17 1 20 18-13-1-22-10-9 9-23 15z" fill="#334155"/>
        <path d="M108 54h1m14 0h1" stroke="#334155" stroke-width="3.5" stroke-linecap="round"/>
        <path d="M111 65q6 5 12 0" fill="none" stroke="#9a4f3a" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M101 46l8-2m12 0 8 2" stroke="#334155" stroke-width="2.5" stroke-linecap="round"/>
      </g>

      <g class="ed-rx-patient-bob">
        <g class="ed-rx-patient-leg-a" fill="none" stroke="#f97316" stroke-width="11" stroke-linecap="round" stroke-linejoin="round">
          <path d="M287 128l-9 20-17 5"/><path d="M261 153l-8 1" stroke="#0f172a" stroke-width="7"/>
        </g>
        <g class="ed-rx-patient-leg-b" fill="none" stroke="#fb923c" stroke-width="11" stroke-linecap="round" stroke-linejoin="round">
          <path d="M302 128l14 17 18-3"/><path d="M334 142l8-2" stroke="#0f172a" stroke-width="7"/>
        </g>
        <path d="M278 90q16-11 32 0l8 38q-23 12-44 0z" fill="#fbbf24" stroke="#d97706" stroke-width="2.5"/>
        <g class="ed-rx-patient-arm-a" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M282 96l-16-8-8-13" stroke="#fbbf24" stroke-width="10"/>
          <path d="M258 76l-5-7" stroke="#f4c7a1" stroke-width="7"/>
        </g>
        <g class="ed-rx-patient-arm-b" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M308 96l16-8 10-14" stroke="#fbbf24" stroke-width="10"/>
          <path d="M334 75l5-7" stroke="#f4c7a1" stroke-width="7"/>
        </g>
        <circle cx="295" cy="61" r="21" fill="#f4c7a1"/>
        <path d="M275 57q2-24 22-23 15 0 20 18-11-3-18-10-9 9-24 10z" fill="#713f12"/>
        <path d="M286 61l4 2m12-2 4-2" stroke="#334155" stroke-width="3" stroke-linecap="round"/>
        <circle cx="290" cy="67" r="2.5" fill="#334155"/><circle cx="302" cy="67" r="2.5" fill="#334155"/>
        <ellipse cx="296" cy="78" rx="4" ry="5.5" fill="#7f1d1d"/>
        <path d="M317 49q9 0 10 8" fill="none" stroke="#38bdf8" stroke-width="3" stroke-linecap="round"/>
        <path d="M324 57l4 2-4 2" fill="#38bdf8"/>
      </g>
    </svg>
    <div class="ed-rx-title">Prescription time!</div>
    <p class="ed-rx-copy">The doctor is on the way. Patient, please wait!</p>
  </div>
</div>
<script>
(function(){
  var overlay=document.getElementById('ed-rx-preloader');
  if(!overlay)return;
  var pendingKey='easydoc.rx-preloader-target';
  var createPath=new URL(@json(route('doctor.prescriptions.create')),window.location.href).pathname;
  var isCreatePage=@json(request()->routeIs('doctor.prescriptions.create'));
  function show(){overlay.classList.add('ed-rx-active');overlay.setAttribute('aria-hidden','false')}
  function hide(){overlay.classList.remove('ed-rx-active');overlay.setAttribute('aria-hidden','true')}
  var requestedPath='';
  try{requestedPath=window.sessionStorage.getItem(pendingKey)||'';}catch(e){}
  if(requestedPath){try{window.sessionStorage.removeItem(pendingKey);}catch(e){}}
  if(isCreatePage||requestedPath===window.location.pathname){
    var landedAt=Date.now();show();
    var reducedMotion=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finish=function(){window.setTimeout(hide,Math.max(0,(reducedMotion?350:1100)-(Date.now()-landedAt)));};
    if(document.readyState==='complete')finish();else window.addEventListener('load',finish,{once:true});
    window.setTimeout(hide,6000);
  }
  document.addEventListener('click',function(event){
    if(event.defaultPrevented||event.button!==0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey)return;
    var link=event.target.closest&&event.target.closest('a[href]');
    if(!link||link.target&&link.target!=='_self'||link.hasAttribute('download')||link.getAttribute('aria-disabled')==='true')return;
    var destination;
    try{destination=new URL(link.href,window.location.href);}catch(e){return;}
    if(destination.origin!==window.location.origin||destination.pathname!==createPath)return;
    event.preventDefault();
    try{window.sessionStorage.setItem(pendingKey,destination.pathname);}catch(e){}
    show();
    var delay=window.matchMedia('(prefers-reduced-motion: reduce)').matches?150:650;
    window.setTimeout(function(){window.location.assign(destination.href);},delay);
  },true);
})();
</script>
