<?php

namespace dokuwiki\plugin\xref\test;

use dokuwiki\plugin\xref\Heuristics;
use DokuWikiTest;

/**
 * Heuristics tests for the xref plugin
 *
 * @group plugin_xref
 * @group plugins
 */
class HeuristicsTest extends DokuWikiTest
{
    /**
     * @return string[][]
     * @see testHeuristics
     */
    public function provideData()
    {
        return [
            ['auth.php#auth_setup', 'auth_setup', 'auth.php'],
            ['inc/Menu/Item/AbstractItem.php', '', 'inc Menu Item AbstractItem.php'],
            ['dokuwiki\Menu\Item\AbstractItem', 'AbstractItem', 'inc Menu Item AbstractItem'],
            ['dokuwiki\Menu\Item\AbstractItem::getLabel()', 'getLabel', 'inc Menu Item AbstractItem'],
            ['dokuwiki\Menu\Item\AbstractItem->getLabel()', 'getLabel', 'inc Menu Item AbstractItem'],
            ['AbstractItem::getLabel()', 'getLabel', 'AbstractItem'],
            ['AbstractItem->getLabel()', 'getLabel', 'AbstractItem'],
            ['$INFO', 'INFO', ''],
            ['foobar()', 'foobar', ''],
            ['FooBar()', 'FooBar', ''],
            ['foobar(\'a\', 5)', 'foobar', ''],
            ['FooBar(\'a\', 5)', 'FooBar', ''],
            ['foobar($test, $more)', 'foobar', ''],
            ['FooBar($test, $more)', 'FooBar', ''],
            ['AbstractItem', 'AbstractItem', 'AbstractItem'],
            ['abstractItem', 'abstractItem', ''],
            ['Doku_Event', 'Event', 'inc Extension Event' ],
        ];
    }

    /**
     * @dataProvider provideData
     * @param string $reference
     * @param string $expDef
     * @param string $expPath
     */
    public function testHeuristics($reference, $expDef, $expPath)
    {
        $heur = new Heuristics($reference);

        $this->assertEquals($expDef, $heur->getDef(), 'definition is wrong');
        $this->assertEquals($expPath, $heur->getPath(), 'path is wrong');
    }

    public function testDeprecations() {
        $heur = new Heuristics('foo');
        $deprecations = $heur->getDeprecations();

        $this->assertArrayHasKey('Doku_Event', $deprecations);
        $this->assertEquals('\dokuwiki\Extension\Event', $deprecations['Doku_Event']);

        $this->assertArrayHasKey('RemoteException', $deprecations);
        $this->assertEquals('\dokuwiki\Remote\RemoteException', $deprecations['RemoteException']);
    }
}
