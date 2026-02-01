<?php

namespace App\Services;

use Illuminate\Process\Pipe;
use Illuminate\Support\Facades\Process;
use Illuminate\Http\StreamedEvent;
use Illuminate\Support\Facades\Cache;

class Stream
{
    public static function event(string $cache)
    {
        return response()->eventStream(function () use ($cache) {
            if (!auth()->check()) {
                yield new StreamedEvent(
                    event: 'unauthorized',
                    data: json_encode([
                        'message' => 'Authentication required.'
                    ])
                );
                return;
            }
            $stream = [];
            Cache::lock($cache . '_lock', 2)->block(1, function () 
                use ($cache, &$stream) {
                $stream = Cache::get($cache, []);
                Cache::forget($cache);
            });
            foreach ($stream as $response) {
                yield new StreamedEvent(
                    event: $response['event'],
                    data: $response['data']
                );
            }
            /*
            yield new StreamedEvent(
                event: 'ping',
                data: json_encode([
                    'time' => now()->toIso8601String()
                ])
            );
            */
        });
    }

    public static function process(array $commands): void
    {
        Process::pipe(function (Pipe $pipe) use ($commands) {
            $last = array_key_last($commands);
            foreach ($commands as $i => $command) {
                if ($i === $last) {
                    $pipe->as('final')->command($command);
                } else {
                    $pipe->command($command);
                }
            }
        }, function (string $type, string $output, string $key) {
            if ($type === 'out' && $key === 'final') {
                echo $output;
                ob_flush();
                flush();
            }
        });
    }

    public static function store(string $cache, string $event, array $data)
    {
        Cache::lock($cache . '_lock', 2)->block(1, function ()
            use ($cache, $event, $data) {
            $stream = Cache::get($cache, []);
            $stream[] = [
                'event' => $event,
                'data' => json_encode($data)
            ];
            Cache::put($cache, $stream, now()->addMinutes(15));
        });
    }
}
