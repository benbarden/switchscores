<?php


namespace App\Services;

use App\Services\GameService;


class ServiceContainer
{
    const KEY_GAME_SERVICE = 'GameService';

    private $services = [];

    // *** Generic get/load/set *** //

    private function get($key)
    {
        if (!$this->exists($key)) {
            throw new \Exception('Failed to load service with key: '.$key);
        }

        return $this->services[$key];
    }

    private function set($key, $object)
    {
        $this->services[$key] = $object;
    }

    private function exists($key)
    {
        return array_key_exists($key, $this->services);
    }

    private function load($service)
    {
        if ($this->exists($service)) {
            return $this->get($service);
        }

        $serviceName = $this->getServiceName($service);

        $serviceClass = resolve($serviceName);

        $this->set($service, $serviceClass);

        return $this->get($service);
    }

    // ** Map names to classes ** //

    private function getServiceName($serviceKey)
    {
        switch ($serviceKey) {
            case self::KEY_GAME_SERVICE:
                $serviceName = 'Services\GameService';
                break;
            default:
                throw new \Exception('Failed to load service class: '.$serviceKey);
                break;
        }
        return $serviceName;
    }

    // ** Get specific classes ** //

    /**
     * @return GameService
     */
    public function getGameService()
    {
        $serviceKey = self::KEY_GAME_SERVICE;

        $serviceObject = $this->load($serviceKey);

        return $serviceObject;
    }
}