<?php
declare(strict_types = 1);

namespace Tests\App\Controller\Helpers;

use App\Controller\Helpers\IsiteKeyHelper;
use PHPUnit\Framework\TestCase;

class IsiteKeyHelperTest extends TestCase
{
    public function testGuidIsTranslatedToKey()
    {
        $helper = new IsiteKeyHelper();
        $this->assertSame('1CFplll4CcRR4pz402p8yW7', $helper->convertGuidToKey('453550a5-110d-4446-91fa-6ac9ffd33317'));
        $this->assertSame('5h7QnDCN0KxLglc7QyNhpG', $helper->convertGuidToKey('0453550a-5110-d444-691f-aac9ffd33317'));
    }

    public function testKeyIsTranslatedToGuid()
    {
        $helper = new IsiteKeyHelper();
        $this->assertSame('453550a5-110d-4446-91fa-6ac9ffd33317', $helper->convertKeyToGuid('1CFplll4CcRR4pz402p8yW7'));
        $this->assertSame('0453550a-5110-d444-691f-aac9ffd33317', $helper->convertKeyToGuid('5h7QnDCN0KxLglc7QyNhpG'));
    }

    public function testAGuidIsNotOnlySomethingWithADash()
    {
        $helper = new IsiteKeyHelper();
        $this->assertFalse($helper->isKeyAGuid('-'));
        $this->assertFalse($helper->isKeyAGuid('5h7QnDCN0KxLglc7QyNhpG'));
        $this->assertTrue($helper->isKeyAGuid('453550a5-110d-4446-91fa-6ac9ffd33317'));
        $this->assertTrue($helper->isKeyAGuid('a8ed3fbc-db98-3f8e-b974-3a6348083c0f'));
    }
}
