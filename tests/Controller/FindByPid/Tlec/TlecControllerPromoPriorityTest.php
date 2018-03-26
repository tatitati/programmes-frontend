<?php
declare(strict_types = 1);

namespace Tests\App\Controller\FindByPid\Tlec;

use App\Builders\PromotionBuilder;
use App\Controller\FindByPid\TlecController;
use ReflectionClass;
use Tests\App\BaseWebTestCase;

class TlecControllerPromoPriorityTest extends BaseWebTestCase
{
    private $tlecController;

    public function setUp()
    {
        $this->tlecController = $this->createMock(TlecController::class);
    }

    public function testCanFindTheFirstRegularPromotionAndUpdateTheList()
    {
        $promotions = [
            $promo0 = PromotionBuilder::any()->with(['isSuperPromotion' => true])->build(),
            $promo1 = PromotionBuilder::any()->with(['isSuperPromotion' => false])->build(),
            $promo2 = PromotionBuilder::any()->with(['isSuperPromotion' => true])->build(),
            $promo3 = PromotionBuilder::any()->with(['isSuperPromotion' => false])->build(),
        ];

        $firstPromotion = $this->invokeMethodInController('extractPriorityPromotionAndUpdateList', [&$promotions]);

        $this->assertEquals($firstPromotion, $promo1);
        $this->assertEquals([0 => $promo0, 2 => $promo2, 3 => $promo3], $promotions);
    }

    public function testCannotRemoveAnythingFromArray()
    {
        $promotions = [
            $promo0 = PromotionBuilder::any()->with(['isSuperPromotion' => true])->build(),
            $promo1 = PromotionBuilder::any()->with(['isSuperPromotion' => true])->build(),
            $promo2 = PromotionBuilder::any()->with(['isSuperPromotion' => true])->build(),
        ];

        $firstPromotion = $this->invokeMethodInController('extractPriorityPromotionAndUpdateList', [&$promotions]);

        $this->assertNull($firstPromotion);
        $this->assertEquals([$promo0, $promo1, $promo2], $promotions);
    }

    /**
     * Edge case. Remove the first regular promotion from a list with only one promotion
     */
    public function testCanProduceAnEmptyArrayOfPromotions()
    {
        $promo0 = PromotionBuilder::any()->with(['isSuperPromotion' => false])->build();
        $promotions = [$promo0];

        $firstPromotion = $this->invokeMethodInController('extractPriorityPromotionAndUpdateList', [&$promotions]);

        $this->assertEquals($firstPromotion, $promo0);
        $this->assertEquals([], $promotions);
    }

    public function testReturnNullWhenNoPromotions()
    {
        $promotions = [];

        $firstPromotion = $this->invokeMethodInController('extractPriorityPromotionAndUpdateList', [&$promotions]);

        $this->assertNull($firstPromotion);
        $this->assertEquals([], $promotions);
    }

    private function invokeMethodInController(string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($this->tlecController));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->tlecController, $parameters);
    }
}
