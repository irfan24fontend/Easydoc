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
  <div id="edx-status" style="font:12px system-ui,sans-serif;color:#64748b;text-align:center;margin-top:6px;min-height:0"></div>
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
<script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.js"></script>
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
  var PROXY=@json(route('prescription.img'));
  var GIF='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
  function wait(ms){return new Promise(function(r){setTimeout(r,ms)})}

  // Remote image gulo ke proxy diye same-origin korbe (capture er por ager obosthay ferot)
  function proxify(el){
    var saved=[];
    el.querySelectorAll('img').forEach(function(img){
      var src=img.getAttribute('src')||'';
      try{
        var u=new URL(src,location.href);
        if(u.origin!==location.origin && u.protocol==='https:'){
          saved.push([img,src,img.getAttribute('srcset')]);
          img.removeAttribute('srcset');
          img.setAttribute('src',PROXY+'?u='+encodeURIComponent(u.href));
        }
      }catch(e){}
    });
    return function(){saved.forEach(function(x){x[0].setAttribute('src',x[1]); if(x[2]) x[0].setAttribute('srcset',x[2]);})};
  }
  function imgsReady(el){
    return Promise.all(Array.prototype.map.call(el.querySelectorAll('img'),function(i){
      return i.complete?Promise.resolve():new Promise(function(r){i.onload=i.onerror=r});
    }));
  }

  function snap(){
    // Asol page e hat dei na: offscreen clone e render kori (page jump/scroll hobe na)
    var el=target(), sy=window.scrollY, sx=window.scrollX;
    var host=document.createElement('div');
    host.setAttribute('aria-hidden','true');
    host.style.cssText='position:fixed;left:-10000px;top:0;width:794px;pointer-events:none;overflow:visible;';
    var clone=el.cloneNode(true);
    clone.removeAttribute('id');
    clone.style.cssText+=';width:794px;max-width:794px;min-height:1123px;margin:0;';
    host.appendChild(clone); document.body.appendChild(host);
    proxify(clone);
    var done=function(){ if(host.parentNode) host.parentNode.removeChild(host); window.scrollTo(sx,sy); };
    var base=function(ratio,skipFonts){return {pixelRatio:ratio,backgroundColor:'#ffffff',cacheBust:false,imagePlaceholder:GIF,skipFonts:!!skipFonts,width:794,height:Math.max(clone.scrollHeight,1123),style:{margin:'0'}}};
    var attempts=[[AW/794,false],[2,false],[1.5,true],[1,true]];
    var lastErr;
    var fonts=(document.fonts&&document.fonts.ready)?document.fonts.ready:Promise.resolve();

    function tryAt(i){
      if(i>=attempts.length) return Promise.reject(lastErr||new Error('render failed'));
      return htmlToImage.toCanvas(clone,base(attempts[i][0],attempts[i][1])).catch(function(e){lastErr=e;return wait(200).then(function(){return tryAt(i+1)})});
    }
    return fonts.then(function(){return imgsReady(clone)}).then(function(){return wait(250)})
      .then(function(){return htmlToImage.toCanvas(clone,base(1,true)).catch(function(){})})  // warm-up
      .then(function(){return tryAt(0)})
      .then(function(c){done();return c},function(e){done();throw e});
  }

  function a4(c){ // ek page A4 canvas (canvas er nijer resolution e)
    var out=document.createElement('canvas'); out.width=c.width; out.height=Math.round(c.width*297/210);
    var g=out.getContext('2d'); g.fillStyle='#fff'; g.fillRect(0,0,out.width,out.height);
    var r=Math.min(out.width/c.width, out.height/c.height), w=c.width*r, h=c.height*r;
    g.drawImage(c,(out.width-w)/2,0,w,h); return out;
  }
  function msg(e){ return (e&&e.message)||(e&&e.type)||String(e); }

  function status(t,err){var e=document.getElementById('edx-status'); if(e){e.textContent=t||''; e.style.color=err?'#b91c1c':'#64748b';}}
  window.addEventListener('error',function(ev){ if(document.getElementById('edx-status').textContent.indexOf('...')>-1) status('Error: '+(ev.message||'script error'),true); });

  document.querySelectorAll('[data-dl]').forEach(function(b){b.addEventListener('click',function(ev){
    ev.preventDefault(); ev.stopPropagation();
    var type=b.getAttribute('data-dl'), old=b.textContent;
    b.textContent='...'; b.disabled=true; status('Preparing...');
    var finish=function(){b.textContent=old;b.disabled=false;};
    Promise.resolve().then(function(){
      if(typeof htmlToImage==='undefined') throw new Error('html-to-image load hoyni (internet/CDN check korun)');
      if(type==='pdf' && !(window.jspdf&&window.jspdf.jsPDF)) throw new Error('jsPDF load hoyni (internet/CDN check korun)');
      status('Rendering image...');
      return snap();
    }).then(function(c){
      status('Saving...');
      if(type==='jpg'){
        return new Promise(function(res,rej){
          a4(c).toBlob(function(blob){
            if(!blob) return rej(new Error('Image toiri hoyni (phone memory kom hote pare)'));
            var u=URL.createObjectURL(blob), a=document.createElement('a');
            a.download=FILE+'-A4.jpg'; a.href=u; a.style.display='none';
            document.body.appendChild(a); a.click();
            setTimeout(function(){a.remove();URL.revokeObjectURL(u)},4000); res();
          },'image/jpeg',.92);
        });
      }
      var pdf=new window.jspdf.jsPDF('p','mm','a4'),pw=210,ph=297,h=c.height*pw/c.width,img=c.toDataURL('image/jpeg',.92),y=0;
      pdf.addImage(img,'JPEG',0,y,pw,h);var left=h-ph;
      while(left>0.5){y-=ph;pdf.addPage();pdf.addImage(img,'JPEG',0,y,pw,h);left-=ph}
      pdf.save(FILE+'.pdf');
    }).then(function(){status('Done \u2713'); setTimeout(function(){status('')},2500); finish();},
            function(e){status('Error: '+msg(e),true); finish();});
  });});
})();
</script>