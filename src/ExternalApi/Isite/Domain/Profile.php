<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Domain;

use App\ExternalApi\Isite\DataNotFetchedException;

class Profile
{
    //Image
    //Title
    //Slug(slugified title)
    //Key (Mutated Guid)
    //Guid

    /** @var Profile[]|null */
    private $children;

    /** @var string */
    private $fileId;

    /** @var string */
    private $key;

    /** @var string */
    private $title;

    /** @var string */
    private $type;

    public function __construct(string $title, string $key, string $fileId, string $type)
    {
        $this->title = $title;
        $this->key = $key;
        $this->fileId = $fileId;
        $this->type = $type;
        if ($this->type !== 'group') {
            $this->setChildren([]);
        }
    }

    /**
     * @return Profile[]
     * @throws DataNotFetchedException
     */
    public function getChildren(): array
    {
        if ($this->children === null) {
            throw new DataNotFetchedException('Profile children have not been queried for yet.');
        }

        return $this->children;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getSlug()
    {
        $text = str_replace(['\'', '"'], '', $this->title);
        // string replace from http://stackoverflow.com/questions/2103797/url-friendly-username-in-php
        // will turn accented characters into plain english
        return strtolower(
            trim(
                preg_replace(
                    '~[^0-9a-z]+~i',
                    '-',
                    html_entity_decode(
                        preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($text, ENT_QUOTES, 'UTF-8')),
                        ENT_QUOTES,
                        'UTF-8'
                    )
                ),
                '-'
            )
        );
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param Profile[] $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
    }
}
