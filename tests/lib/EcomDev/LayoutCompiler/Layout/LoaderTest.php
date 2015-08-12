<?php

use EcomDev_LayoutCompiler_Layout_Loader as LayoutLoader;
use org\bovigo\vfs\vfsStream as Stream;
use org\bovigo\vfs\vfsStreamDirectory as StreamDirectory;
use EcomDev_LayoutCompiler_Layout_Item_Remove as RemoveItem;

class EcomDev_LayoutCompiler_Layout_LoaderTest 
    extends PHPUnit_Framework_TestCase
{
    use EcomDev_LayoutCompiler_HelperTestTrait;

    /**
     * @var LayoutLoader
     */
    private $loader;

    /**
     * Virtual files
     * 
     * @var StreamDirectory
     */
    private $fileSystem;
    
    protected function setUp()
    {
        $this->loader = new LayoutLoader();
        $this->fileSystem = Stream::setup('root');
        $files = array(
            'handle_one_file1.php' => sprintf(
                '<?php %s',
                '$this->addItem(new EcomDev_LayoutCompiler_Layout_Item_Remove("handle_one_block_one"));'
            ),
            'handle_one_file2.php' => sprintf( // File that should be merged to file1
                '<?php %s',
                implode("\n", array(
                    '$this->addItem(new EcomDev_LayoutCompiler_Layout_Item_Remove("handle_one_block_two"));',
                    '$this->addItem($item = new EcomDev_LayoutCompiler_Layout_Item_Remove("handle_one_block_three"));',
                    '$this->addItemRelation($item, "handle_one_block_three");',
                ))
            ), 
            'handle_two_file1.php' => sprintf( // File that has only one item
                '<?php %s',
                implode("\n", array(
                    '$this->addItem(new EcomDev_LayoutCompiler_Layout_Item_Remove("handle_two_block_one"));'
                ))
            ),
            'handle_three_file1.php' => sprintf( // File that returns an empty array
                '<?php '
            ),
            'handle_three_file2.php' => sprintf( // File that returns an empty array
                '<?php '
            ),
            'handle_four_file1.php' => sprintf( // File without content
                '<?php '
            )
        );
        Stream::create($files, $this->fileSystem);
    }
    
    public function testItUsesInternalFlagToCheckIfHandleIsLoaded()
    {
        $this->writeAttribute($this->loader, 'loaded', array('loaded_handle' => true, 'loaded_handle_two' => false));
        $this->assertTrue($this->loader->isLoaded('loaded_handle'));
        $this->assertFalse($this->loader->isLoaded('not_loaded_handle'));
        $this->assertTrue($this->loader->isLoaded('loaded_handle_two'));
    }
    
    public function testItResetsLoadedHandleList()
    {
        $this->writeAttribute($this->loader, 'loaded', array('loaded_handle' => true));
        $this->assertSame($this->loader, $this->loader->reset());
        $this->assertAttributeSame(array(), 'loaded', $this->loader);
    }
    
    public function testItIncludesAllFilesForAHandleAndMarksItAsLoaded()
    {
        $index = $this->createIndex();
        $index->expects($this->once())
            ->method('getHandleIncludes')
            ->with('handle_one')
            ->willReturn(
                array(
                    Stream::url($this->fileSystem->path() . '/handle_one_file1.php'),
                    Stream::url($this->fileSystem->path() . '/handle_one_file2.php')
                )
            );

        $this->assertFalse($this->loader->isLoaded('handle_one'));
        
        $this->assertEquals(
            array(
                new RemoveItem('handle_one_block_one'),
                new RemoveItem('handle_one_block_two'),
                new RemoveItem('handle_one_block_three')
            ),
            $this->loader->load('handle_one', $index)
        );
        
        $this->assertTrue($this->loader->isLoaded('handle_one'));
    }

    public function testItIncludesASingleFileForAHandleAndMarksItAsLoaded()
    {
        $index = $this->createIndex();
        $index->expects($this->once())
            ->method('getHandleIncludes')
            ->with('handle_two')
            ->willReturn(
                array(
                    Stream::url($this->fileSystem->path() . '/handle_two_file1.php')
                )
            );

        $this->assertEquals(
            array(
                new RemoveItem('handle_two_block_one')
            ),
            $this->loader->load('handle_two', $index)
        );

        $this->assertTrue($this->loader->isLoaded('handle_two'));
    }

    public function testItIncludesFileWithEmptyArray()
    {
        $index = $this->createIndex();
        $index->expects($this->once())
            ->method('getHandleIncludes')
            ->with('handle_three')
            ->willReturn(
                array(
                    Stream::url($this->fileSystem->path() . '/handle_three_file1.php'),
                    Stream::url($this->fileSystem->path() . '/handle_three_file2.php')
                )
            );

        $this->assertEquals(
            array(),
            $this->loader->load('handle_three', $index)
        );

        $this->assertTrue($this->loader->isLoaded('handle_three'));
    }

    public function testItIncludesAFileWithNoReturnValueAndStillMarksItAsLoaded()
    {
        $index = $this->createIndex();
        $index->expects($this->once())
            ->method('getHandleIncludes')
            ->with('handle_four')
            ->willReturn(
                array(
                    Stream::url($this->fileSystem->path() . '/handle_four_file1.php')
                )
            );

        $this->assertEquals(
            array(),
            $this->loader->load('handle_four', $index)
        );

        $this->assertTrue($this->loader->isLoaded('handle_four'));
    }

    public function testItIncludesNonExistingFileAndMarksHandleAsLoaded()
    {
        $index = $this->createIndex();
        $index->expects($this->once())
            ->method('getHandleIncludes')
            ->with('handle_hundred')
            ->willReturn(
                array(
                    Stream::url($this->fileSystem->path() . '/handle_hundred_file1.php')
                )
            );

        $this->assertEquals(
            array(),
            $this->loader->load('handle_hundred', $index)
        );

        $this->assertTrue($this->loader->isLoaded('handle_hundred'));
    }

    public function testItLoadsItemsIntoProcessor()
    {
        $index = $this->createIndex();
        $index->expects($this->once())
            ->method('getHandleIncludes')
            ->with('handle_one')
            ->willReturn(
                array(
                    Stream::url($this->fileSystem->path() . '/handle_one_file1.php'),
                    Stream::url($this->fileSystem->path() . '/handle_one_file2.php')
                )
            );

        $processor = $this->createProcessor();
        $processor->expects($this->exactly(3))
            ->method('addItem')
            ->id('addItem')
            ->withConsecutive(
                array($this->equalTo(new RemoveItem('handle_one_block_one'))),
                array($this->equalTo(new RemoveItem('handle_one_block_two'))),
                array($this->equalTo(new RemoveItem('handle_one_block_three')))
            )
            ->willReturnSelf();

        $processor->expects($this->once())
            ->method('addItemRelation')
            ->after('addItem')
            ->with($this->equalTo(new RemoveItem('handle_one_block_three')), 'handle_one_block_three')
            ->willReturnSelf();

        $this->assertSame(
            $this->loader, 
            $this->loader->loadIntoProcessor('handle_one', $processor, $index)
        );
    }
    
    public function testItDoesNotLoadItemsIntoProcessorIfTheyAreAlreadyLoaded()
    {
        $index = $this->createIndex();
        $index->expects($this->never())
            ->method('getHandleIncludes');

        $processor = $this->createProcessor();
        $processor->expects($this->never())
            ->method('addItem');
        
        $this->writeAttribute($this->loader, 'loaded', array('handle_one' => true));

        $this->assertSame(
            $this->loader,
            $this->loader->loadIntoProcessor('handle_one', $processor, $index)
        );
    }
}
