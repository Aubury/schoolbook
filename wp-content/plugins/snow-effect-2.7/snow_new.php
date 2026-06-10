<?php
/**
 * Plugin Name: Snow Effect (UI by URL param)
 * Description: Эффект снега. Панель управления показывается только администраторам при наличии ?snow_ui=1 в URL. px/s timing, rotation, live controls, cross-tab sync.
 * Version: 2.7.3
 * Author: VD School (adapted)
 * Text Domain: snow-effect
 */

if (!defined('ABSPATH')) exit;

/**
 * Используем уникальное имя класса, чтобы избежать конфликта с другими копиями.
 */
if (!class_exists('VD_SnowEffectPlugin')) {

    class VD_SnowEffectPlugin {
        private $opt_key = 'snow_effect_options';
        private $defaults = array(
            'enabled'      => '1',
            'color'        => '#ffffff',
            'density'      => '0',
            'max_radius'   => '5',
            'speed'        => '1',
            'rotation'     => '1'
        );

        public function __construct(){
            add_action('admin_menu', array($this,'admin_menu'));
            add_action('admin_init', array($this,'maybe_save_options'));
            add_action('wp_enqueue_scripts', array($this,'enqueue_assets'));
            add_action('wp_ajax_snow_effect_save_settings', array($this,'ajax_save_settings'));
            add_shortcode('snow_effect', array($this,'shortcode_render'));
        }

        private function get_options(){
            $opts = get_option($this->opt_key, array());
            return wp_parse_args($opts, $this->defaults);
        }

        public function admin_menu(){
            add_options_page('Snow Effect', 'Snow Effect', 'manage_options', 'snow-effect', array($this,'options_page'));
        }

        public function maybe_save_options(){
            if (!current_user_can('manage_options')) return;
            if (empty($_POST['snow_effect_nonce'])) return;
            if (!wp_verify_nonce($_POST['snow_effect_nonce'], 'snow_effect_save')) return;

            $raw_color = $_POST['color'] ?? $this->defaults['color'];
            $color = $this->defaults['color'];
            if (function_exists('sanitize_hex_color')) {
                $c = sanitize_hex_color($raw_color);
                if (!empty($c)) $color = $c;
            } else {
                if (is_string($raw_color) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $raw_color)) {
                    $color = $raw_color;
                }
            }

            $rotation = isset($_POST['rotation']) && is_numeric($_POST['rotation']) ? floatval($_POST['rotation']) : $this->defaults['rotation'];

            $opts = array(
                'enabled'    => isset($_POST['enabled']) ? '1' : '0',
                'color'      => $color,
                'density'    => is_numeric($_POST['density']) ? sanitize_text_field($_POST['density']) : $this->defaults['density'],
                'max_radius' => is_numeric($_POST['max_radius']) ? sanitize_text_field($_POST['max_radius']) : $this->defaults['max_radius'],
                'speed'      => is_numeric($_POST['speed']) ? sanitize_text_field($_POST['speed']) : $this->defaults['speed'],
                'rotation'   => (string)$rotation
            );
            update_option($this->opt_key, $opts);
            add_settings_error('snow_effect_messages','saved',__('Настройки сохранены','snow-effect'),'updated');
        }

        public function options_page(){
            if (!current_user_can('manage_options')) wp_die(__('No.'));
            $opts = $this->get_options();
            settings_errors('snow_effect_messages');
            ?>
            <div class="wrap">
                <h1>Snow Effect — настройки</h1>
                <form method="post" action="">
                    <?php wp_nonce_field('snow_effect_save','snow_effect_nonce'); ?>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">Авто-вставка на всех страницах</th>
                                <td><label><input type="checkbox" name="enabled" value="1" <?php checked($opts['enabled'],'1'); ?>> Включено</label></td>
                            </tr>
                            <tr>
                                <th scope="row">Цвет (контур при fallback)</th>
                                <td><input type="color" name="color" value="<?php echo esc_attr($opts['color']); ?>"></td>
                            </tr>
                            <tr>
                                <th scope="row">Плотность (0 = auto)</th>
                                <td><input type="number" name="density" min="0" max="1200" value="<?php echo esc_attr($opts['density']); ?>"></td>
                            </tr>
                            <tr>
                                <th scope="row">Макс радиус</th>
                                <td><input type="number" step="0.1" name="max_radius" min="1" max="50" value="<?php echo esc_attr($opts['max_radius']); ?>"></td>
                            </tr>
                            <tr>
                                <th scope="row">Скорость (множитель)</th>
                                <td><input type="number" step="0.05" name="speed" min="0.1" max="3" value="<?php echo esc_attr($opts['speed']); ?>"></td>
                            </tr>
                            <tr>
                                <th scope="row">Rotation (множитель)</th>
                                <td><input type="number" step="0.05" name="rotation" min="0" max="5" value="<?php echo esc_attr($opts['rotation']); ?>"> — интенсивность вращения</td>
                            </tr>
                        </tbody>
                    </table>
                    <?php submit_button(); ?>
                </form>

                <h2>Ручная вставка</h2>
                <p>Используйте шорткод <code>[snow_effect]</code> — эффект запустится и там. Панель администратора показывается только при параметре <code>?snow_ui=1</code> в URL.</p>
            </div>
            <?php
        }

        public function enqueue_assets(){
            $opts = $this->get_options();

            wp_register_script('vd-snow-effect-js', '', array(), null, true);
            wp_enqueue_script('vd-snow-effect-js');

            $show_ui = false;
            if ( current_user_can('manage_options') && isset($_GET['snow_ui']) ) {
                $param = sanitize_text_field(wp_unslash($_GET['snow_ui']));
                if ($param === '1') $show_ui = true;
            }

            $snowflake_local_path = ''; // при желании укажите sprite

            $settings = array(
                'color'      => $opts['color'],
                'density'    => ($opts['density'] === '0' ? null : (int)$opts['density']),
                'maxRadius'  => (float)$opts['max_radius'],
                'speed'      => (float)$opts['speed'],
                'rotation'   => (float)$opts['rotation'],
                'enabled'    => $opts['enabled'] === '1' ? true : false,
                'showUI'     => $show_ui,
                'ajax_url'   => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('snow_effect_ajax_nonce'),
                'snowflakeUrl' => $snowflake_local_path,
            );
            wp_localize_script('vd-snow-effect-js','SnowEffectSettings', $settings);

            wp_add_inline_script('vd-snow-effect-js', $this->get_full_js());
        }

        private function get_full_js(){
            return <<<'JS'
(function(){
  'use strict';
  var S = window.SnowEffectSettings || {};
  var autoEnabled = !!S.enabled;

  function log() {
    try { console.log.apply(console, ['[Snow]'].concat(Array.prototype.slice.call(arguments))); } catch(e){}
  }

  function clamp(v, a, b) {
    if (typeof v !== 'number' || isNaN(v) || !isFinite(v)) return a;
    if (v < a) return a;
    if (v > b) return b;
    return v;
  }

  function createSnowInline(options){
    options = options || {};

    // --- SINGLE-INSTANCE / CLEANUP: удаляем старый canvas и контроллер, если остались ---
    try {
      var oldCanvas = document.getElementById('site-snow-canvas');
      if (oldCanvas && oldCanvas.parentNode) {
        console && console.log && console.log('[Snow] removing existing canvas');
        oldCanvas.parentNode.removeChild(oldCanvas);
      }
      if (window.__vd_snow_controller && typeof window.__vd_snow_controller.destroy === 'function') {
        try {
          console && console.log && console.log('[Snow] destroying previous controller');
          window.__vd_snow_controller.destroy();
        } catch(e){}
        window.__vd_snow_controller = null;
      }
    } catch(e){}

    var o = {
      color: options.color || '#ffffff',
      density: (typeof options.density === 'number') ? options.density : null,
      maxRadius: (typeof options.maxRadius === 'number') ? options.maxRadius : 5,
      minRadius: 1.2,
      speedMultiplier: (typeof options.speedMultiplier === 'number') ? options.speedMultiplier : 1,
      rotationMultiplier: (typeof options.rotationMultiplier === 'number') ? options.rotationMultiplier : 1,
      horizMaxPxPerSec: 40,
      force: !!options.force
    };

    // ensure sane radii bounds
    if (!isFinite(o.maxRadius) || o.maxRadius <= 0) o.maxRadius = 5;
    if (o.maxRadius < o.minRadius) o.maxRadius = o.minRadius + 1;

    try { if (!o.force && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { log('reduced-motion active, aborting'); return null; } } catch(e){}

    var canvas = document.createElement('canvas');
    canvas.id = 'site-snow-canvas';
    canvas.style.position='fixed'; canvas.style.left='0'; canvas.style.top='0';
    canvas.style.pointerEvents='none'; canvas.style.zIndex=String(2147483646);
    canvas.style.width='100%'; canvas.style.height='100%';
    document.body.appendChild(canvas);
    var ctx = canvas.getContext && canvas.getContext('2d');
    if (!ctx) { if (canvas.parentNode) canvas.parentNode.removeChild(canvas); return null; }

    var DPR = window.devicePixelRatio||1;
    var flakes = [], flakeCount = o.density || Math.max(30, Math.floor(window.innerWidth/22));
    flakeCount = Math.min(flakeCount, 1200);
    var raf = null;
    var lastTime = performance.now();

    function rand(a,b){return Math.random()*(b-a)+a;}
    function resize(){ canvas.width = Math.ceil(window.innerWidth*DPR); canvas.height = Math.ceil(window.innerHeight*DPR); canvas.style.width = window.innerWidth+'px'; canvas.style.height = window.innerHeight+'px'; ctx.setTransform(DPR,0,0,DPR,0,0); }

    function makeFlake(){
      var r = rand(o.minRadius,o.maxRadius);
      r = clamp(r, o.minRadius, o.maxRadius * 1.4); // clamp at creation
      if (r < 0.35) r = 0.35;
      var baseVyPx = rand(40, 140) * (0.6 + r/6);
      var maxH = o.horizMaxPxPerSec * (0.6 + r/6);
      var baseVxPx = rand(-maxH, maxH);
      var phase = rand(0, Math.PI*2);
      var baseRot = rand(-1.2, 1.2) * (0.6 + r/8);

      return {
        x: rand(0,window.innerWidth),
        y: rand(-window.innerHeight, -10),
        r: r,
        baseVxPx: baseVxPx,
        baseVyPx: baseVyPx,
        baseRot: baseRot,
        vx: baseVxPx * o.speedMultiplier,
        vy: baseVyPx * o.speedMultiplier,
        rot: rand(0,Math.PI*2),
        rotSpeed: baseRot * o.rotationMultiplier * o.speedMultiplier,
        o: rand(0.45,0.98),
        phase: phase,
        melting: false
      };
    }

    function resetFlake(f){
      var nf = makeFlake();
      f.x = nf.x; f.y = nf.y; f.r = nf.r;
      f.baseVxPx = nf.baseVxPx; f.baseVyPx = nf.baseVyPx; f.baseRot = nf.baseRot;
      f.vx = nf.vx; f.vy = nf.vy; f.rot = nf.rot; f.rotSpeed = nf.rotSpeed;
      f.o = nf.o; f.phase = nf.phase; f.melting = false;
    }

    function init(){ flakes = []; for(var i=0;i<flakeCount;i++) flakes.push(makeFlake()); }
    function ensureCount(){ if (flakes.length !== flakeCount) init(); }

    function drawSnowflake(ctx,f){
      ctx.save();
      ctx.translate(f.x,f.y);
      ctx.rotate(f.rot);
      ctx.globalAlpha = f.o;

      var haloR = Math.max(6, f.r * 3.2);
      var g = ctx.createRadialGradient(0,0,0,0,0,haloR);
      g.addColorStop(0,'rgba(255,255,255,1)');
      g.addColorStop(0.5,'rgba(255,255,255,0.6)');
      g.addColorStop(1,'rgba(255,255,255,0)');
      ctx.globalCompositeOperation = 'lighter';
      ctx.fillStyle = g;
      ctx.beginPath();
      ctx.arc(0,0, haloR, 0, Math.PI*2);
      ctx.fill();
      ctx.globalCompositeOperation = 'source-over';

      ctx.lineWidth = Math.max(0.35, f.r/2);
      ctx.lineCap = 'round';
      ctx.strokeStyle = o.color;
      ctx.shadowColor = 'rgba(255,255,255,0.6)';
      ctx.shadowBlur = Math.max(2, f.r * 1.6);

      var base = Math.max(6,f.r*4.0), L=base;
      ctx.beginPath();
      for(var a=0;a<8;a++){
        var ang = a*(Math.PI/4);
        var x2 = Math.cos(ang)*L, y2 = Math.sin(ang)*L;
        ctx.moveTo(0,0);
        ctx.lineTo(x2,y2);

        var branchPos1 = 0.38*L, branchPos2 = 0.72*L;
        var sideLen1 = 0.28*L, sideLen2 = 0.18*L;
        var sideAngA = ang + Math.PI/10, sideAngB = ang - Math.PI/10;

        var bx1 = Math.cos(ang)*branchPos1, by1 = Math.sin(ang)*branchPos1;
        ctx.moveTo(bx1,by1); ctx.lineTo(bx1 + Math.cos(sideAngA)*sideLen1, by1 + Math.sin(sideAngA)*sideLen1);
        ctx.moveTo(bx1,by1); ctx.lineTo(bx1 + Math.cos(sideAngB)*sideLen1, by1 + Math.sin(sideAngB)*sideLen1);

        var bx2 = Math.cos(ang)*branchPos2, by2 = Math.sin(ang)*branchPos2;
        ctx.moveTo(bx2,by2); ctx.lineTo(bx2 + Math.cos(sideAngA)*sideLen2, by2 + Math.sin(sideAngA)*sideLen2);
        ctx.moveTo(bx2,by2); ctx.lineTo(bx2 + Math.cos(sideAngB)*sideLen2, by2 + Math.sin(sideAngB)*sideLen2);
      }
      ctx.stroke();

      ctx.shadowBlur = 0;
      for (var i=0;i<6;i++){
        var ang = Math.random()*Math.PI*2;
        var dist = (f.r*1.2) + Math.random()*(f.r*3);
        ctx.beginPath();
        ctx.fillStyle = 'rgba(255,255,255,1)';
        ctx.arc(Math.cos(ang)*dist, Math.sin(ang)*dist, Math.max(0.6, f.r*0.25*Math.random()), 0, Math.PI*2);
        ctx.fill();
      }

      ctx.beginPath();
      ctx.fillStyle = 'rgba(255,255,255,1)';
      ctx.arc(0,0, Math.max(0.9, f.r*0.6), 0, Math.PI*2);
      ctx.fill();

      ctx.globalAlpha = 1;
      ctx.restore();
    }

    function drawAll(){ ctx.clearRect(0,0,canvas.width,canvas.height); for(var i=0;i<flakes.length;i++){ var f=flakes[i]; drawSnowflake(ctx,f);} }

    function normalizeXWrap(x, margin){
      var totalWidth = window.innerWidth + margin*2;
      return ((x + margin) % totalWidth + totalWidth) % totalWidth - margin;
    }

    // плавная интерполяция
    function lerp(a,b,t){ return a + (b-a) * t; }

    function step(now){
      if (!now) now = performance.now();
      var dt = Math.min(0.12, (now - lastTime)/1000); // cap dt
      lastTime = now;

      ensureCount();

      var margin = 60;

      for(var i=0;i<flakes.length;i++){
        var f = flakes[i];

        if (f.melting) {
          var meltRate = 0.9 * o.speedMultiplier;
          f.o -= meltRate * dt;
          f.r *= Math.max(0.92, 1 - 0.25 * dt);
          f.vx *= Math.max(0.5, 1 - 0.6 * dt);
          f.vy *= Math.max(0.5, 1 - 0.6 * dt);
          f.rot += f.rotSpeed * dt * 0.6;
          if (f.o <= 0.02 || f.r < 0.35) {
            resetFlake(f);
          }
          continue;
        }

        // целевая горизонтальная скорость (px/s) = базовая + мгновенная "ветровая" составляющая
        var windAmp = 8 * (0.6 + f.r/6);
        var windPxPerSec = Math.sin((f.y * 0.004) + f.phase) * windAmp * o.speedMultiplier;

        // целевой vx — комбинируем базовую (baseVxPx*speed) и ветер
        var targetVx = (f.baseVxPx * o.speedMultiplier) + windPxPerSec;

        // сглаженно подводим текущую скорость к targetVx
        var smooth = Math.min(1, 0.06 + 6*dt);
        f.vx = lerp(f.vx, targetVx, smooth);

        // вертикальная скорость напрямую определяется базовой вертикальной скоростью
        f.vy = f.baseVyPx * o.speedMultiplier;

        // позиции — интегрируем по реальному времени (px/s -> px)
        f.x += f.vx * dt;
        f.y += f.vy * dt;

        // вращение
        f.rot += f.rotSpeed * dt;

        // когда снежинка опустилась ниже экрана — плавное таяние / ресет
        if (f.y > window.innerHeight + 6) {
          f.melting = true;
          f.vy = Math.max(12, f.vy * 0.45);
          f.vx = f.vx * 0.5 + (Math.random() - 0.5) * 6;
        }

        // корректный wrap по X, без сжатия к центру
        f.x = normalizeXWrap(f.x, margin);

        // защита от неожиданно большого радиуса: всегда держим r в разумных пределах
        var maxAllowed = Math.max(o.maxRadius * 1.4, o.minRadius + 0.1);
        f.r = clamp(f.r, o.minRadius, maxAllowed);

        // если вдруг r стало экстремально большим (артефакт), логируем и ресетим снежинку
        if (!isFinite(f.r) || f.r > (o.maxRadius * 3)) {
          log('Warning: extreme flake radius detected, resetting', f.r, 'maxRadius=', o.maxRadius);
          resetFlake(f);
        }
      }

      drawAll();
      raf = requestAnimationFrame(step);
    }

    function start(){ if (raf) cancelAnimationFrame(raf); resize(); init(); lastTime = performance.now(); raf = requestAnimationFrame(step); log('init (px/s mode) userSpeed=', o.speedMultiplier, 'rotation=', o.rotationMultiplier); }
    function stop(){ if (raf) cancelAnimationFrame(raf); raf=null; if (canvas && canvas.parentNode) canvas.parentNode.removeChild(canvas); }

    window.addEventListener('resize', function(){ try{ resize(); init(); }catch(e){} }, {passive:true});
    start();

    // expose safe pointer so new instances can clean up old ones
    try { window.__vd_snow_controller = { destroy: stop }; } catch(e){}

    return {
      destroy: stop,
      setColor: function(c){ o.color = c; },
      setDensity: function(n){
        if (!n || n<=0) flakeCount = Math.max(30, Math.floor(window.innerWidth/22));
        else flakeCount = Math.min(1200, Math.floor(n));
        init();
        log('setDensity ->', flakeCount);
      },
      setMaxRadius: function(v){
        var newMax = Number(v) || 5;
        if (!isFinite(newMax) || newMax <= 0) newMax = 5;
        o.maxRadius = Math.max(o.minRadius + 0.1, newMax);
        // clamp existing flakes radii
        for(var i=0;i<flakes.length;i++){
          flakes[i].r = clamp(flakes[i].r, o.minRadius, o.maxRadius * 1.4);
        }
        init();
        log('setMaxRadius ->', o.maxRadius);
      },
      setSpeedMultiplier: function(s){
        o.speedMultiplier = Number(s) || 1;
        for(var i=0;i<flakes.length;i++){
          var f = flakes[i];
          f.vx = f.baseVxPx * o.speedMultiplier;
          f.vy = f.baseVyPx * o.speedMultiplier;
          f.rotSpeed = f.baseRot * o.rotationMultiplier * o.speedMultiplier;
        }
        log('setSpeedMultiplier ->', o.speedMultiplier);
      },
      setRotation: function(r){
        o.rotationMultiplier = Number(r) || 1;
        for(var i=0;i<flakes.length;i++){
          flakes[i].rotSpeed = flakes[i].baseRot * o.rotationMultiplier * o.speedMultiplier;
        }
        log('setRotation ->', o.rotationMultiplier);
      },
      getOptions: function(){ return { density: flakeCount, maxRadius: o.maxRadius, speedMultiplier: o.speedMultiplier, rotationMultiplier: o.rotationMultiplier }; }
    };
  }

  // cross-tab sync (BroadcastChannel + localStorage fallback)
  (function setupCrossTab(){
    if ('BroadcastChannel' in window) {
      try {
        var bc = new BroadcastChannel('snow-effect-channel');
        bc.onmessage = function(ev){
          if (!ev.data) return;
          try { if (window.snowController) {
            window.snowController.setColor && window.snowController.setColor(ev.data.color);
            window.snowController.setDensity && window.snowController.setDensity(Number(ev.data.density)||0);
            window.snowController.setMaxRadius && window.snowController.setMaxRadius(Number(ev.data.max_radius)||5);
            window.snowController.setSpeedMultiplier && window.snowController.setSpeedMultiplier(Number(ev.data.speed)||1);
            window.snowController.setRotation && window.snowController.setRotation(Number(ev.data.rotation)||1);
          } } catch(e){}
        };
        window.__snowEffectBC = bc;
      } catch(e){}
    }
    window.addEventListener('storage', function(e){
      if (!e) return;
      if (e.key === 'snow-effect-update') {
        try {
          var payload = JSON.parse(e.newValue);
          if (window.snowController) {
            window.snowController.setColor && window.snowController.setColor(payload.color);
            window.snowController.setDensity && window.snowController.setDensity(Number(payload.density)||0);
            window.snowController.setMaxRadius && window.snowController.setMaxRadius(Number(payload.max_radius)||5);
            window.snowController.setSpeedMultiplier && window.snowController.setSpeedMultiplier(Number(payload.speed)||1);
            window.snowController.setRotation && window.snowController.setRotation(Number(payload.rotation)||1);
          }
        } catch(err){}
      }
    }, false);
  })();

  function broadcastUpdate(payload) {
    try {
      if (window.__snowEffectBC) {
        window.__snowEffectBC.postMessage(payload);
      } else {
        localStorage.setItem('snow-effect-update', JSON.stringify(payload));
      }
    } catch(e){}
  }

  function autoStartIfNeeded(){
    if (!autoEnabled) return;
    var startOpts = {
      color: S.color || '#ffffff',
      density: (typeof S.density === 'number' ? S.density : (S.density === null ? null : (S.density ? Number(S.density) : null))),
      maxRadius: (S.maxRadius ? Number(S.maxRadius) : 5),
      speedMultiplier: (S.speed ? Number(S.speed) : 1),
      rotationMultiplier: (S.rotation ? Number(S.rotation) : 1),
      force: true
    };
    var controller = createSnowInline(startOpts);
    if (S.showUI) { injectAdminUI(controller); }
    window.snowController = controller;
  }

  function injectAdminUI(controller){
    if (document.getElementById('snow-ui')) return;
    var div = document.createElement('div');
    div.id = 'snow-ui';
    div.setAttribute('aria-hidden','false');
    div.style.cssText = 'position:fixed;right:12px;top:12px;z-index:100000;background:rgba(6,10,18,0.85);color:#dfefff;padding:10px;border-radius:8px;font-size:13px;box-shadow:0 6px 18px rgba(0,0,0,0.35);max-width:320px;';
    div.innerHTML = '<strong>Snow (admin)</strong>'
      +'<div style="margin-top:8px"><button id="snow-toggle">Остановить</button> <button id="snow-refresh">Recreate</button></div>'
      +'<div style="margin-top:8px">Цвет: <input id="snow-color" type="color" value="'+(S.color||'#ffffff')+'"></div>'
      +'<div style="margin-top:6px">Плотность: <input id="snow-density" type="range" min="0" max="1200" value="'+(S.density||0)+'"> <span id="sdVal">'+(S.density||"auto")+'</span></div>'
      +'<div>Макс радиус: <input id="snow-size" type="range" min="1" max="50" step="0.1" value="'+(S.maxRadius||5)+'"> <span id="ssVal">'+(S.maxRadius||5)+'</span></div>'
      +'<div>Скорость: <input id="snow-speed" type="range" min="0.1" max="3" step="0.01" value="'+(S.speed||1)+'"> <span id="spVal">'+(S.speed||1)+'×</span></div>'
      +'<div>Rotation: <input id="snow-rotation" type="range" min="0" max="5" step="0.01" value="'+(S.rotation||1)+'"> <span id="rotVal">'+(S.rotation||1)+'</span></div>'
      +'<div style="margin-top:6px"><label><input id="snow-respect" type="checkbox"> Respect prefers-reduced-motion</label></div>'
      +'<div style="margin-top:8px"><button id="snow-save">Сохранить</button> <span id="snow-save-msg" style="margin-left:8px"></span></div>';
    document.body.appendChild(div);

    var btnToggle = document.getElementById('snow-toggle');
    var btnRefresh = document.getElementById('snow-refresh');
    var colorEl = document.getElementById('snow-color');
    var densEl = document.getElementById('snow-density');
    var densVal = document.getElementById('sdVal');
    var sizeEl = document.getElementById('snow-size');
    var sizeVal = document.getElementById('ssVal');
    var speedEl = document.getElementById('snow-speed');
    var speedVal = document.getElementById('spVal');
    var rotationEl = document.getElementById('snow-rotation');
    var rotationVal = document.getElementById('rotVal');
    var respectEl = document.getElementById('snow-respect');
    var saveBtn = document.getElementById('snow-save');
    var saveMsg = document.getElementById('snow-save-msg');

    btnToggle.addEventListener('click', function(){
      if (!controller) {
        controller = createSnowInline({ color: colorEl.value, density: densEl.value>0?Number(densEl.value):null, maxRadius: Number(sizeEl.value), speedMultiplier: Number(speedEl.value), rotationMultiplier: Number(rotationEl.value), force: !respectEl.checked });
        this.textContent = 'Остановить';
        window.snowController = controller;
        return;
      }
      controller.destroy && controller.destroy();
      controller = null;
      window.snowController = null;
      this.textContent = 'Запустить';
    });

    btnRefresh.addEventListener('click', function(){
      controller && controller.destroy && controller.destroy();
      controller = createSnowInline({ color: colorEl.value, density: densEl.value>0?Number(densEl.value):null, maxRadius: Number(sizeEl.value), speedMultiplier: Number(speedEl.value), rotationMultiplier: Number(rotationEl.value), force: !respectEl.checked });
      window.snowController = controller;
      btnToggle.textContent = 'Остановить';
    });

    colorEl.addEventListener('input', function(){ controller && controller.setColor && controller.setColor(this.value); });
    densEl.addEventListener('input', function(){ densVal.textContent = (this.value>0?this.value:'auto'); controller && controller.setDensity && controller.setDensity(Number(this.value)); });
    sizeEl.addEventListener('input', function(){ sizeVal.textContent = this.value; controller && controller.setMaxRadius && controller.setMaxRadius(Number(this.value)); });
    speedEl.addEventListener('input', function(){ speedVal.textContent = this.value + '×'; controller && controller.setSpeedMultiplier && controller.setSpeedMultiplier(Number(this.value)); });
    rotationEl.addEventListener('input', function(){ rotationVal.textContent = this.value; controller && controller.setRotation && controller.setRotation(Number(this.value)); });

    saveBtn.addEventListener('click', function(){
      saveMsg.textContent = 'Сохраняю...';
      saveMsg.style.color = '#fff';
      var payload = {
        action: 'snow_effect_save_settings',
        nonce: S.ajax_nonce,
        color: colorEl.value,
        density: densEl.value,
        max_radius: sizeEl.value,
        speed: speedEl.value,
        rotation: rotationEl.value
      };
      var xhr = new XMLHttpRequest();
      xhr.open('POST', S.ajax_url, true);
      xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
      xhr.onreadystatechange = function(){
        if (xhr.readyState !== 4) return;
        if (xhr.status >= 200 && xhr.status < 300) {
          try {
            var res = JSON.parse(xhr.responseText);
            if (res.success) {
              saveMsg.textContent = 'Сохранено';
              saveMsg.style.color = '#9f9';
              var updatePayload = {
                color: colorEl.value,
                density: densEl.value,
                max_radius: sizeEl.value,
                speed: speedEl.value,
                rotation: rotationEl.value,
                ts: Date.now()
              };
              if (window.snowController) {
                window.snowController.setColor && window.snowController.setColor(colorEl.value);
                window.snowController.setDensity && window.snowController.setDensity(Number(densEl.value)>0?Number(densEl.value):0);
                window.snowController.setMaxRadius && window.snowController.setMaxRadius(Number(sizeEl.value));
                window.snowController.setSpeedMultiplier && window.snowController.setSpeedMultiplier(Number(speedEl.value));
                window.snowController.setRotation && window.snowController.setRotation(Number(rotationEl.value));
              }
              broadcastUpdate(updatePayload);
            } else {
              saveMsg.textContent = 'Ошибка: ' + (res.data && res.data.message ? res.data.message : 'server');
              saveMsg.style.color = '#f88';
            }
          } catch(e){
            saveMsg.textContent = 'Ошибка ответа сервера';
            saveMsg.style.color = '#f88';
          }
        } else {
          saveMsg.textContent = 'Ошибка: ' + xhr.status;
          saveMsg.style.color = '#f88';
        }
        setTimeout(function(){ saveMsg.textContent = ''; }, 3000);
      };
      var form = [];
      for (var k in payload) if (payload.hasOwnProperty(k)) {
        form.push(encodeURIComponent(k) + '=' + encodeURIComponent(payload[k]));
      }
      xhr.send(form.join('&'));
    });
  }

  window.addEventListener('DOMContentLoaded', function(){
    var placeholder = document.querySelector('.snow-effect-placeholder');
    if (autoEnabled || placeholder) {
      autoStartIfNeeded();
    }
  });

  window.__SnowEffect_create = createSnowInline;

})();
JS;
        }

        public function ajax_save_settings(){
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message'=>'forbidden'), 403);
            }

            $ok = isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'snow_effect_ajax_nonce');
            if (!$ok) {
                wp_send_json_error(array('message'=>'bad_nonce'), 400);
            }

            $opts = $this->get_options();

            $raw_color = isset($_POST['color']) ? sanitize_text_field(wp_unslash($_POST['color'])) : $opts['color'];
            $color = $opts['color'];
            if (function_exists('sanitize_hex_color')) {
                $c = sanitize_hex_color($raw_color);
                if (!empty($c)) $color = $c;
            } else {
                if (is_string($raw_color) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $raw_color)) {
                    $color = $raw_color;
                }
            }

            $density = isset($_POST['density']) && is_numeric($_POST['density']) ? intval($_POST['density']) : $opts['density'];
            $max_radius = isset($_POST['max_radius']) && is_numeric($_POST['max_radius']) ? floatval($_POST['max_radius']) : $opts['max_radius'];
            $speed = isset($_POST['speed']) && is_numeric($_POST['speed']) ? floatval($_POST['speed']) : $opts['speed'];
            $rotation = isset($_POST['rotation']) && is_numeric($_POST['rotation']) ? floatval($_POST['rotation']) : $opts['rotation'];

            $opts['color'] = $color;
            $opts['density'] = (string)$density;
            $opts['max_radius'] = (string)$max_radius;
            $opts['speed'] = (string)$speed;
            $opts['rotation'] = (string)$rotation;
            $opts['enabled'] = isset($opts['enabled']) ? $opts['enabled'] : '1';

            update_option($this->opt_key, $opts);

            wp_send_json_success(array('message'=>'saved','options'=>$opts));
        }

        public function shortcode_render($atts){
            return '<div class="snow-effect-placeholder" aria-hidden="true"></div>';
        }

    } // end class VD_SnowEffectPlugin

} // end if (!class_exists)

if (!isset($GLOBALS['vd_snow_effect_plugin_instance']) && class_exists('VD_SnowEffectPlugin')) {
    $GLOBALS['vd_snow_effect_plugin_instance'] = new VD_SnowEffectPlugin();
}
