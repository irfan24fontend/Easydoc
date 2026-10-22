/* EasyDoc - doctor mobile drawer. </body> er age: <script src="{{ asset('js/doctor-mobile.js') }}"></script> */
(function(){
  var SIDEBAR_SEL = window.EDOC_SIDEBAR || '#sidebar, .sidebar, aside, nav.sidebar, .main-sidebar';
  var MAIN_SEL    = window.EDOC_MAIN    || '#main, main, .main-content, .content-wrapper, .page-content';
  var sidebar = document.querySelector(SIDEBAR_SEL);
  if(!sidebar) return;
  var main = document.querySelector(MAIN_SEL);
  sidebar.classList.add('ed-m-sidebar');
  if(main) main.classList.add('ed-m-main');

  var overlay=document.createElement('div'); overlay.className='ed-m-overlay';
  var bar=document.createElement('div'); bar.className='ed-m-topbar';
  bar.innerHTML='<button type="button" aria-label="Menu">&#9776;</button><b>EasyDoc</b>';
  // desktop e topbar lukano
  var st=document.createElement('style'); st.textContent='@media(min-width:992px){.ed-m-topbar,.ed-m-overlay{display:none!important}}';
  document.head.appendChild(st);

  document.body.insertBefore(bar, document.body.firstChild);
  document.body.appendChild(overlay);

  function toggle(open){ document.body.classList.toggle('ed-menu-open', open); }
  bar.querySelector('button').addEventListener('click', function(){ toggle(!document.body.classList.contains('ed-menu-open')); });
  overlay.addEventListener('click', function(){ toggle(false); });
  sidebar.addEventListener('click', function(e){ if(e.target.closest('a[href]:not([href="#"])')) toggle(false); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') toggle(false); });
})();
