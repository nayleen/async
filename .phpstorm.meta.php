<?php

namespace PHPSTORM_META
{
    override(
        \DI\Container::get(0),
        map([
            '' => '@',
        ])
    );

    override(
        \Psr\Container\ContainerInterface::get(0),
        map([
            '' => '@',
        ])
    );
}
