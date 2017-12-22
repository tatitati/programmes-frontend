<?php

namespace Tests\App\Builders;

use App\Builders\PromotionBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use PHPUnit\Framework\TestCase;
use TypeError;

class PromotionBuilderTest extends TestCase
{
    public function testBasicCreation()
    {
        $promotion = PromotionBuilder::any()->build();
        $this->assertCount(2, $promotion->getRelatedLinks());
        $this->assertInstanceOf(Image::class, $promotion->getPromotedEntity());
    }

    public function testCanCreatePromotionsOfImage()
    {
        $promotion = PromotionBuilder::ofImage()->build();
        $this->assertInstanceOf(Image::class, $promotion->getPromotedEntity());
    }

    public function testCanCreatePromotionsOfCoreEntity()
    {
        $promotion = PromotionBuilder::ofCoreEntity()->build();
        $this->assertInstanceOf(Episode::class, $promotion->getPromotedEntity());
    }

    public function testCanCreateComplexPromotions()
    {
        $promotion = PromotionBuilder::any()->with(['isSuperPromotion' => true])->build();
        $this->assertTrue($promotion->isSuperPromotion());
    }

    public function testDelayCreationOfObject()
    {
        $builder = PromotionBuilder::any()->with(['isSuperPromotion' => true]);
        $builder->with(['title' => 'my title promotion']);
        $promotion = $builder->build();

        $this->assertTrue($promotion->isSuperPromotion());
        $this->assertEquals('my title promotion', $promotion->getTitle());
    }

    /**
     * @expectedException TypeError
     */
    public function testExceptionIsRaisedWhenTryingToSetWrongDataType()
    {
        $promotion = PromotionBuilder::any()->with(['url' => ['wrong_data_type']])->build();
        $this->assertTrue($promotion->isSuperPromotion());
    }
}
