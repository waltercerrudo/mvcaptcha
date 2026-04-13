# mvCaptcha

**Versión 0.10.1** — Actualización y modernización asistida por [Claude Code](https://claude.ai/code) (Anthropic).

Un CAPTCHA visual para PHP que usa efectos de **parallax con el mouse**: el usuario mueve el cursor para ensamblar dos mitades de imagen y leer el texto oculto. Sin dependencias externas, sin JavaScript de terceros, sin cookies de rastreo.

---

## Demo

```
php -S localhost:8000
```

Abre `http://localhost:8000/` en el navegador.

---

## Requisitos

| Requisito | Versión mínima |
|-----------|---------------|
| PHP | 4.0.1+ |
| Extensión GD | con soporte TTF |
| Fuentes TTF | incluidas en `mvcaptcha/fonts/` |

---

## Instalación

1. Copia la carpeta `mvcaptcha/` en tu proyecto.
2. Incluye la clase:

```php
require '/ruta/a/mvcaptcha/MvCaptcha.php';
```

---

## Uso

### Modo página completa

```php
require __DIR__ . '/mvcaptcha/MvCaptcha.php';
(new MvCaptcha('./ok.php', './error.php'))->run();
```

`run()` maneja todo el ciclo: muestra el formulario en GET y valida + redirige en POST.

### Modo widget embebido

```php
require __DIR__ . '/mvcaptcha/MvCaptcha.php';

$captcha = new MvCaptcha('./ok.php', './error.php');

// En tu propio formulario:
echo $captcha->widget();   // devuelve el HTML del widget
```

Luego validá manualmente en el POST:

```php
if ($captcha->validate()) {
    // OK
} else {
    // Error
}
```

### Constructor

```php
new MvCaptcha(
    string $urlOk,      // redirección tras validación exitosa
    string $urlError,   // redirección tras validación fallida
    int    $length = 4  // longitud de la cadena CAPTCHA
)
```

---

## Cómo funciona

```
index.php
   └─ MvCaptcha::run()
         ├─ GET  → generate() → buildPage()
         │            ├─ genera string aleatorio (4 chars, case-sensitive)
         │            ├─ renderiza PNG con fuentes TTF aleatorias
         │            ├─ aplica máscaras PNG a cada mitad
         │            └─ devuelve las mitades como data URIs inline
         └─ POST → validate() → redirect(urlOk|urlError)
```

El parallax está implementado en JavaScript puro (`_MvParallax`). Las dos mitades de imagen se mueven en direcciones opuestas según la posición del mouse. El punto de solución (donde ambas mitades se alinean) es generado aleatoriamente en el servidor y embebido en el HTML — nunca se expone como texto.

Un clic en cualquier parte congela las imágenes en su posición actual para que el usuario pueda escribir el texto.

---

## Estructura de archivos

```
mvcaptcha/
├── MvCaptcha.php          # Clase principal (única dependencia)
├── fonts/                 # Fuentes TTF para la generación de imagen
└── img/
    ├── back/              # Fondos decorativos (back1.png … back6.png)
    └── mask/              # Máscaras PNG para recorte de mitades
```

---

## Versión 0.10.1 — Notas de actualización

Esta versión es una **refactorización y modernización completa** de la librería original, realizada con la asistencia de **Claude Code** (claude-sonnet-4-6, Anthropic).

### Cambios respecto a versiones anteriores

- **Arquitectura unificada:** toda la lógica consolidada en una sola clase (`MvCaptcha.php`), eliminando las dependencias entre múltiples archivos de clase separados.
- **API limpia:** interfaz pública reducida a tres métodos (`run`, `widget`, `validate`) con tipos estrictos PHP 8.
- **Imágenes inline:** las mitades del CAPTCHA se sirven como data URIs directamente en el HTML, eliminando el endpoint `img.php` y simplificando el despliegue.
- **Parallax reescrito:** el motor JS pasó de Parallax.js (librería externa) a `_MvParallax`, una implementación propia de ~80 líneas con curva de respuesta no-lineal (|Δ|^1.5) para mayor precisión cerca del punto de solución.
- **Estética modernizada:** UI dark con glassmorphism, paleta violeta/índigo, tipografía del sistema, animaciones CSS sutiles. Compatible con embebido en páginas de terceros mediante clases prefijadas `_mvc-`.
- **Sin dependencias externas:** cero librerías JS o CSS de terceros.

---

## Licencia

GNU General Public License v2.0 — ver [LICENSE](LICENSE).
