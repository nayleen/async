<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cluster\Cluster;
use Amp\Socket\ServerSocketFactory;
use DI;
use Psr\Container\ContainerInterface;

return [
    // cluster config
    'async.cluster.enabled' => DI\factory(static function (string $enableClustering): bool {
        if (!class_exists(Cluster::class)) {
            return false;
        }

        return (bool) $enableClustering;
    })->parameter('enableClustering', DI\env('ASYNC_CLUSTER', '1')),

    'async.cluster.is_worker' => static function (ContainerInterface $container): bool {
        $clusterSupport = (bool) $container->get('async.cluster.enabled');

        if (!$clusterSupport) {
            return false;
        }

        return Cluster::isWorker();
    },

    // cluster services
    ServerSocketFactory::class => DI\decorate(static function (ServerSocketFactory $factory, bool $clusterSupport): ServerSocketFactory {
        return $clusterSupport ? Cluster::getServerSocketFactory() : $factory;
    })->parameter('clusterSupport', DI\get('async.cluster.enabled')),
];
