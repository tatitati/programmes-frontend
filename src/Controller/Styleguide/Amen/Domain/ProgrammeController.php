<?php
declare(strict_types = 1);
namespace App\Controller\Styleguide\Amen\Domain;

use App\Builders\BrandBuilder;
use App\Builders\ClipBuilder;
use App\Builders\EpisodeBuilder;
use App\Builders\SeriesBuilder;
use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;

class ProgrammeController extends BaseController
{
    public function __invoke()
    {
        return $this->renderWithChrome('styleguide/amen/domain/programme.html.twig', [
            'itemsOfDifferentTypes' => $this->buildProgrammesItemsOfDifferentTypes(),
            'itemsWithDifferentDisplayOptions' => $this->buildItemsWithDifferentDisplays(),
            'itemsWithDifferentHtmlStructure' => $this->buildItemsWithDifferentHtmlStructure(),
        ]);
    }

    /**
     * Use case: The user see two different programmes
     * @return array
     */
    private function buildProgrammesItemsOfDifferentTypes()
    {
        return [
            // programme items
            'Programme item is Episode' => [
                'item' => EpisodeBuilder::any()->with(['isStreamable' => true])->build(),
                'render_options' => [],
            ],
            'Programme item is Clip' => [
                'item' => ClipBuilder::any()->with(['isStreamable' => true, 'duration' => 1000])->build(),
                'render_options' => [],
            ],
            // programme containers
            'Programme container is Brand' => [
                'item' => BrandBuilder::any()->with(['isStreamable' => true])->build(),
                'render_options' => [],
            ],
            'Programme container is Series' => [
                'item' => SeriesBuilder::any()->with(['isStreamable' => true])->build(),
                'render_options' => [],
            ],
        ];
    }

    /**
     * Use case: The user see the same programme, but each one displays different fields. However they are the same.
     * They have different displays only.
     *
     * @return array
     */
    private function buildItemsWithDifferentDisplays()
    {
        $clipBuilderDefault = ClipBuilder::any()->with(['isStreamable' => true, 'duration' => 1000]);

        return [
            'TYPE VARIATION 1' => [
                'item' => $clipBuilderDefault->build(),
                'render_options' => [
                    'show_image' => false,
                ],
            ],
            'TYPE VARIATION 2' => [
                'item' => $clipBuilderDefault->build(),
                'render_options' => [
                    'media_variant' => 'media--card',
                ],
            ],
            'TITLE VARIATION' => [
                'item' => $clipBuilderDefault->build(),
                'render_options' => [
                    'title_options' => [
                        'max_title_length' => 10,
                    ],
                ],
            ],
            'BODY VARIATION' => [
                'item' => $clipBuilderDefault->with([
                    'releaseDate' => new PartialDate(2020, 03, 21),
                ])->build(),
                'render_options' => [
                    'body_options' => [
                        'show_synopsis' => true,
                        'show_release_date' => true,
                    ],
                ],
            ],
            'CTA VARIATION (call to action)' => [
                'item' => $clipBuilderDefault->build(),
                'render_options' => [
                    'cta_options' => [
                        'show_duration' => false,
                    ],
                ],
            ],
            'IMAGE VARIATION' => [
                'item' => $clipBuilderDefault->build(),
                'render_options' => [
                    'image_options' => [
                        'media_panel_class' => '1/2',
                        'badge_text' => 'some badge text',
                    ],
                ],
            ],
            'IMAGE VARIATION 2' => [
                'item' => $clipBuilderDefault->build(),
                'render_options' => [
                    'show_image' => false,
                    'image_options' => [
                        'badge_text' => 'some badge text',
                    ],
                ],
            ],
        ];
    }

    private function buildItemsWithDifferentHtmlStructure()
    {
        $defaultClipBuilder = ClipBuilder::any()->with(['isStreamable' => true, 'duration' => 1000]);

        return [
            'TITLE VARIATION 1' => [
                'item' => $defaultClipBuilder->build(),
                'render_options' => [
                    'title_options' => [
                        'h_tag' => 'h1',
                        'text_colour_on_title_link' => false,
                        'title_size_large' => 'gel-trafalgar',
                        'title_size_small' => 'gel-pica',
                    ],
                ],
            ],
            'BODY VARIATION' => [
                'item' => $defaultClipBuilder->with([
                    'releaseDate' => new PartialDate(2020, 03, 21),
                ])->build(),
                'render_options' => [
                    'body_options' => [
                        'show_synopsis' => true,
                        'show_release_date' => true,
                        'synopsis_class' => 'media__meta-item',
                        'full_details_class' => 'media',
                    ],
                ],
            ],
            'IMAGE VARIATION' => [
                'item' => $defaultClipBuilder->build(),
                'render_options' => [
                    'image_options' => [
                        'badge_text' => 'This is a badge text',
                        'badge_class' => 'text--annotation-white-outline',
                    ],
                ],
            ],
        ];
    }
}
