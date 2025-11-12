<?php
class SimpleCache {
    private $cacheDir;
    private $cacheTime;

    public function __construct($cacheDir = 'cache/', $cacheTime = 300) { // 5 minutes default
        $this->cacheDir = $cacheDir;
        $this->cacheTime = $cacheTime;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key) {
        $file = $this->cacheDir . md5($key) . '.cache';

        if (file_exists($file) && (time() - filemtime($file)) < $this->cacheTime) {
            return unserialize(file_get_contents($file));
        }

        return false;
    }

    public function set($key, $data) {
        $file = $this->cacheDir . md5($key) . '.cache';
        file_put_contents($file, serialize($data));
    }

    public function delete($key) {
        $file = $this->cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
?>
