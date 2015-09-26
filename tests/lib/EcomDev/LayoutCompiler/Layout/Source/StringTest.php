<?php

use EcomDev_LayoutCompiler_Layout_Source_String as SourceString;

class EcomDev_LayoutCompiler_Layout_Source_StringTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var SourceString
     */
    private $source;
    
    protected function setUp()
    {
        $this->source = new SourceString('<content><item></item></content>');
    }
    
    public function testItUsesObjectChecksumAsPartOfIdentifier()
    {
        $this->assertSame('string_56bc5812441f9374f81270424f729ff1', $this->source->getId());
    }
    
    public function testItReturnsChecksumOfContent()
    {
        $this->assertSame('56bc5812441f9374f81270424f729ff1', $this->source->getChecksum());
    }
    
    public function testItUsesIdAsOriginalPath()
    {
        $this->assertSame('string_56bc5812441f9374f81270424f729ff1', $this->source->getOriginalPath());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage String source has a malformed structure:
     */
    public function testItThrowsRuntimeErrorForMalformedXmlOnLoad()
    {
        $this->source = new SourceString('<some>node></node></some>');
        $this->source->load();
    }
    
    public function testItParsesContentAndReturnsAnArrayWithIdentifierOfSourceAsKey()
    {
        $loadedItems = $this->source->load();
        $this->assertSame(array('string_56bc5812441f9374f81270424f729ff1'), array_keys($loadedItems));
        $this->assertXmlStringEqualsXmlString(
            (new SimpleXMLElement('<layout><content><item></item></content></layout>'))->asXML(),
            $loadedItems['string_56bc5812441f9374f81270424f729ff1']->asXML()
        );
    }
}