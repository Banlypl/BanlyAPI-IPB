<?php

namespace IPS\banlyapi\widgets;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _banCheck extends \IPS\Widget
{
	public $key = 'banCheck';

	public $app = 'banlyapi';

	public $plugin = '';

	public function init()
	{
		try
		{
			parent::init();
		}
		catch ( \Throwable $e ) {}
	}

	public function render()
	{
		try
		{
			return $this->html();
		}
		catch ( \Throwable $e )
		{
			return '<div class="banlyWidget">Banly: błąd renderowania bloku.</div>';
		}
	}

	protected function html()
	{
		$logoDark = 'https://banly.pl/banly-18.png';
		$logoLight = 'https://banly.pl/banly-light-18.png';

		$endpoint = (string) \IPS\Http\Url::internal( 'app=banlyapi&module=proxy&controller=check', 'front' );
		$csrf = isset( \IPS\Session::i()->csrfKey ) ? (string) \IPS\Session::i()->csrfKey : '';
		$uid = isset( $this->uniqueKey ) ? (string) $this->uniqueKey : uniqid( 'b', FALSE );
		$id = 'banly-widget-' . preg_replace( '/[^a-zA-Z0-9_-]/', '', $uid );

		$logoDarkEsc = htmlspecialchars( $logoDark, ENT_QUOTES, 'UTF-8' );
		$logoLightEsc = htmlspecialchars( $logoLight, ENT_QUOTES, 'UTF-8' );
		$endpointEsc = htmlspecialchars( $endpoint, ENT_QUOTES, 'UTF-8' );
		$csrfEsc = htmlspecialchars( $csrf, ENT_QUOTES, 'UTF-8' );
		$idEsc = htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' );
		$idJs = json_encode( $id );

		return <<<HTML
<div class="banlyWidget" id="{$idEsc}" data-endpoint="{$endpointEsc}" data-csrf="{$csrfEsc}">
<style>
.banlyWidget{font-size:13px;line-height:1.35;color:#f4f4f5}
.banlyWidget__brand{display:flex;justify-content:center;margin:0 0 12px}
.banlyWidget__logo{max-width:118px;height:auto;display:block}
.banlyWidget__logo--onLight{display:none}
.banlyWidget--light .banlyWidget__logo--onDark{display:none}
.banlyWidget--light .banlyWidget__logo--onLight{display:block}
.banlyWidget__row{display:flex;gap:6px;align-items:stretch}
.banlyWidget__input{flex:1;min-width:0;box-sizing:border-box;height:36px;margin:0;padding:0 10px;border:0;border-radius:10px;background:rgba(0,0,0,.35) !important;box-shadow:inset 0 0 0 1px rgba(255,255,255,.1);color:#f4f4f5 !important;font:inherit}
.banlyWidget__input::placeholder{color:rgba(244,244,245,.42)}
.banlyWidget__input:focus{outline:none;box-shadow:inset 0 0 0 1px #ea5942,0 0 0 3px rgba(234,89,66,.22)}
.banlyWidget__btn{flex:0 0 auto;height:36px;margin:0;padding:0 12px;border:0;border-radius:10px;cursor:pointer;font:inherit;font-weight:650;background:#d94a35 !important;color:#fff !important}
.banlyWidget__btn:hover{background:#f06b58}
.banlyWidget__btn:active{transform:scale(.98)}
.banlyWidget__btn:disabled{opacity:.45;cursor:default;transform:none}
.banlyWidget__status{margin-top:10px}
.banlyWidget__status.is-error{color:#fca5a5;font-size:12px}
.banlyWidget__pending{color:#9a9aa3;font-size:12px}
.banlyWidget__result{position:relative;overflow:hidden;padding:10px 12px 10px 14px;border-radius:12px;background:rgba(0,0,0,.28);box-shadow:inset 0 0 0 1px rgba(255,255,255,.08)}
.banlyWidget__result::before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:#ea5942}
.banlyWidget__result.is-clear::before{background:#22c55e}
.banlyWidget__player{display:flex;align-items:center;gap:10px;min-width:0}
.banlyWidget img.banlyWidget__avatar,.banlyWidget__avatar--empty{width:48px;height:48px;max-width:48px;border-radius:10px;object-fit:cover;flex:0 0 auto;background:rgba(0,0,0,.35);box-shadow:inset 0 0 0 1px rgba(255,255,255,.1)}
.banlyWidget__avatar--empty{display:flex;align-items:center;justify-content:center;color:#9a9aa3;font-size:13px;font-weight:650}
.banlyWidget__meta{min-width:0}
.banlyWidget__nick{color:#f4f4f5;font-size:14px;font-weight:600;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.banlyWidget__count{margin-top:3px;color:#fca5a5;font-size:12px;font-weight:500;line-height:1.3}
.banlyWidget__result.is-clear .banlyWidget__count{color:#86efac}
.banlyWidget__num{font-weight:700;font-variant-numeric:tabular-nums}
.banlyWidget a.banlyWidget__link{display:flex;align-items:center;justify-content:flex-end;gap:4px;margin-top:10px;padding-top:8px;border-top:1px solid rgba(255,255,255,.08);background:none;box-shadow:none;color:#f4a090;font-size:12px;font-weight:600;line-height:1.2;text-decoration:none}
.banlyWidget a.banlyWidget__link:hover{color:#fff}
.banlyWidget__go{color:inherit}
.banlyWidget--light{color:#1c1c1f}
.banlyWidget--light .banlyWidget__input{background:#fff !important;color:#1c1c1f !important;box-shadow:inset 0 0 0 1px rgba(0,0,0,.18) !important}
.banlyWidget--light .banlyWidget__input::placeholder{color:rgba(28,28,31,.45)}
.banlyWidget--light .banlyWidget__nick{color:#1c1c1f}
.banlyWidget--light .banlyWidget__count{color:#c2412d}
.banlyWidget--light .banlyWidget__result.is-clear .banlyWidget__count{color:#15803d}
.banlyWidget--light .banlyWidget__result{background:rgba(0,0,0,.04);box-shadow:inset 0 0 0 1px rgba(0,0,0,.08)}
.banlyWidget--light .banlyWidget__status.is-error{color:#c2412d}
.banlyWidget--light .banlyWidget__pending{color:#5c5c66}
.banlyWidget--light .banlyWidget__avatar--empty{color:#5c5c66;background:rgba(0,0,0,.06)}
.banlyWidget--light a.banlyWidget__link{color:#d94a35;border-top-color:rgba(0,0,0,.1)}
.banlyWidget--light a.banlyWidget__link:hover{color:#1c1c1f}
</style>
<div class="banlyWidget__brand">
<a href="https://banly.pl" target="_blank" rel="noopener noreferrer" title="Banly.pl">
<img class="banlyWidget__logo banlyWidget__logo--onDark" src="{$logoDarkEsc}" alt="Banly.pl" width="140" height="40">
<img class="banlyWidget__logo banlyWidget__logo--onLight" src="{$logoLightEsc}" alt="" width="140" height="40">
</a>
</div>
<form class="banlyWidget__form" action="#" method="post">
<div class="banlyWidget__row">
<input class="banlyWidget__input" type="text" name="q" autocomplete="off" spellcheck="false" placeholder="SteamID / SteamID64 / link">
<button class="banlyWidget__btn" type="submit">Sprawdź</button>
</div>
</form>
<div class="banlyWidget__status" aria-live="polite"></div>
<script>
(function(){
var root=document.getElementById({$idJs});
if(!root||root.getAttribute('data-bound')==='1')return;
root.setAttribute('data-bound','1');
(function paintTheme(){var node=root,bg='';while(node){var c=window.getComputedStyle(node).backgroundColor;if(c&&c!=='transparent'&&c!=='rgba(0, 0, 0, 0)'){bg=c;break;}node=node.parentElement;}var m=/rgba?\\((\\d+),\\s*(\\d+),\\s*(\\d+)/.exec(bg||'');var light=false;if(m){var y=(0.2126*(+m[1])+0.7152*(+m[2])+0.0722*(+m[3]))/255;light=y>0.62;}root.classList.toggle('banlyWidget--light',light);})();
var form=root.querySelector('.banlyWidget__form');
var input=root.querySelector('.banlyWidget__input');
var button=root.querySelector('.banlyWidget__btn');
var status=root.querySelector('.banlyWidget__status');
var endpoint=root.getAttribute('data-endpoint');
var csrf=root.getAttribute('data-csrf');
function setStatus(html,kind){status.className='banlyWidget__status'+(kind?' is-'+kind:'');status.innerHTML=html||'';}
function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function initials(name){var parts=String(name||'').trim().split(/\\s+/).filter(Boolean).slice(0,2);var s=parts.map(function(p){return p.charAt(0);}).join('');return (s||'?').toUpperCase().slice(0,2);}
function countHtml(n){n=Number(n)||0;if(n<=0)return 'Brak aktywnych banów';var word=n===1?'sieć z aktywnym banem':'sieci z aktywnym banem';return '<span class="banlyWidget__num">'+n+'</span> '+word;}
function resultHtml(data){var n=Number(data.activeNetworks)||0;var name=String(data.personaName||'').trim()||String(data.q||'').trim();var avatar=String(data.avatarUrl||'');if(!/^https:\\/\\//i.test(avatar))avatar='';var tone=n>0?'is-bad':'is-clear';var face=avatar?'<img class="banlyWidget__avatar" src="'+esc(avatar)+'" alt="" width="48" height="48">':'<span class="banlyWidget__avatar banlyWidget__avatar--empty" aria-hidden="true">'+esc(initials(name))+'</span>';var link='';if(data.detailsUrl){var safeUrl=String(data.detailsUrl).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');link='<a class="banlyWidget__link" href="'+safeUrl+'" target="_blank" rel="noopener noreferrer">Szczegóły na Banly.pl <span class="banlyWidget__go" aria-hidden="true">\\u2192</span></a>';}return '<div class="banlyWidget__result '+tone+'"><div class="banlyWidget__player">'+face+'<div class="banlyWidget__meta"><div class="banlyWidget__nick">'+esc(name)+'</div><div class="banlyWidget__count">'+countHtml(n)+'</div></div></div>'+link+'</div>';}
form.addEventListener('submit',function(ev){
ev.preventDefault();
var q=(input.value||'').trim();
if(!q){setStatus('Podaj SteamID.','error');return;}
button.disabled=true;setStatus('<span class="banlyWidget__pending">Sprawdzanie…</span>','');
var body=new URLSearchParams();body.set('q',q);body.set('csrfKey',csrf);
fetch(endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:body.toString()})
.then(function(res){return res.json().then(function(data){return {data:data||{}};}).catch(function(){return {data:{}};});})
.then(function(pack){
button.disabled=false;var data=pack.data;
if(!data.ok){setStatus((data.message||data.error||'Nie udało się sprawdzić. Spróbuj później.'),'error');return;}
setStatus(resultHtml(data),'ok');
}).catch(function(){button.disabled=false;setStatus('Nie udało się sprawdzić. Spróbuj później.','error');});
});
})();
</script>
</div>
HTML;
	}
}
