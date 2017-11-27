<?php
declare(strict_types = 1);
namespace App\Controller\Styleguide\Amen\Organism;

use App\Builders\EpisodeBuilder;
use App\Builders\ImageBuilder;
use App\Builders\PromotionBuilder;
use App\Controller\BaseController;

class PromotionController extends BaseController
{
    public function __invoke()
    {
        return $this->renderWithChrome('styleguide/amen/organism/promotion.html.twig', [
            'promosOfDifferentTypes' => $this->buildPromotionsOfDifferentTypes(),
            'promosWithDifferentDisplayOptions' => $this->buildPromotionsWithDifferentDisplays(),
            'promotionsWithDifferentHtmlAttributes' => $this->buildPromotionsWithDifferentHtmlAttributes(),
        ]);
    }

    /**
     * Use case: The user see two different promotions
     * @return array
     */
    private function buildPromotionsOfDifferentTypes()
    {
        return [
            'Promotion Image' => [
                'item' => PromotionBuilder::default()
                    ->withPromotedEntity(ImageBuilder::default()->build())
                    ->build(),
                'render_options' => [],
            ],
            'Promotion of core entity' => [
                'item' => PromotionBuilder::default()
                    ->withPromotedEntity(
                        EpisodeBuilder::default()->withDuration(100)->build()
                    )
                    ->build(),
                'render_options' => [],
            ],
        ];
    }

    /**
     * Use case: the user sees the same promotions, but some fields are hiden/displayed.
     * List of options to customize render of promotion:
     * @see \App\DsAmen\Organism\Promotion\PromotionPresenter
     * @return array
     */
    private function buildPromotionsWithDifferentDisplays()
    {
        $defaultPromotion = PromotionBuilder::default()->build();

        return [
            'Promotion displaying 3 maximum related links' => [
                'item' => $defaultPromotion,
                'render_options' => [
                    'show_synopsis' => true,
                    'show_image' => false,
                    'related_links_count' => 3,
                ],
            ],
            'Promotion displaying 1 maximum related links' => [
                'item' => $defaultPromotion,
                'render_options' => [
                    'show_synopsis' => false,
                    'show_image' => true,
                    'related_links_count' => 1,
                ],
            ],
        ];
    }

    /**
     * Use case: The user see the same promotions, same fields, but the layout is different.
     * List of options to customize render of promotion:
     * @see \App\DsAmen\Organism\Promotion\PromotionPresenter
     * @return array
     */
    private function buildPromotionsWithDifferentHtmlAttributes()
    {
        $defaultPromotion = PromotionBuilder::default()->build();

        return [
            'Promotion of image not showing synopsis' => [
                'item' => $defaultPromotion,
                'render_options' => [
                    'title_size' => 'gel-trafalgar',
                    'h_tag' => 'h1',
                    'media_panel_class' => '1/1',
                    'link_location_prefix' => 'custom_location_prefix',
                    'media_variant' => 'media--column media--card',
                ],
            ],
            'Promotion of image showing synopsis' => [
                'item' => $defaultPromotion,
                'render_options' => [
                    'title_size' => 'gel-pica-bold',
                    'h_tag' => 'h6',
                    'media_panel_class' => '1/2',
                    'media_variant' => 'media--card',
                ],
            ],
        ];
    }
}
