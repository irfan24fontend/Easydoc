{{--
  print.blade.php te toolbar er jaygay boshan:
     @include('partials.prescription-share', ['prescription' => $prescription])
  Ar prescription er main paper div e id boshan:  id="prescription-paper"
--}}
@php
    $isPublic = $isPublic ?? false;
    $shareUrl = $isPublic ? url()->current() : $prescription->shareUrl();
    $fileName = 'prescription-' . ($prescription->id ?? 'rx');
@endphp

<div id="rx-sharebar" class="rx-sharebar">
  @unless($isPublic)
    <div class="rx-linkbox">
      <input type="text" id="rx-link" value="{{ $shareUrl }}" readonly onclick="this.select()">
      <button type="button" onclick="rxCopy()" id="rx-copy-btn">Copy link</button>
    </div>
  @endunless

  <div class="rx-actions">
    <button type="button" class="rx-b rx-jpg" onclick="rxDownload('jpg')">&#11015; JPG</button>
    <button type="button" class="rx-b rx-pdf" onclick="rxDownload('pdf')">&#11015; PDF</button>
    <button type="button" class="rx-b rx-print" onclick="window.print()">&#128424; Print</button>
    @unless($isPublic)
      <a class="rx-b rx-wa" target="_blank" rel="noopener"
         href="https://wa.me/?text={{ urlencode('Apnar prescription: ' . $shareUrl) }}">WhatsApp</a>
      <a class="rx-b rx-sms" href="sms:?&body={{ urlencode('Apnar prescription: ' . $shareUrl) }}">SMS</a>
      <button type="button" class="rx-b rx-native" id="rx-native" style="display:none" onclick="rxNative()">Share</button>
      <form method="POST" action="{{ route('prescription.share.revoke', $prescription) }}"
            onsubmit="return confirm('Link bondho korle ager link ar kaj korbe na. Nishchit?')" style="display:inline">
        @csrf
        <button type="submit" class="rx-b rx-revoke">Link bondho</button>
      </form>
    @endunless
  </div>
</div>

<style>
.rx-sharebar{max-width:820px;margin:12px auto;padding:12px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;font-family:system-ui,sans-serif}
.rx-linkbox{display:flex;gap:8px;margin-bottom:10px}
.rx-linkbox input{flex:1;min-width:0;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px}
.rx-linkbox button,.rx-b{padding:10px 14px;border:0;border-radius:8px;background:#0f766e;color:#fff;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;line-height:1.2}
.rx-actions{display:flex;flex-wrap:wrap;gap:8px}
.rx-jpg{background:#2563eb}.rx-pdf{background:#dc2626}.rx-print{background:#475569}.rx-wa{background:#16a34a}.rx-sms{background:#7c3aed}.rx-native{background:#0891b2}.rx-revoke{background:#fff;color:#b91c1c;border:1px solid #fecaca}
@media(max-width:520px){.rx-actions .rx-b{flex:1 1 30%;text-align:center}}
@media print{.rx-sharebar{display:none!important}}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
(function(){
  var FILE = @json($fileName), URL_ = @json($shareUrl);
  if (navigator.share) { var n=document.getElementById('rx-native'); if(n) n.style.display='inline-block'; }

  window.rxCopy = function(){
    var i=document.getElementById('rx-link'), b=document.getElementById('rx-copy-btn');
    (navigator.clipboard ? navigator.clipboard.writeText(i.value) : Promise.reject())
      .catch(function(){ i.select(); document.execCommand('copy'); })
      .then(function(){ b.textContent='Copied \u2713'; setTimeout(function(){b.textContent='Copy link'},1800); });
  };
  window.rxNative = function(){ navigator.share({title:'Prescription', text:'Apnar prescription', url:URL_}).catch(function(){}); };

  function capture(){
    var el=document.getElementById('prescription-paper');
    if(!el){ alert('id="prescription-paper" paper div e nai.'); return Promise.reject(); }
    var bar=document.getElementById('rx-sharebar'); 
    return html2canvas(el,{scale:2,useCORS:true,backgroundColor:'#ffffff',windowWidth:Math.max(el.scrollWidth,794)});
  }

  window.rxDownload = function(type){
    capture().then(function(canvas){
      if(type==='jpg'){
        var a=document.createElement('a'); a.download=FILE+'.jpg';
        a.href=canvas.toDataURL('image/jpeg',0.95); document.body.appendChild(a); a.click(); a.remove(); return;
      }
      var pdf=new window.jspdf.jsPDF('p','mm','a4'), pw=210, ph=297;
      var imgH=canvas.height*pw/canvas.width, img=canvas.toDataURL('image/jpeg',0.95), y=0;
      pdf.addImage(img,'JPEG',0,y,pw,imgH);
      var left=imgH-ph;
      while(left>0){ y-=ph; pdf.addPage(); pdf.addImage(img,'JPEG',0,y,pw,imgH); left-=ph; }
      pdf.save(FILE+'.pdf');
    });
  };
})();
</script>
