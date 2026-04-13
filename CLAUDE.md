# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

mvCaptcha is a PHP CAPTCHA library that uses GD image generation with parallax mouse-movement effects to display split CAPTCHA images. The user must move the mouse to visually assemble two image halves to read the text.

**Requirements:** PHP >= 4.0.1, GD Library with TTF support, fonts in `fonts/` directory.

## Running Locally

This is a plain PHP project with no build step. Serve it with PHP's built-in server from the repo root:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/` to see the captcha demo.

## Architecture

The flow is:

1. **`index.php`** — Entry point. Starts session, sets hotlink protection flag, instantiates `mvcaptcha`, calls `$mvcaptcha->run()`.
2. **`class/mvcaptcha/mvcaptcha.class.php`** — Main orchestrator class. On first load (`verformulario()` returns true), calls `vermvcaptcha()` which: instantiates `captcha`, saves the generated string and filename to `$_SESSION`, then returns the HTML form. On POST submission, calls `proceder()` which compares `$_POST['captchastring']` against `$_SESSION['CAPTCHAString']` and redirects to `ok.php` or `error.php`.
3. **`class/captcha-2.0.1/captcha.class.php`** — Generates a random 4-character string (mixed case alpha), renders it to a PNG using random TTF fonts from `fonts/`, and saves the file to `sys_get_temp_dir()` with a random hex filename stored in `$_SESSION['FILENAME']`.
4. **`img.php`** — Serves the captcha image. Called twice by the HTML (`?s=1&l=a` and `?s=1&l=b`): loads the temp PNG, applies a PNG mask from `img/mask/mask{s}{l}.png` via `imageMask`, crops to a 300×75 slice at different horizontal offsets to split the image into two halves.
5. **`class/imagemask/class.imagemask.php`** — Applies a PNG mask to an image using alpha compositing.
6. **`js/parallax.js`** — Parallax.js library; the two image halves move in opposite directions on mouse movement, snapping to still on click.

The captcha string is stored server-side in `$_SESSION['CAPTCHAString']`; comparison is case-sensitive.
