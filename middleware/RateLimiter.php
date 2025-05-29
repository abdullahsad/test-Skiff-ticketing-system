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

    /**
     * Handles rate limiting for a given key.
     *
     * This method checks the number of requests made using a specific key
     * and enforces a limit on the number of requests allowed within a 
     * specified time frame. If the limit is exceeded, it sends a 429 
     * Too Many Requests response and terminates the script.
     *
     * @param string $key The unique identifier for tracking requests.
     * 
     * @return bool Returns true if the request is allowed, otherwise terminates the script.
     */
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
