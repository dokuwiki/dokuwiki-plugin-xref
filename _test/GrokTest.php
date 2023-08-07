<?php

namespace dokuwiki\plugin\xref\test;

use dokuwiki\plugin\xref\Grok;
use DokuWikiTest;

/**
 * Grok tests for the xref plugin
 *
 * @group plugin_xref
 * @group plugins
 * @group internet
 */
class GrokTest extends DokuWikiTest
{
    /**
     * @return string[][]
     * @see testResultCount
     */
    public function provideData()
    {
        // These should all be unique enough to have only one result
        return [
            ['auth.php#auth_setup'],
            ['inc/Menu/Item/AbstractItem.php'],
            ['dokuwiki\Menu\Item\AbstractItem'],
            ['dokuwiki\Menu\Item\AbstractItem::getLabel()'],
            ['dokuwiki\Menu\Item\AbstractItem->getLabel()'],
            ['AbstractItem::getLabel()'],
            ['AbstractItem->getLabel()'],
            ['AbstractItem'],
        ];
    }

    /**
     * @dataProvider provideData
     * @param string $reference
     */
    public function testResultCount($reference)
    {
        $grok = new Grok($reference);
        $this->assertEquals(1, $grok->getResultCount(), $grok->getSearchUrl());
    }

    public function testEmptyData()
    {
        $grok = new Grok('', 'https://testurl/');
        $this->assertEquals('https://testurl', $grok->getSearchUrl());
        $this->assertSame(0, $grok->getResultCount());
    }
}
