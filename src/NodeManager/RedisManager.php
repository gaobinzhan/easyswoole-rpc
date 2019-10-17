<?php

namespace EasySwoole\Rpc\NodeManager;

use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\ServiceNode;
use EasySwoole\Utility\Random;

class RedisManager implements NodeManagerInterface
{
    protected $redisKey;

    protected $pool;

    function __construct(RedisPool $pool, string $hashKey = '__rpcNodes')
    {
        $this->redisKey = $hashKey;
        $this->pool = $pool;
    }

    function getServiceNodes(string $serviceName, ?string $version = null): array
    {
        $redis = $this->pool->getObj(15);
        try {
            $nodes = $redis->hGetAll($this->redisKey . md5($serviceName));
            $nodes = $nodes ?: [];
            $ret = [];
            foreach ($nodes as $nodeId => $node) {
                /**
                 * @var  $nodeId
                 * @var  ServiceNode $node
                 */
                if (time() - $node->getLastHeartBeat() > 30) {
                    $this->deleteServiceNode($node);
                }
                if ($version && $version != $node->getServiceVersion()) {
                    continue;
                }
                $ret[$nodeId] = $node;
            }
            return $ret;
        } catch (\Throwable $throwable) {
            //如果该redis断线则销毁
            $this->pool->unsetObj($redis);
        } finally {
            $this->pool->recycleObj($redis);
        }
        return [];
    }

    function getServiceNode(string $serviceName, ?string $version = null): ?ServiceNode
    {
        $list = $this->getServiceNodes($serviceName, $version);
        if (empty($list)) {
            return null;
        }
        return Random::arrayRandOne($list);
    }

    function deleteServiceNode(ServiceNode $serviceNode): bool
    {
        /** @var \Redis $redis */
        $redis = PoolManager::getInstance()->getPool('__rpcRedis')->getObj(15);
        try {
            $redis->hDel($this->redisKey . md5($serviceNode->getServiceName()), $serviceNode->getNodeId());
            return true;
        } catch (\Throwable $throwable) {
            PoolManager::getInstance()->getPool('__rpcRedis')->unsetObj($redis);
        } finally {
            PoolManager::getInstance()->getPool('__rpcRedis')->recycleObj($redis);
        }
        return false;
    }

    function serviceNodeHeartBeat(ServiceNode $serviceNode): bool
    {
        if (empty($serviceNode->getLastHeartBeat())) {
            $serviceNode->setLastHeartBeat(time());
        }
        /** @var \Redis $redis */
        $redis = PoolManager::getInstance()->getPool('__rpcRedis')->getObj(15);
        try {
            $redis->hSet($this->redisKey . md5($serviceNode->getServiceName()), $serviceNode->getNodeId(), $serviceNode);
            return true;
        } catch (\Throwable $throwable) {
            //如果该redis断线则销毁
            PoolManager::getInstance()->getPool('__rpcRedis')->unsetObj($redis);
        } finally {
            //这边需要测试一个对象被unset后是否还能被回收
            PoolManager::getInstance()->getPool('__rpcRedis')->recycleObj($redis);
        }
        return false;
    }
}
