<?php

namespace App\Services;

class DockerClient
{
    private string $socketPath;

    public function __construct(string $socketPath = '/var/run/docker.sock')
    {
        $this->socketPath = $socketPath;
    }

    /**
     * Perform a raw HTTP request against the Docker Engine API over the UNIX socket.
     *
     * @param string $method
     * @param string $path
     * @return array{status:int,headers:array<string,string>,body:string}|null
     */
    public function request(string $method, string $path): ?array
    {
        $fp = @stream_socket_client('unix://' . $this->socketPath, $errno, $errstr, 2);

        if (! $fp) {
            return null;
        }

        $request = sprintf("%s %s HTTP/1.1\r\nHost: localhost\r\nConnection: Close\r\n\r\n", strtoupper($method), $path);
        fwrite($fp, $request);

        $response = stream_get_contents($fp);
        fclose($fp);

        if ($response === false) {
            return null;
        }

        [$rawHeaders, $body] = explode("\r\n\r\n", $response, 2);
        $headerLines = explode("\r\n", $rawHeaders);
        $statusLine = array_shift($headerLines);
        preg_match('#HTTP/\d\.\d\s+(\d{3})#', $statusLine, $matches);
        $status = isset($matches[1]) ? (int) $matches[1] : 500;

        $headers = [];
        foreach ($headerLines as $line) {
            if (strpos($line, ':') !== false) {
                [$name, $value] = explode(':', $line, 2);
                $headers[trim($name)] = trim($value);
            }
        }

        return [
            'status' => $status,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    /**
     * List running containers with basic metadata.
     *
     * @return array<int,array<string,mixed>>
     */
    public function listContainers(): array
    {
        $response = $this->request('GET', '/containers/json?all=1');

        if ($response === null || $response['status'] !== 200) {
            return [];
        }

        $data = json_decode($response['body'], true);

        return is_array($data) ? $data : [];
    }

    /**
     * Get one-shot stats for a specific container.
     *
     * @param string $id
     * @return array<string,mixed>|null
     */
    public function containerStats(string $id): ?array
    {
        $response = $this->request('GET', sprintf('/containers/%s/stats?stream=false', $id));

        if ($response === null || $response['status'] !== 200) {
            return null;
        }

        $data = json_decode($response['body'], true);

        return is_array($data) ? $data : null;
    }

    /**
     * List containers with derived stats (CPU, memory, network).
     *
     * @return array<int,array<string,mixed>>
     */
    public function listContainersWithStats(): array
    {
        $containers = $this->listContainers();

        $result = [];

        foreach ($containers as $container) {
            $id = $container['Id'] ?? null;
            if (! $id) {
                continue;
            }

            $stats = $this->containerStats($id);

            $cpuPercent = null;
            $memUsage = null;
            $memLimit = null;
            $memPercent = null;
            $netInput = null;
            $netOutput = null;

            if ($stats) {
                // CPU percent calculation from docker docs
                $cpuDelta = ($stats['cpu_stats']['cpu_usage']['total_usage'] ?? 0) - ($stats['precpu_stats']['cpu_usage']['total_usage'] ?? 0);
                $systemDelta = ($stats['cpu_stats']['system_cpu_usage'] ?? 0) - ($stats['precpu_stats']['system_cpu_usage'] ?? 0);
                $onlineCpus = $stats['cpu_stats']['online_cpus'] ?? ($stats['cpu_stats']['cpu_usage']['percpu_usage'] ? count($stats['cpu_stats']['cpu_usage']['percpu_usage']) : 1);

                if ($cpuDelta > 0 && $systemDelta > 0 && $onlineCpus > 0) {
                    $cpuPercent = ($cpuDelta / $systemDelta) * $onlineCpus * 100.0;
                }

                $memUsage = $stats['memory_stats']['usage'] ?? null;
                $memLimit = $stats['memory_stats']['limit'] ?? null;
                if ($memUsage !== null && $memLimit) {
                    $memPercent = ($memUsage / $memLimit) * 100.0;
                }

                $netRx = 0;
                $netTx = 0;
                if (! empty($stats['networks']) && is_array($stats['networks'])) {
                    foreach ($stats['networks'] as $network) {
                        $netRx += $network['rx_bytes'] ?? 0;
                        $netTx += $network['tx_bytes'] ?? 0;
                    }
                }

                $netInput = $netRx;
                $netOutput = $netTx;
            }

            $result[] = [
                'id' => $id,
                'names' => $container['Names'] ?? [],
                'image' => $container['Image'] ?? null,
                'state' => $container['State'] ?? null,
                'status' => $container['Status'] ?? null,
                'labels' => $container['Labels'] ?? [],
                'created' => $container['Created'] ?? null,
                'cpu_percent' => $cpuPercent,
                'mem_usage' => $memUsage,
                'mem_limit' => $memLimit,
                'mem_percent' => $memPercent,
                'net_input' => $netInput,
                'net_output' => $netOutput,
            ];
        }

        return $result;
    }

    /**
     * Build a simple summary of containers by state.
     *
     * @return array<string,mixed>
     */
    public function summary(): array
    {
        $containers = $this->listContainersWithStats();

        $total = count($containers);
        $running = 0;
        $stopped = 0;
        $totalCpu = 0.0;
        $totalMem = 0;
        $totalMemLimit = 0;
        $totalNetIn = 0;
        $totalNetOut = 0;

        foreach ($containers as $container) {
            $state = $container['state'] ?? '';
            if ($state === 'running') {
                $running++;
            } else {
                $stopped++;
            }

            $totalCpu += $container['cpu_percent'] ?? 0;
            $totalMem += $container['mem_usage'] ?? 0;
            $totalMemLimit += $container['mem_limit'] ?? 0;
            $totalNetIn += $container['net_input'] ?? 0;
            $totalNetOut += $container['net_output'] ?? 0;
        }

        $avgCpu = $total > 0 ? $totalCpu / $total : null;
        $memPercent = ($totalMemLimit > 0) ? ($totalMem / $totalMemLimit) * 100.0 : null;

        return [
            'total' => $total,
            'running' => $running,
            'stopped' => $stopped,
            'avg_cpu_percent' => $avgCpu,
            'total_mem_usage' => $totalMem,
            'total_mem_limit' => $totalMemLimit,
            'total_mem_percent' => $memPercent,
            'total_net_input' => $totalNetIn,
            'total_net_output' => $totalNetOut,
        ];
    }
}


