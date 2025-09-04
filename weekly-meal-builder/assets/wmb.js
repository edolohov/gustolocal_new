(function () {
  "use strict";

  var CFG = window.WMB || {};
  var MENU_URL = CFG.menu_url || "/wp-json/wmb/v1/menu";
  var LS_KEY = "wmb_state_v3";

  function el(s, r){return (r||document).querySelector(s)}
  function els(s, r){return Array.from((r||document).querySelectorAll(s))}
  function money(n){return (Math.round(n*100)/100).toFixed(2) + "€"}
  function escapeHtml(s){return String(s||"").replace(/[&<>"']/g, function(m){return({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[m])})}
  function pad2(n){ return (n<10?'0':'')+n; }

  function normalizeTags(tags){
    if (!tags) return [];
    if (Array.isArray(tags)) return tags.map(function(t){return String(t).trim()}).filter(Boolean);
    return String(tags).split(",").map(function(x){return x.trim()}).filter(Boolean);
  }

  var menu = null;
  var state = { week:"", qty:{}, filters:{ sections:[], tags:[] } };
  var countdownTimer = null;

  function flatItems(){
    return (menu && menu.sections ? menu.sections : [])
      .flatMap(function(s){
        return (s.items||[]).map(function(it){
          return Object.assign({_sectionTitle:s.title}, it);
        });
      });
  }
  function byId(id){ return flatItems().find(function(i){ return String(i.id)===String(id); }); }
  function totalPortions(){ return Object.values(state.qty).reduce(function(a,b){return a+b},0); }
  function totalPrice(){
    return Object.entries(state.qty).reduce(function(sum, kv){
      var it=byId(kv[0]); return sum + (it ? (Number(it.price)||0)*kv[1] : 0);
    }, 0);
  }
  function persist(){ try{ localStorage.setItem(LS_KEY, JSON.stringify(state)); }catch(e){} }
  function restore(){
    try{
      var raw=localStorage.getItem(LS_KEY); if(!raw) return;
      var saved=JSON.parse(raw);
      if(saved && typeof saved==="object"){
        state.qty=saved.qty||{};
        state.filters=saved.filters||{sections:[],tags:[]};
      }
    }catch(e){}
  }

  /* ======== DELIVERY ======== */
  var RU_WEEKDAYS = ['Вск','Пн','Вт','Ср','Чт','Пт','Сб'];
  var RU_WEEKDAYS_FULL = ['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];

  function dateAtTZ(date, tz){
    try{ var s = date.toLocaleString('sv-SE', { timeZone: tz }); return new Date(s.replace(' ', 'T')); }
    catch(e){ return new Date(date); }
  }
  function nextWeekday(from, weekday){
    var d = new Date(from.getFullYear(), from.getMonth(), from.getDate());
    while (d.getDay() !== weekday) d.setDate(d.getDate()+1);
    if (d < from) d.setDate(d.getDate()+7);
    return d;
  }
  function sameWeekPrevWeekday(baseDate, weekday){
    var d = new Date(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate());
    while (d.getDay() !== weekday) d.setDate(d.getDate()-1);
    return d;
  }
  function parseHHMM(s){ var m=/^(\d{1,2}):(\d{2})$/.exec(String(s||'')); return m?{h:+m[1],m:+m[2]}:{h:14,m:0}; }
  function formatISODate(d){ return d.getFullYear()+'-'+pad2(d.getMonth()+1)+'-'+pad2(d.getDate()); }
  function humanDeadline(deadline){ return RU_WEEKDAYS[deadline.getDay()]+' '+pad2(deadline.getHours())+':'+pad2(deadline.getMinutes()); }

  function computeDelivery(config){
    var tz = (config && config.timezone) || 'UTC';
    var now = dateAtTZ(new Date(), tz);
    var targets = [];
    if (config.tuesday && config.tuesday.enabled) targets.push({name:'tuesday', weekday:2, dl:config.tuesday.deadline});
    if (config.friday  && config.friday.enabled)  targets.push({name:'friday',  weekday:5, dl:config.friday.deadline});
    if (!targets.length) return null;
    var blackout = Array.isArray(config.blackout)? config.blackout : [];

    function candidate(afterDate, target){
      var deliver = nextWeekday(afterDate, target.weekday);
      var dlday = sameWeekPrevWeekday(deliver, target.dl && Number.isInteger(target.dl.dow) ? target.dl.dow : 0);
      var hm = parseHHMM(target.dl && target.dl.time);
      var deadline = new Date(dlday.getFullYear(), dlday.getMonth(), dlday.getDate(), hm.h, hm.m, 0);
      return { name:target.name, deliver:deliver, deadline:deadline };
    }

    var pointer = new Date(now.getTime());
    var current = null;
    for (var step=0; step<14; step++){
      var best=null;
      for (var i=0;i<targets.length;i++){
        var c = candidate(pointer, targets[i]);
        if (!best || c.deliver < best.deliver) best = c;
      }
      var iso = formatISODate(best.deliver);
      var blocked = blackout.indexOf(iso)!==-1;
      if (!blocked){
        if (now <= best.deadline){ current = best; break; }
        pointer = new Date(best.deliver.getTime()); pointer.setDate(pointer.getDate()+1);
      } else {
        pointer = new Date(best.deliver.getTime()); pointer.setDate(pointer.getDate()+1);
      }
    }
    if (!current) current = candidate(pointer, targets[0]);

    return {
      target: current.name,
      tz: tz,
      date: formatISODate(current.deliver),
      weekday: RU_WEEKDAYS[current.deliver.getDay()],
      weekday_full: RU_WEEKDAYS_FULL[current.deliver.getDay()],
      deadline_iso: current.deadline.toISOString(),
      deadline_human: humanDeadline(current.deadline)
    };
  }

  function renderBanner(node, cfg, delivery){
    if (!node || !cfg || !delivery) return;
    var tpl = (cfg.banner || 'Доставка {weekday_short}, {delivery_date}. Дедлайн {deadline}. Осталось {countdown}');
    function fill(countdown){
      var text = tpl
        .replace('{delivery_date}', delivery.date)
        .replace('{weekday}', delivery.weekday_full)
        .replace('{weekday_short}', delivery.weekday)
        .replace('{deadline}', delivery.deadline_human)
        .replace('{countdown}', countdown || '');
      node.innerHTML = '<div class="wmb-delivery">'+escapeHtml(text)+'</div>';
    }
    function tick(){
      var now = new Date();
      var ms = new Date(delivery.deadline_iso).getTime() - now.getTime();
      if (ms <= 0){ fill('0д 00:00'); clearInterval(countdownTimer); return; }
      var sec = Math.floor(ms/1000), h = Math.floor(sec/3600);
      var m = Math.floor((sec%3600)/60), s = sec%60;
      fill(pad2(h)+':'+pad2(m)+':'+pad2(s));
    }
    if (countdownTimer) clearInterval(countdownTimer);
    tick();
    countdownTimer = setInterval(tick, 1000);
  }

  /* ======== INGREDIENTS MODAL ======== */
  function ensureIngredientsModal(){
    if (document.getElementById('wmb-ing-modal')) return;
    document.body.insertAdjacentHTML('beforeend', [
      '<div id="wmb-ing-modal" aria-hidden="true">',
        '<div class="back" tabindex="-1"></div>',
        '<div class="dialog" role="dialog" aria-modal="true" aria-labelledby="wmb-ing-title">',
          '<button class="close" aria-label="Закрыть">×</button>',
          '<h3 id="wmb-ing-title">Состав</h3>',
          '<div id="wmb-ing-allergens" class="allergy-row" style="display:none"></div>',
          '<pre id="wmb-ing-text"></pre>',
        '</div>',
      '</div>'
    ].join(''));
    var modal = el('#wmb-ing-modal');
    var close = function(){ modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); };
    modal.querySelector('.back').addEventListener('click', close);
    modal.querySelector('.close').addEventListener('click', close);
    document.addEventListener('keydown', function(e){ if(e.key==='Escape') close(); });
  }

  function openIngredientsFor(id){
    var item = byId(id);
    if (!item) return;
    ensureIngredientsModal();
    var modal = el('#wmb-ing-modal');
    el('#wmb-ing-title').textContent = item.name || 'Состав';
    el('#wmb-ing-text').textContent  = (item.ingredients || '').trim();

    var wrap = el('#wmb-ing-allergens');
    var arr = Array.isArray(item.allergens) ? item.allergens : [];
    if (arr.length){
      wrap.style.display = '';
      wrap.innerHTML = '<strong>Аллергены:</strong> ' + arr.map(function(a){
        return '<span class="pill">'+escapeHtml(a)+'</span>';
      }).join('');
    } else {
      wrap.style.display = 'none';
      wrap.innerHTML = '';
    }
    modal.classList.add('open');
    modal.setAttribute('aria-hidden','false');
  }

  /* ======== FILTERS ======== */
  function allSectionTitles(){ return (menu.sections||[]).map(function(s){return s.title}); }
  function allTags(){
    var set = new Set();
    (menu.sections||[]).forEach(function(s){ (s.items||[]).forEach(function(it){ normalizeTags(it.tags).forEach(function(t){ set.add(t) }) }) });
    return Array.from(set).sort(function(a,b){return a.localeCompare(b)});
  }
  function itemPasses(item, sectionTitle){
    var sOk = true, tOk = true;
    if (state.filters.sections && state.filters.sections.length){
      sOk = state.filters.sections.indexOf(sectionTitle) !== -1;
    }
    if (state.filters.tags && state.filters.tags.length){
      var itemTags = normalizeTags(item.tags);
      tOk = state.filters.tags.every(function(t){ return itemTags.indexOf(t)!==-1; });
    }
    return sOk && tOk;
  }

  /* ======== UI RENDER ======== */
  function render(root){
    if (!menu) { root.innerHTML = '<div class="wmb-loading">Загрузка меню…</div>'; return; }

    ensureIngredientsModal();

    var secTitles = allSectionTitles();
    var tags = allTags();
    var dcfg = menu.delivery_config || null;
    var delivery = dcfg ? computeDelivery(dcfg) : null;

    root.innerHTML = [
      '<div class="wmb-wrapper">',
        '<div class="wmb-header">',
          '<div>',
            (menu.description ? '<div class="wmb-sub">'+escapeHtml(menu.description)+'</div>' : ''),
            '<div id="wmb-banner" class="wmb-banner" aria-live="polite"></div>',
          '</div>',
        '</div>',

        '<div class="wmb-filters">',
          (secTitles.length ? [
            '<div class="wmb-filter-group">',
              '<div class="wmb-filter-title">Категории</div>',
              '<div class="wmb-chips">',
                secTitles.map(function(t){
                  var act = state.filters.sections.indexOf(t)!==-1 ? 'is-active' : '';
                  return '<button class="wmb-chip '+act+'" data-type="section" data-value="'+escapeHtml(t)+'">'+escapeHtml(t)+'</button>';
                }).join(''),
                '<button class="wmb-chip wmb-chip-reset" data-type="section" data-reset="1" '+(state.filters.sections.length?'':'disabled')+'>Сбросить</button>',
              '</div>',
            '</div>'
          ].join('') : ''),
          (tags.length ? [
            '<div class="wmb-filter-group">',
              '<div class="wmb-filter-title">Теги</div>',
              '<div class="wmb-chips">',
                tags.map(function(t){
                  var act = state.filters.tags.indexOf(t)!==-1 ? 'is-active' : '';
                  return '<button class="wmb-chip '+act+'" data-type="tag" data-value="'+escapeHtml(t)+'">'+escapeHtml(t)+'</button>';
                }).join(''),
                '<button class="wmb-chip wmb-chip-reset" data-type="tag" data-reset="1" '+(state.filters.tags.length?'':'disabled')+'>Сбросить</button>',
              '</div>',
            '</div>'
          ].join('') : ''),
        '</div>',

        '<div class="wmb-body">',
          '<div class="wmb-catalog">',
            (menu.sections||[]).map(renderSection).join('') || '<div class="wmb-empty">Пока нет блюд.</div>',
          '</div>',
          '<aside class="wmb-sidebar">',
            '<div class="wmb-summary">',
              '<div class="wmb-summary-row"><span>Порций</span><strong id="wmb-total-portions">'+totalPortions()+'</strong></div>',
              '<div class="wmb-summary-row"><span>Итого</span><strong id="wmb-total-price">'+money(totalPrice())+'</strong></div>',
              '<button id="wmb-checkout" class="wmb-checkout-btn" '+(totalPortions()===0?'disabled':'')+'>Перейти к оформлению</button>',
            '</div>',
          '</aside>',
        '</div>',
      '</div>'
    ].join("");

    if (delivery && dcfg){ renderBanner(el('#wmb-banner', root), dcfg, delivery); }

    // фильтры
    els('.wmb-chip', root).forEach(function(chip){
      var type = chip.getAttribute('data-type');
      var val  = chip.getAttribute('data-value');
      var reset= chip.getAttribute('data-reset')==='1';
      chip.addEventListener('click', function(){
        if (reset){
          if (type==='section') state.filters.sections = [];
          if (type==='tag')     state.filters.tags = [];
          persist(); render(root); return;
        }
        if (type==='section'){
          var arr = state.filters.sections;
          var i = arr.indexOf(val);
          if (i===-1) arr.push(val); else arr.splice(i,1);
        } else if (type==='tag'){
          var arr = state.filters.tags;
          var i = arr.indexOf(val);
          if (i===-1) arr.push(val); else arr.splice(i,1);
        }
        persist(); render(root);
      });
    });

    // qty
    els('.wmb-qty-inc', root).forEach(function(b){ b.addEventListener('click', function(){ changeQty(b.dataset.id, +1, root); }) });
    els('.wmb-qty-dec', root).forEach(function(b){ b.addEventListener('click', function(){ changeQty(b.dataset.id, -1, root); }) });

    // состав
    els('.wmb-ing-btn', root).forEach(function(b){
      b.addEventListener('click', function(){ openIngredientsFor(b.getAttribute('data-id')); });
    });

    var btn = el('#wmb-checkout', root); if (btn) btn.addEventListener('click', function(){ onCheckout(delivery); });
  }

  function renderSection(section){
    var items = (section.items||[])
      .map(function(it){ return Object.assign({_sectionTitle: section.title}, it); })
      .filter(function(it){ return itemPasses(it, section.title); });

    if (!items.length) return "";
    return [
      '<section class="wmb-section">',
        '<h2 class="wmb-section-title">'+escapeHtml(section.title)+'</h2>',
        '<div class="wmb-grid">',
          items.map(renderCard).join(''),
        '</div>',
      '</section>'
    ].join('');
  }

  function renderCard(item){
    var q = state.qty[item.id] || 0;
    var unit = item.unit || item.unit_text || item.unit_label || "";
    var tags = normalizeTags(item.tags);
    var allergens = Array.isArray(item.allergens) ? item.allergens : [];
    var hasIngredients = !!(item.ingredients && String(item.ingredients).trim().length);

    return [
      '<div class="wmb-card">',
        '<div class="wmb-card-title">',
          escapeHtml(item.name),
          (hasIngredients ? ' <button class="wmb-ing-btn" data-id="'+item.id+'" aria-label="Состав блюда '+escapeHtml(item.name)+'">Состав</button>' : ''),
        '</div>',
        '<div class="wmb-card-meta">',
          '<span>'+money(Number(item.price)||0)+'</span>',
          (unit ? '<span class="wmb-unit">'+escapeHtml(unit)+'</span>' : ''),
        '</div>',
        (tags.length? '<div class="wmb-card-tags">'+tags.map(function(t){return '<span class="wmb-tag">'+escapeHtml(t)+'</span>'}).join('')+'</div>' : ''),
        (allergens.length ? '<div class="wmb-allergens">'+allergens.map(function(a){ return '<span class="wmb-allergen" title="Аллерген"><i>⚠️</i>'+escapeHtml(a)+'</span>'; }).join('')+'</div>' : ''),
        '<div class="wmb-qty">',
          '<button class="wmb-qty-dec" data-id="'+item.id+'" '+(q===0?'disabled':'')+' aria-label="Уменьшить">–</button>',
          '<span class="wmb-qty-value" aria-live="polite">'+q+'</span>',
          '<button class="wmb-qty-inc" data-id="'+item.id+'" aria-label="Увеличить">+</button>',
        '</div>',
      '</div>'
    ].join('');
  }

  function changeQty(id, delta, root){
    var cur = state.qty[id] || 0;
    var next = cur + delta;
    if (next < 0) next = 0;
    if (next !== cur) {
      state.qty[id] = next;
      if (next === 0) delete state.qty[id];
      persist();
      render(root);
    }
  }

  async function onCheckout(deliveryInfo){
    try{
      var payload = {
        week: state.week || "",
        items: Object.fromEntries(Object.entries(state.qty).map(function(kv){return [kv[0], Number(kv[1])]})),
        total_portions: totalPortions(),
        total_price: Number((Math.round(totalPrice()*100)/100).toFixed(2))
      };
      if (deliveryInfo){
        payload.delivery = {
          target: deliveryInfo.target,
          tz: deliveryInfo.tz,
          date: deliveryInfo.date,
          weekday: deliveryInfo.weekday,
          weekday_ru: deliveryInfo.weekday_full,
          deadline_iso: deliveryInfo.deadline_iso,
          deadline_human: deliveryInfo.deadline_human
        };
      }
      var body = new URLSearchParams();
      body.append('action','wmb_add_to_cart');
      body.append('nonce', CFG.nonce);
      body.append('product_id', String(CFG.product_id));
      body.append('payload', JSON.stringify(payload));

      var res = await fetch(CFG.ajax_url, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
        body: body.toString(),
        credentials: 'same-origin'
      });
      var json = await res.json();
      if (!json || !json.success) throw new Error(json && json.data ? json.data : 'Ошибка AJAX');
      if (json.data && json.data.redirect) window.location.href = json.data.redirect;
      else alert('Добавлено в корзину, но ссылка на корзину не получена.');
    }catch(e){
      console.error(e);
      alert('Не удалось добавить в корзину.');
    }
  }

  async function boot(){
    var root = document.getElementById('meal-builder-root');
    if (!root) return;

    try{
      var res = await fetch(MENU_URL, {credentials:'same-origin'});
      if (!res.ok) throw new Error('HTTP '+res.status);
      menu = await res.json();
    }catch(e){
      console.error('Не удалось загрузить меню из', MENU_URL, e);
      menu = { description:'', sections: [] };
    }
    restore();
    render(root);
  }

  document.addEventListener('DOMContentLoaded', boot);
})();
