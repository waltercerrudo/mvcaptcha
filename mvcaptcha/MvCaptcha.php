<?php
/**
 * MvCaptcha — CAPTCHA de parallax con mouse.
 *
 * Uso mínimo:
 *   require 'mvcaptcha/MvCaptcha.php';
 *   (new MvCaptcha('/ok.php', '/error.php'))->run();
 *
 * Uso embebido en formulario propio:
 *   echo (new MvCaptcha('/ok.php', '/error.php'))->widget();
 *   // En el handler POST:
 *   if ((new MvCaptcha('/ok.php', '/error.php'))->validate()) { ... }
 */
class MvCaptcha
{
    // ── Claves de sesión ──────────────────────────────────────────────────────
    private const SK_STRING = '_mvc_string';
    private const SK_FILE   = '_mvc_file';
    private const SK_MASK   = '_mvc_mask';

    // ── Rutas internas ────────────────────────────────────────────────────────
    private string $fontDir;
    private string $maskDir;
    private string $backDir;

    /**
     * @param string $urlOk    Destino tras validación exitosa.
     * @param string $urlError Destino tras validación fallida.
     * @param int    $length   Caracteres del captcha (por defecto 4).
     */
    public function __construct(
        private readonly string $urlOk,
        private readonly string $urlError,
        private readonly int    $length = 4
    ) {
        $this->fontDir = __DIR__ . '/fonts/';
        $this->maskDir = __DIR__ . '/img/mask/';
        $this->backDir = __DIR__ . '/img/back/';

        if (!function_exists('imagettftext')) {
            throw new RuntimeException('MvCaptcha requiere GD con soporte TTF.');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // API pública
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Modo página completa.
     * GET  → genera captcha y envía HTML completo.
     * POST → valida y redirige a urlOk / urlError.
     */
    public function run(): void
    {
        $this->sessionStart();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->redirect($this->validate() ? $this->urlOk : $this->urlError);
        }

        echo $this->buildPage();
    }

    /**
     * Devuelve el fragmento HTML del widget (para embeber en página propia).
     * Incluye estilos y JS inline; listo para insertar en cualquier <form>.
     * El campo oculto del action no se genera aquí: el <form> lo provee el host.
     */
    public function widget(): string
    {
        $this->sessionStart();
        $this->generate();
        return $this->buildWidget();
    }

    /**
     * Valida la respuesta del usuario contra el valor de sesión.
     * Limpia el captcha de sesión tras la verificación.
     *
     * @return bool true si el texto ingresado es correcto.
     */
    public function validate(): bool
    {
        $this->sessionStart();
        $input   = $_POST['_mvc_captcha'] ?? '';
        $correct = $_SESSION[self::SK_STRING] ?? '';
        $this->cleanup();
        return $input === $correct && $correct !== '';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Generación de captcha
    // ══════════════════════════════════════════════════════════════════════════

    private function generate(): void
    {
        $this->cleanup();
        $_SESSION[self::SK_STRING] = $this->generateString();
        $_SESSION[self::SK_FILE]   = bin2hex(random_bytes(8));
        $_SESSION[self::SK_MASK]   = random_int(1, 3);
        $this->renderPng();
    }

    private function generateString(): string
    {
        $pool = array_merge(range('A', 'Z'), range('a', 'z'));
        $out  = '';
        for ($i = 0; $i < $this->length; $i++) {
            $out .= $pool[random_int(0, count($pool) - 1)];
        }
        return $out;
    }

    /** Genera la imagen PNG base y la guarda en temp. */
    private function renderPng(): void
    {
        $fonts  = $this->listFonts();
        $width  = $this->length * 45 + 200;
        $height = 75;
        $img    = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);

        for ($i = 0; $i < $this->length; $i++) {
            $color = imagecolorallocate($img,
                random_int(60, 140), random_int(60, 140), random_int(60, 140));
            imagettftext($img, 35, random_int(-15, 15),
                $i * 45 + 100, random_int(55, 68),
                $color, $fonts[array_rand($fonts)], $_SESSION[self::SK_STRING][$i]);
        }

        $path = $this->tempPath();
        if (!imagepng($img, $path)) {
            throw new RuntimeException('No se pudo guardar la imagen captcha en ' . $path);
        }
        imagedestroy($img);
    }

    private function listFonts(): array
    {
        $files = array_merge(
            glob($this->fontDir . '*.ttf')         ?: [],
            glob($this->fontDir . 'fuentes/*.ttf') ?: []
        );
        if (empty($files)) {
            throw new RuntimeException('No se encontraron fuentes TTF en ' . $this->fontDir);
        }
        return $files;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Composición de imagen (máscara + recorte → data URI)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Aplica la máscara PNG a la imagen captcha, recorta la mitad
     * indicada y devuelve una cadena data:image/png;base64,...
     *
     * @param 'a'|'b' $half
     */
    private function buildDataUri(string $half): string
    {
        $maskNum  = $_SESSION[self::SK_MASK];
        $srcPath  = $this->tempPath();
        $maskPath = $this->maskDir . 'mask' . $maskNum . $half . '.png';

        $src  = imagecreatefrompng($srcPath);
        $mask = imagecreatefrompng($maskPath);

        $sw = imagesx($src);
        $sh = imagesy($src);

        // Componer imagen con alpha basado en brillo de la máscara
        $out = imagecreatetruecolor($sw, $sh);
        imagealphablending($out, false);
        imagesavealpha($out, true);

        for ($x = 0; $x < $sw; $x++) {
            for ($y = 0; $y < $sh; $y++) {
                $sc = imagecolorat($src, $x, $y);
                $mc = imagecolorat($mask, $x, $y);

                $mr    = ($mc >> 16) & 0xFF;
                $mg    = ($mc >> 8)  & 0xFF;
                $mb    = $mc & 0xFF;
                $alpha = (int) round(($mr + $mg + $mb) / 6);
                if ($alpha > 1) $alpha--;

                imagesetpixel($out, $x, $y, imagecolorallocatealpha(
                    $out,
                    ($sc >> 16) & 0xFF,
                    ($sc >> 8)  & 0xFF,
                    $sc & 0xFF,
                    $alpha
                ));
            }
        }

        imagedestroy($src);
        imagedestroy($mask);

        // Recortar franja 300×75
        $crop   = imagecreatetruecolor(300, 75);
        imagealphablending($crop, false);
        imagesavealpha($crop, true);

        $offset = ($half === 'a') ? random_int(0, 30) : random_int(40, 60);
        imagecopy($crop, $out, 0, 0, $offset, 0, 300, 75);
        imagedestroy($out);

        ob_start();
        imagepng($crop);
        $raw = ob_get_clean();
        imagedestroy($crop);

        return 'data:image/png;base64,' . base64_encode($raw);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Construcción de HTML
    // ══════════════════════════════════════════════════════════════════════════

    private function buildPage(): string
    {
        $this->generate();
        $widget = $this->buildWidget();

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>mvCaptcha</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                html { height: 100%; }
                body {
                    min-height: 100%;
                    background: #080810;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 2rem;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
                }
                body::before {
                    content: '';
                    position: fixed;
                    inset: 0;
                    background: radial-gradient(ellipse 90% 55% at 50% -5%,
                        rgba(108,99,255,.18) 0%, transparent 65%);
                    pointer-events: none;
                }
                form { display: contents; }
            </style>
        </head>
        <body>
            <form method="post">
                {$widget}
            </form>
        </body>
        </html>
        HTML;
    }

    /** HTML puro del widget, sin <form> ni <button>. */
    private function buildWidget(): string
    {
        $imgA    = $this->buildDataUri('a');
        $imgB    = $this->buildDataUri('b');
        $back    = random_int(1, 6);
        $backImg = $this->backToDataUri($back);
        $reloadUrl = htmlspecialchars($_SERVER['REQUEST_URI'] ?? './', ENT_QUOTES);

        // Punto de solución aleatorio: posición normalizada del mouse (-1..1)
        // donde las dos mitades quedan alineadas. Evita que el captcha
        // aparezca resuelto en reposo (mouse al centro = nx=0, ny=0).
        $solveX = random_int(-40, 40) / 100.0;   // -0.40 … 0.40
        $solveY = random_int(-25, 25) / 100.0;   // -0.25 … 0.25

        $css = $this->inlineCSS();
        $js  = $this->inlineJS();

        return <<<HTML
        <style>{$css}</style>

        <div class="_mvc-widget">
            <p class="_mvc-hint">
                Mueve el mouse para ensamblar el texto.<br>
                Clic para fijar &mdash;
                <a href="{$reloadUrl}">&#x21BA; nueva imagen</a>
            </p>

            <div id="_mvc-scene" class="_mvc-scene"
                 data-solve-x="{$solveX}"
                 data-solve-y="{$solveY}"
                 style="background-image:url({$backImg})">
                <div data-depth-x="0.8" data-depth-y="-0.4">
                    <img src="{$imgA}" alt="" width="300" height="75">
                </div>
                <div data-depth-x="-0.8" data-depth-y="0.4">
                    <img src="{$imgB}" alt="" width="300" height="75">
                </div>
            </div>

            <label class="_mvc-label" for="_mvc-input">
                Texto (sensible a may&uacute;sculas):
            </label>
            <input id="_mvc-input" class="_mvc-input"
                   type="text" name="_mvc_captcha"
                   maxlength="20" spellcheck="false"
                   autocomplete="off" required>
        </div>

        <script>{$js}</script>
        <script>
        (function(){
            var scene = document.getElementById('_mvc-scene');
            if (scene) {
                var p = new _MvParallax(scene);
                document.addEventListener('click', function(){ p.disable(); }, {once:true});
            }
        })();
        </script>
        HTML;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CSS y JS inline
    // ══════════════════════════════════════════════════════════════════════════

    private function inlineCSS(): string
    {
        return <<<'CSS'
        ._mvc-widget {
            display: inline-block;
            font-family: system-ui, "Segoe UI", sans-serif;
            font-size: 14px;
            color: #444;
            text-align: center;
        }
        ._mvc-hint { margin: 0 0 .6rem; line-height: 1.5; }
        ._mvc-hint a { color: #3354aa; }
        ._mvc-scene {
            position: relative;
            width: 300px;
            height: 75px;
            margin: 0 auto .8rem;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            cursor: crosshair;
        }
        ._mvc-scene > div {
            position: absolute;
            top: 0; left: 0;
            will-change: transform;
        }
        ._mvc-scene img { display: block; }
        ._mvc-label { display: block; margin-bottom: .3rem; }
        ._mvc-input {
            padding: 6px 10px;
            font-size: 1rem;
            border: 1px solid #bbb;
            border-radius: 4px;
            width: 160px;
            text-align: center;
            letter-spacing: .1em;
        }
        ._mvc-input:focus {
            outline: 2px solid #4a90e2;
            border-color: #4a90e2;
        }
        CSS;
    }

    private function inlineJS(): string
    {
        return <<<'JS'
        (function(global){
            'use strict';

            // Fuerza máxima de desplazamiento en px para cada eje
            var SX = 48, SY = 22;
            // Factor de suavizado (easing hacia el target cada frame)
            var EASE = 0.10;

            /**
             * Curva de respuesta no-lineal: |Δ|^1.5 * signo(Δ)
             *
             * Efecto: movimientos pequeños cerca del punto de solución
             * producen desplazamientos muy finos (control preciso);
             * movimientos amplios generan barrido rápido.
             *
             *   Δ=0.05 → 0.011   (muy suave cerca del centro)
             *   Δ=0.30 → 0.164
             *   Δ=0.70 → 0.586
             *   Δ=1.00 → 1.000
             */
            function curve(v) {
                return Math.sign(v) * Math.pow(Math.abs(v), 1.5);
            }

            function MvParallax(scene) {
                this._scene   = scene;
                this._layers  = Array.from(scene.querySelectorAll('[data-depth-x],[data-depth-y]'));
                // Punto de solución embebido por PHP (posición normalizada del mouse
                // en la que ambas mitades quedan alineadas)
                this._solveX  = parseFloat(scene.dataset.solveX || 0);
                this._solveY  = parseFloat(scene.dataset.solveY || 0);
                this._active  = true;
                this._cur     = this._layers.map(function(){ return {x:0,y:0}; });
                this._tgt     = this._layers.map(function(){ return {x:0,y:0}; });
                this._raf     = null;
                this._onMove  = this._onMove.bind(this);
                this._tick    = this._tick.bind(this);
                window.addEventListener('mousemove', this._onMove);
                this._raf = requestAnimationFrame(this._tick);
            }

            MvParallax.prototype._onMove = function(e) {
                if (!this._active) return;
                // Posición normalizada: -1 (izq/arriba) … +1 (der/abajo)
                var nx = (e.clientX / window.innerWidth)  * 2 - 1;
                var ny = (e.clientY / window.innerHeight) * 2 - 1;
                // Desplazamiento relativo al punto de solución
                var rx = curve(nx - this._solveX);
                var ry = curve(ny - this._solveY);
                var self = this;
                this._layers.forEach(function(layer, i) {
                    var dx = parseFloat(layer.dataset.depthX || 0);
                    var dy = parseFloat(layer.dataset.depthY || 0);
                    self._tgt[i].x = rx * dx * SX;
                    self._tgt[i].y = ry * dy * SY;
                });
            };

            MvParallax.prototype._tick = function() {
                var self = this;
                this._layers.forEach(function(layer, i) {
                    var c = self._cur[i], t = self._tgt[i];
                    c.x += (t.x - c.x) * EASE;
                    c.y += (t.y - c.y) * EASE;
                    layer.style.transform = 'translate('+c.x.toFixed(2)+'px,'+c.y.toFixed(2)+'px)';
                });
                this._raf = requestAnimationFrame(this._tick);
            };

            /**
             * Congela las imágenes en su posición actual.
             * No snapea a cero — el usuario puede leer el texto donde quedó.
             */
            MvParallax.prototype.disable = function() {
                this._active = false;
                window.removeEventListener('mousemove', this._onMove);
                cancelAnimationFrame(this._raf);
                this._raf = null;
                // Fija el transform exactamente donde estaba (sin más easing)
                var self = this;
                this._layers.forEach(function(layer, i) {
                    var c = self._cur[i];
                    layer.style.transform = 'translate('+c.x.toFixed(2)+'px,'+c.y.toFixed(2)+'px)';
                });
            };

            MvParallax.prototype.enable = function() {
                if (this._active) return;
                this._active = true;
                window.addEventListener('mousemove', this._onMove);
                this._raf = requestAnimationFrame(this._tick);
            };

            MvParallax.prototype.destroy = function() {
                this.disable();
            };

            global._MvParallax = MvParallax;
        }(window));
        JS;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Utilidades
    // ══════════════════════════════════════════════════════════════════════════

    private function backToDataUri(int $n): string
    {
        $path = $this->backDir . 'back' . $n . '.png';
        if (!file_exists($path)) return '';
        return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
    }

    private function tempPath(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . ($_SESSION[self::SK_FILE] ?? '') . '.png';
    }

    private function cleanup(): void
    {
        $path = $this->tempPath();
        if ($path !== DIRECTORY_SEPARATOR . '.png' && file_exists($path)) {
            unlink($path);
        }
        unset(
            $_SESSION[self::SK_STRING],
            $_SESSION[self::SK_FILE],
            $_SESSION[self::SK_MASK]
        );
    }

    private function sessionStart(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
