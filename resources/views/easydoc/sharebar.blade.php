<div id="edx-bar">
  @unless($isPublic)
    <div class="edx-link"><input id="edx-url" type="text" value="{{ $shareUrl }}" readonly onclick="this.select()"><button type="button" id="edx-copy">Copy link</button></div>
  @endunless
  <div class="edx-row">
    <button type="button" class="edx-b" style="background:#2563eb" data-dl="jpg">&#11015; JPG</button>
    <button type="button" class="edx-b" style="background:#dc2626" data-dl="pdf">&#11015; PDF</button>
    <button type="button" class="edx-b" style="background:#475569" onclick="window.print()">Print</button>
    @unless($isPublic)
      <a class="edx-b" style="background:#16a34a" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode('Apnar prescription: '.$shareUrl) }}">WhatsApp</a>
      <a class="edx-b" style="background:#7c3aed" href="sms:?&body={{ urlencode('Apnar prescription: '.$shareUrl) }}">SMS</a>
      <button type="button" class="edx-b" id="edx-native" style="background:#0891b2;display:none">Share</button>
      <form method="POST" action="{{ $revokeUrl }}" onsubmit="return confirm('Link bondho korle ager link kaj korbe na. Nishchit?')" style="display:inline">
        @csrf
        <button type="submit" class="edx-b" style="background:#fff;color:#b91c1c;border:1px solid #fecaca">Link bondho</button>
      </form>
    @endunless
  </div>
</div>
<style>
#edx-bar{position:fixed;left:0;right:0;bottom:0;z-index:9000;background:#fff;border-top:1px solid #e2e8f0;box-shadow:0 -4px 18px rgba(0,0,0,.12);padding:10px 12px calc(10px + env(safe-area-inset-bottom,0px));font-family:system-ui,sans-serif}
.edx-link{display:flex;gap:8px;margin-bottom:8px}
.edx-link input{flex:1;min-width:0;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font-size:16px}
.edx-link button,.edx-b{padding:10px 14px;border:0;border-radius:8px;background:#0f766e;color:#fff;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;line-height:1.2}
.edx-row{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
@media(max-width:520px){.edx-row .edx-b{flex:1 1 28%;text-align:center}}
@media print{#edx-bar{display:none!important}}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
(function(){
  var FILE='prescription-{{ $rxId }}', URL_=@json($shareUrl);
  var nat=document.getElementById('edx-native');
  if(navigator.share&&nat){nat.style.display='inline-block';nat.onclick=function(){navigator.share({title:'Prescription',text:'Apnar prescription',url:URL_}).catch(function(){})}}
  var cp=document.getElementById('edx-copy');
  if(cp)cp.onclick=function(){var i=document.getElementById('edx-url');
    (navigator.clipboard?navigator.clipboard.writeText(i.value):Promise.reject()).catch(function(){i.select();document.execCommand('copy')})
    .then(function(){cp.textContent='Copied \u2713';setTimeout(function(){cp.textContent='Copy link'},1800)})};

  function target(){
    var q=document.querySelector('#prescription-paper,.prescription-paper,#print-area,.print-area,.rx-paper,.rx-page,.paper,.sheet,.a4,.page');
    if(q) return q;
    var tb=document.querySelector('.rx-toolbar'), n=tb?tb.nextElementSibling:null;
    while(n && (['SCRIPT','STYLE'].indexOf(n.tagName)>-1 || n.offsetHeight<150)) n=n.nextElementSibling;
    return n || document.querySelector('main,.container') || document.body;
  }
  var AW=2480, AH=3508; // A4 @ 300 DPI
  var AW=2480, AH=3508; // A4 @300dpi
  function snap(){
    var el=target(), bar=document.getElementById('edx-bar'); bar.style.display='none';
    var w=el.offsetWidth||794, sc=AW/w;
    return html2canvas(el,{scale:sc,useCORS:true,backgroundColor:'#ffffff'})
      .then(function(c){bar.style.display='';return c},function(e){bar.style.display='';throw e});
  }
  function a4(c){ // ek page A4 canvas, content fit kore (white background)
    var out=document.createElement('canvas'); out.width=AW; out.height=AH;
    var g=out.getContext('2d'); g.fillStyle='#fff'; g.fillRect(0,0,AW,AH);
    var r=Math.min(AW/c.width, AH/c.height), w=c.width*r, h=c.height*r;
    g.drawImage(c,(AW-w)/2,0,w,h); return out;
  }
  document.querySelectorAll('[data-dl]').forEach(function(b){b.onclick=function(){
    var type=b.getAttribute('data-dl'), old=b.textContent; b.textContent='...';
    snap().then(function(c){
      if(type==='jpg'){var a=document.createElement('a');a.download=FILE+'-A4.jpg';a.href=a4(c).toDataURL('image/jpeg',.95);document.body.appendChild(a);a.click();a.remove();return}
      var pdf=new window.jspdf.jsPDF('p','mm','a4'),pw=210,ph=297,h=c.height*pw/c.width,img=c.toDataURL('image/jpeg',.95),y=0;
      pdf.addImage(img,'JPEG',0,y,pw,h);var left=h-ph;
      while(left>0.5){y-=ph;pdf.addPage();pdf.addImage(img,'JPEG',0,y,pw,h);left-=ph}
      pdf.save(FILE+'.pdf');
    }).catch(function(){alert('Download hoyni, abar chesta korun.')}).then(function(){b.textContent=old});
  }});
})();
</script>
