<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

class syntax_plugin_xref extends DokuWiki_Syntax_Plugin
{

    protected $dir = '';
    protected $web = '';

    public function __construct()
    {
        $this->dir = rtrim($this->getConf('dir'), '/');
        $this->web = rtrim($this->getConf('web'), '/');
    }

    /** @inheritdoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritdoc */
    public function getPType()
    {
        return 'normal';
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 150;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('\[\[xref>.+?\]\]', $mode, 'plugin_xref');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $match = trim(substr($match, 7, -2));

        list($link, $name) = explode('|', $match, 2);
        list($link, $anchor) = explode('#', $link, 2);
        if (!$name) $name = $link;
        if ($anchor) $anchor = "#" . $anchor;

        $first = 0;
        if ($link[0] == '$') $first = 4;
        $found = $this->find($link, $first);

        return array($link, $found, $name, $anchor);
    }

    /** @inheritdoc */
    public function render($format, Doku_Renderer $R, $data)
    {
        global $conf;
        if ($format != 'xhtml') return false;

        //prepare for formating
        $link['target'] = $conf['target']['extern'];
        $link['style'] = '';
        $link['pre'] = '';
        $link['suf'] = '';
        $link['more'] = '';
        $link['class'] = 'xref_plugin';
        $link['name'] = hsc($data[2]);

        if (!$data[1]) {
            $link['url'] = $this->web;
            $link['title'] = $this->getLang('unknown');
            $link['class'] .= ' xref_plugin_err';
        } else {
            $link['url'] = $this->web . '/' . $data[1] . hsc($data[3]);
            $link['title'] = sprintf($this->getLang('view'), hsc($data[0]));
        }

        $R->doc .= $R->_formatLink($link);
        return true;
    }

    /**
     * Try to find the given name in the xref directory
     *
     * @param int $first - defines which type should be searched first for the name
     */
    protected function find($name, $first = 0)
    {
        $paths = array(
            0 => '_functions',
            1 => '_classes',
            2 => '_constants',
            3 => '_tables',
            4 => '_variables',
        );

        $clean = preg_replace('/[^\w\-_]+/', '', $name);
        $small = strtolower($clean);

        $path = $paths[$first];
        unset($paths[$first]);
        do {
            $check = $path . '/' . $clean . '.html';
            if (@file_exists($this->dir . '/' . $check)) return $check;
            $check = $path . '/' . $small . '.html';
            if (@file_exists($this->dir . '/' . $check)) return $check;
            $path = array_shift($paths);
        } while ($path);

        // still here? might be a file reference
        $clean = preg_replace('/\.\.+/', '.', $name);
        if (@file_exists($this->dir . '/' . $clean . '.html')) {
            return $clean . '.html';
        }

        return '';
    }

}
