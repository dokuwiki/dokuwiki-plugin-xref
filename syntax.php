<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_xref extends DokuWiki_Syntax_Plugin {

    var $dir = '';
    var $web = '';

    function syntax_plugin_xref(){
        $this->dir = rtrim($this->getConf('dir'),'/');
        $this->web = rtrim($this->getConf('web'),'/');
    }

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Andreas Gohr',
            'email'  => 'andi@splitbrain.org',
            'date'   => '2008-10-03',
            'name'   => 'PHPXref Plugin',
            'desc'   => 'Makes linking to a PHPXref generated API doc easy.',
            'url'    => 'http://dokuwiki.org/plugin:xref',
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'normal';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 150;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\[\[xref>.+?\]\]',$mode,'plugin_xref');
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        $match = trim(substr($match,7,-2));

        list($link,$name) = explode('|',$match,2);
        if(!$name) $name = $link;

        $first = 0;
        if($link[0] == '$') $first = 4;
        $found = $this->_find($link,$first);

        return array($link,$found,$name);
    }

    /**
     * Create output
     */
    function render($format, &$R, $data) {
        global $conf;
        if($format != 'xhtml') return false;

        //prepare for formating
        $link['target'] = $conf['target']['extern'];
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['class']  = 'xref_plugin';
        $link['name']  = hsc($data[2]);

        if(!$data[1]){
            $link['url']   = $this->web;
            $link['title'] = $this->getLang('unknown');
            $link['class'] .= ' xref_plugin_err';
        }else{
            $link['url']  = $this->web.'/'.$data[1];
            $link['title']  = sprintf($this->getLang('view'),hsc($data[0]));
        }

        $R->doc .= $R->_formatLink($link);
        return true;
    }

    /**
     * Try to find the given name in the xref directory
     *
     * @param int $first - defines which type should be searched first for the name
     */
    function _find($name,$first=0){
        $paths = array(
                    0 => '_functions',
                    1 => '_classes',
                    2 => '_constants',
                    3 => '_tables',
                    4 => '_variables'
                );

        $clean = preg_replace('/[^\w\-_]+/','',$name);
        $small = strtolower($clean);

        $path = $paths[$first];
        unset($paths[$first]);
        do{
            $check = $path.'/'.$clean.'.html';
            if(@file_exists($this->dir.'/'.$check)) return $check;
            $check = $path.'/'.$small.'.html';
            if(@file_exists($this->dir.'/'.$check)) return $check;
            $path = array_shift($paths);
        }while($path);

        // still here? might be a file reference
        $clean = preg_replace('/\.\.+/','.',$name);
        if(@file_exists($this->dir.'/'.$clean.'.html')){
            return $clean.'.html';
        }

        return '';
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
