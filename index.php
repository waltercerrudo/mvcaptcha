<?php
require __DIR__ . '/mvcaptcha/MvCaptcha.php';
(new MvCaptcha('./ok.php', './error.php'))->run();
