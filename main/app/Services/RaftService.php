<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RaftService
{
    // All 3 Raft nodes — matches docker-compose service names
    private array $nodes = [
        'http://raft_node_1:4001',
        'http://raft_node_2:4002',
        'http://raft_node_3:4003',
    ];

    // Find current leader by asking each node for its status
    // Cache the result for 1 second so we're not polling on every request
    public function getLeader(): ?string
    {
        return Cache::remember('raft_leader', 1, function () {
            foreach ($this->nodes as $node) {
                try {
                    $response = Http::timeout(1)->get("{$node}/status");

                    if ($response->successful()) {
                        $status = $response->json();

                        if ($status['isLeader']) {
                            return $status['nodeId'];
                        }
                    }
                } catch (\Exception $e) {
                    // Node is down, try next
                    continue;
                }
            }

            return null; // no leader found (election in progress)
        });
    }

    public function getLeaderStatus(): array
    {
        $statuses = [];

        foreach ($this->nodes as $node) {
            try {
                $response = Http::timeout(1)->get("{$node}/status");
                if ($response->successful()) {
                    $statuses[] = $response->json();
                }
            } catch (\Exception $e) {
                $statuses[] = ['nodeId' => $node, 'state' => 'unreachable'];
            }
        }

        return $statuses;
    }
}
