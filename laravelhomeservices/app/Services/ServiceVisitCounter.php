<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;
use Illuminate\Support\Facades\Redis;

final class ServiceVisitCounter
{
    private const PENDING_SERVICE_IDS_KEY = 'service-visits:pending-service-ids';

    public function increment(Service $service): int
    {
        $key = $this->counterKey($service->getKey());
        $count = (int) Redis::incr($key);
        Redis::sadd(self::PENDING_SERVICE_IDS_KEY, (string) $service->getKey());

        return $count;
    }

    public function total(Service $service): int
    {
        return (int) $service->views + (int) Redis::get($this->counterKey($service->getKey()));
    }

    /**
     * @return array<int, int>
     */
    public function pendingServiceIds(): array
    {
        return array_map('intval', Redis::smembers(self::PENDING_SERVICE_IDS_KEY));
    }

    public function claim(int $serviceId): int
    {
        return (int) Redis::getset($this->counterKey($serviceId), '0');
    }

    public function restore(int $serviceId, int $visits): void
    {
        if ($visits <= 0) {
            return;
        }

        Redis::incrby($this->counterKey($serviceId), $visits);
        Redis::sadd(self::PENDING_SERVICE_IDS_KEY, (string) $serviceId);
    }

    public function forgetWhenEmpty(int $serviceId): void
    {
        Redis::eval(
            "if redis.call('GET', KEYS[1]) == '0' then return redis.call('SREM', KEYS[2], ARGV[1]) end return 0",
            2,
            $this->counterKey($serviceId),
            self::PENDING_SERVICE_IDS_KEY,
            (string) $serviceId
        );
    }

    private function counterKey(int $serviceId): string
    {
        return 'service-visits:service:' . $serviceId;
    }
}
