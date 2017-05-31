<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013;

use App\Ds2013\InvalidOptionException;
use App\Ds2013\Presenter;
use PHPUnit\Framework\TestCase;

class PresenterTest extends TestCase
{
    public function testAbstractPresenter()
    {
        $presenter = $this->getMockForAbstractClass(Presenter::class, [], 'TestDemoObjectPresenter');

        $this->assertAttributeEquals([], 'options', $presenter);
        $this->assertSame('test_demo_object', $presenter->getTemplateVariableName());
        $this->assertSame('@Ds2013/test_demo_object.html.twig', $presenter->getTemplatePath());

        // Assert each presenter generates their own template info
        $presenter = $this->getMockForAbstractClass(Presenter::class, [], 'AnotherTestDemoObjectPresenter');
        $this->assertSame('another_test_demo_object', $presenter->getTemplateVariableName());
        $this->assertSame('@Ds2013/another_test_demo_object.html.twig', $presenter->getTemplatePath());
    }

    public function testGetOption()
    {
        $presenter = $this->getMockForAbstractClass(Presenter::class, [
            ['optionOne' => 1, 'optionTwo' => 2],
        ]);

        $this->assertSame(1, $presenter->getOption('optionOne'));
        $this->assertSame(2, $presenter->getOption('optionTwo'));
    }

    public function testGetOptionInvalid()
    {
        $presenter = $this->getMockForAbstractClass(Presenter::class, [
            ['optionOne' => 1, 'optionTwo' => 2],
        ]);

        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage(
            'Called getOption with an invalid value. Expected one of "optionOne", "optionTwo" but got "garbage"'
        );

        $presenter->getOption('garbage');
    }

    public function testGetUniqueID()
    {
        $presenter = $this->getMockForAbstractClass(Presenter::class, [], 'TestDemoObjectPresenter');
        $uniqueIdFn = $this->boundCall('getUniqueId', $presenter);

        $initialId = $uniqueIdFn();

        // Assert format
        $this->assertRegexp('/^ds2013-TestDemoObjectPresenter-[0-9]+$/', $initialId);

        // Assert we get the same value if we call uniqueID multiple times on the same Presenter
        $this->assertSame($initialId, $uniqueIdFn());

        // Assert a new presenter gets a different ID
        $secondPresenter = $this->getMockForAbstractClass(Presenter::class, [], 'TestDemoObjectPresenter');
        $secondPresenterUniqueIdFn = $this->boundCall('getUniqueId', $secondPresenter);

        $this->assertNotEquals($initialId, $secondPresenterUniqueIdFn());
    }

    /**
     * This is funky. It generates a closure that has its scope bound to a
     * presenter, which means it has access to call protected function names.
     * Thus we can call boundCall('protectedFn') to create a function that
     * calls $protectedFunction->protectedFn().
     */
    private function boundCall(string $protectedFunctionName, ?Presenter $presenter = null): callable
    {
        if (!$presenter) {
            $presenter = $this->getMockForAbstractClass(Presenter::class);
        }

        // Define a closure that will call the protected method using "this".
        $callable = function (...$args) use ($protectedFunctionName) {
            return $this->{$protectedFunctionName}(...$args);
        };
        // Bind the closure to $presenter's scope.
        return $callable->bindTo($presenter, $presenter);
    }
}
