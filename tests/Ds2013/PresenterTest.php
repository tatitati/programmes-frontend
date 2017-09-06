<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013;

use App\Ds2013\Presenter;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class PresenterTest extends TestCase
{
    public function testAbstractPresenter()
    {
        /** @var Presenter|PHPUnit_Framework_MockObject_MockObject $presenter */
        $presenter = $this->getMockForAbstractClass(Presenter::class, [], 'TestDemoObjectPresenter');

        $this->assertAttributeEquals([], 'options', $presenter);
        $this->assertSame('test_demo_object', $presenter->getTemplateVariableName());
        $this->assertSame('@Ds2013/test_demo_object.html.twig', $presenter->getTemplatePath());

        // Assert each presenter generates their own template info
        /** @var Presenter|PHPUnit_Framework_MockObject_MockObject $presenter */
        $presenter = $this->getMockForAbstractClass(Presenter::class, [], 'AnotherTestDemoObjectPresenter');
        $this->assertSame('another_test_demo_object', $presenter->getTemplateVariableName());
        $this->assertSame('@Ds2013/another_test_demo_object.html.twig', $presenter->getTemplatePath());
    }

    public function testGetOption()
    {
        /** @var Presenter|PHPUnit_Framework_MockObject_MockObject $presenter */
        $presenter = $this->getMockForAbstractClass(Presenter::class, [
            ['optionOne' => 1, 'optionTwo' => 2],
        ]);

        $this->assertSame(1, $presenter->getOption('optionOne'));
        $this->assertSame(2, $presenter->getOption('optionTwo'));
    }

    public function testGetOptionInvalid()
    {
        /** @var Presenter|PHPUnit_Framework_MockObject_MockObject $presenter */
        $presenter = $this->getMockForAbstractClass(Presenter::class, [
            ['optionOne' => 1, 'optionTwo' => 2],
        ]);

        $this->expectException(\App\Exception\InvalidOptionException::class);
        $this->expectExceptionMessage(
            'Called getOption with an invalid value. Expected one of "optionOne", "optionTwo" but got "garbage"'
        );

        $presenter->getOption('garbage');
    }

    public function testGetUniqueId()
    {
        /** @var Presenter|PHPUnit_Framework_MockObject_MockObject $presenter */
        $presenter = $this->getMockForAbstractClass(Presenter::class, [], 'TestDemoObjectPresenter');
        $initialId = $presenter->getUniqueId();

        // Assert format
        $this->assertRegExp('/^ds-2013-TestDemoObjectPresenter-[0-9]+$/', $initialId);

        // Assert we get the same value if we call uniqueID multiple times on the same Presenter
        $this->assertSame($initialId, $presenter->getUniqueId());

        // Assert a new presenter gets a different ID
        /** @var Presenter|PHPUnit_Framework_MockObject_MockObject $secondPresenter */
        $secondPresenter = $this->getMockForAbstractClass(Presenter::class, [], 'TestDemoObjectPresenter');
        $this->assertNotEquals($initialId, $secondPresenter->getUniqueId());
    }
}
