<?php
declare(strict_types = 1);

namespace App\Twig;

use Twig_Extension;
use Twig_Function;

class FavouriteButtonExtension extends Twig_Extension
{
    private $buttons = [];

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new Twig_Function('add_button', [$this, 'addButton']),
            new Twig_Function('get_buttons', [$this, 'getButtons']),
        ];
    }

    public function addButton(string $elementId, string $id, string $type, string $contextId, ?string $profile = null, ?string $product = null)
    {
        $button = [
            'element_id' => $elementId,
            'id' => $id,
            'type' => $type,
            'context_id' => $contextId,
        ];
        if ($profile) {
            $button['profile'] = $profile;
        }
        if ($product) {
            $button['product'] = $product;
        }
        $this->buttons[] = $button;
    }

    public function getButtons(): ?array
    {
        return $this->buttons;
    }
}
