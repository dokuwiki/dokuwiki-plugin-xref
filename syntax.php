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

        list($reference, $name) = explode('|', $match, 2);
        list($reference, $anchor) = explode('#', $reference, 2);
        if (!$name) $name = $reference;
        if ($anchor) $reference = "#" . $anchor;

        return array($reference, $name);
    }

    /** @inheritdoc */
    public function render($format, Doku_Renderer $R, $data)
    {
        global $conf;
        if ($format != 'xhtml') return false;

        list($reference, $name) = $data;
        $grok = new \dokuwiki\plugin\xref\Grok($reference, $this->getConf('grokbaseurl'));
        $count = $grok->getResultCount();

        $link = [
            'target' => $conf['target']['extern'],
            'style' => '',
            'pre' => '',
            'suf' => '',
            'more' => '',
            'class' => 'interwiki plugin_xref',
            'name' => hsc($name),
            'url' => $grok->getSearchUrl(),
            'title' => sprintf($this->getLang('view'), hsc($reference)),
        ];

        if ($count === false || $count === 0) {
            $link['title'] = $this->getLang('unknown');
            $link['class'] .= ' plugin_xref_err';
        }

        if ($count > 1) {
            $link['title'] = sprintf($this->getLang('search'), hsc($reference));
        }

        $R->doc .= $R->_formatLink($link);
        return true;
    }

}
