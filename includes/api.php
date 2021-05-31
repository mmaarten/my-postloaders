<?php

function my_register_postloader($loader)
{
    \My\Postloaders\Postloaders::getInstance()->register($loader);
}

function my_postloader($loader_id)
{
    \My\Postloaders\Postloaders::getInstance()->render($loader_id);
}
