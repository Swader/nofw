<?php

return [
    ['GET', '/', 'Standard\Controllers\HomeController'],
    ['GET', '/extra', ['Standard\Controllers\ExtraController', 'indexAction']]
];