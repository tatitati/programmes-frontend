<?php
namespace App\ExternalApi\Isite;

class GuidQuery implements QueryInterface
{
    /** @var (int|string)[] */
    private $parameters = [];

    public function setContentId(string $guid): self
    {
        $this->parameters['contentId'] = $guid;
        return $this;
    }

    public function setDepth(int $depth): self
    {
        $this->parameters['depth'] = $depth;
        return $this;
    }

    public function getPath(): string
    {
        return '/content?' . http_build_query($this->parameters);
    }

    public function setPreview(bool $preview): self
    {
        $this->parameters['preview'] = $preview ? 'true' : 'false';
        return $this;
    }

    public function setAllowNonLive(bool $allow): self
    {
        $this->parameters['allowNonLive'] = $allow ? 'true' : 'false';
        return $this;
    }
}
