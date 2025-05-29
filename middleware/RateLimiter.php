<?php
class RateLimiter {
    private $max_request;
    private $time_in_sec;
    private $redis;

    public function __construct($redis, $max_request = 5, $time_in_sec = 60) {
        $this->redis = $redis;
        $this->max_request = $max_request;
        $this->time_in_sec = $time_in_sec;
    }

    public function handle($key) {
        $current = $this->redis->get($key);
        if ($current === false) {
            $this->redis->set($key, 1, $this->time_in_sec);
            return true;
        }

        if ($current >= $this->max_request) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Too many requests. Please try again later.']);
            exit;
        }

        $this->redis->incr($key);
        return true;
    }
}
