<?php
//clearstatcache();
$language = "hr_HR.UTF-8";
putenv("LANGUAGE=" . $language);
setlocale(LC_ALL, $language);

$domain = "messages"; // which language file to use
bindtextdomain($domain, "Locale");
bind_textdomain_codeset($domain, 'UTF-8');

textdomain($domain);

echo _("HELLO_WORLD");