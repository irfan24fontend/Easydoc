{{-- Stethoscope preloader. Layout e <body> er thik porei: @include('partials.preloader') --}}
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
