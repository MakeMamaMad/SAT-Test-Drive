// Images are intentionally not substituted for an unverified axle configuration.
const configurations={
 'SAT-500':{
  3:{curb:'от 7 900 кг',volume:'43–49 м³',gross:'до 45 000 кг',image:'https://www.satpricep.by/upload/dev2fun.imagecompress/webp/resize_cache/iblock/30a/940_640_0/o41xubrun6bkg1h46t0es04unb1t82f6.webp',caption:'SAT-500 · 3 оси'},
  4:{curb:'от 7 920 кг',volume:'49–55 м³',gross:'до 52 500 кг',image:'https://frankfurt.apollo.olxcdn.com/v1/files/pwm90kp4ujse2-KZ/image%3Bs%3D4160x3120',caption:'SAT-500 · 4 оси'}
 },
 'SAT-545':{
  3:{curb:'7 980 кг',volume:'45 м³',gross:'50 300 кг',image:'assets/images/sat545-3.webp',caption:'SAT-545 · 3 оси'},
  4:{curb:'8 900 кг ±3%',volume:'45 м³',gross:'не указана',image:'https://satpricep.ru/upload/dev2fun.imagecompress/webp/resize_cache/iblock/8a6/940_640_0/tsmisk24qf23z0ww7xgjf1qdowt82i4n.webp',caption:'SAT-545 · 4 оси'}
 }
};
function renderCard(card,axles){
 const model=card.dataset.cardModel,info=configurations[model][axles];
 for(const [key,value] of Object.entries({axles,curb:info.curb,volume:info.volume,gross:info.gross})) card.querySelector('[data-spec="'+key+'"]').textContent=value;
 card.querySelectorAll('.axle-option').forEach(b=>{const active=b.dataset.axles===String(axles);b.classList.toggle('is-active',active);b.setAttribute('aria-pressed',String(active));});
 const img=card.querySelector('.model-art img'),placeholder=card.querySelector('.photo-placeholder');
 img.onerror=()=>{img.hidden=true;img.removeAttribute('src');placeholder.hidden=false;};
 if(info.image){img.src=info.image;img.alt='Фотография '+model+' · '+axles+' оси';img.hidden=false;placeholder.hidden=true;}
 else{img.removeAttribute('src');img.hidden=true;placeholder.hidden=false;}
 card.querySelector('.photo-caption').textContent=info.caption;
}
document.querySelectorAll('[data-card-model]').forEach(card=>{
 card.querySelectorAll('.axle-option').forEach(b=>b.addEventListener('click',()=>renderCard(card,b.dataset.axles)));
 renderCard(card,card.querySelector('.axle-option.is-active')?.dataset.axles||'3');
});
document.querySelectorAll('.select-model').forEach(btn=>btn.addEventListener('click',()=>{
 const card=btn.closest('[data-card-model]');
 document.querySelector('#model').value=card.dataset.cardModel;
 document.querySelector('#axles').value=card.querySelector('.axle-option.is-active').dataset.axles;
 document.querySelector('#form').scrollIntoView({behavior:'smooth'});
}));
document.querySelector('#application').addEventListener('submit',async event=>{
 event.preventDefault();
 const form=event.currentTarget,notice=document.querySelector('#form-message');
 if(location.hostname.endsWith('github.io')){notice.textContent='Демо-версия: настоящая регистрация заработает на REG.RU после подключения PHP и MySQL.';return;}
 const button=form.querySelector('button[type=submit]');
 const payload={model:form.elements.model.value,axles:Number(form.elements.axles.value),name:form.elements.name.value.trim(),phone:form.elements.phone.value.trim(),email:form.elements.email.value.trim(),consent:form.querySelector('.consent input').checked};
 button.disabled=true;notice.textContent='Сохраняем заявку…';
 try{const response=await fetch('api/register.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
 const data=await response.json();if(!response.ok)throw new Error(data.error||'Ошибка регистрации');location.assign('queue.html?token='+encodeURIComponent(data.token));}
 catch(error){notice.textContent=error.message||'Регистрация временно недоступна.';button.disabled=false;}
});
