<?php
declare(strict_types = 1);

namespace App\Cosmos;

class Dials
{
    private $dials;

    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function get(string $key)
    {
        if (null === $this->dials) {
            $this->loadDials();
        }

        if (property_exists($this->dials, $key)) {
            return $this->dials->{$key};
        }
        return null;
    }

    private function loadDials(): void
    {
        // Default object to empty
        $this->dials = (object) [];

        // Try read the file in
        if (file_exists($this->path)) {
            $file = json_decode(file_get_contents($this->path));
            if ($file !== null) {
                $this->dials = $file;
            }
        }
    }
}
