<?php

namespace dokuwiki\plugin\xref;

/**
 * Figure out what to send to Grok to hopefully show the right, single hit
 */
class Heuristics
{

    /** @var string the definition to search */
    protected $def = '';
    /** @var string the path to use */
    protected $path = '';

    /**
     * Try to gues what the given reference means and how to best search for it
     *
     * @param string $reference
     */
    public function __construct($reference)
    {
        if ($reference !== '') $reference = $this->checkHash($reference);
        if ($reference !== '') $reference = $this->checkFilename($reference);
        if ($reference !== '') $reference = $this->checkNamespace($reference);
        if ($reference !== '') $reference = $this->checkClassPrefix($reference);
        if ($reference !== '') $reference = $this->checkVariable($reference);
        if ($reference !== '') $reference = $this->checkFunction($reference);
        if ($reference !== '') $reference = $this->checkPSRClass($reference);
        if ($reference !== '') $this->def = $reference;
    }

    /**
     * @return string
     */
    public function getDef()
    {
        return trim(preg_replace('/[^\w]+/', '', $this->def));
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return trim(preg_replace('/[^\w.]+/', ' ', $this->path));
    }

    /**
     * Handle things in the form path#symbol
     *
     * @param string $reference
     * @return string
     */
    protected function checkHash($reference)
    {
        if (strpos($reference, '#') === false) return $reference;
        list($this->path, $this->def) = explode('#', $reference, 2);
        return '';
    }

    /**
     * Known file extension?
     *
     * @param string $reference
     * @return mixed|string
     */
    protected function checkFilename($reference)
    {
        if (preg_match('/\.(php|js|css|html)$/', $reference)) {
            $this->def = '';
            $this->path = $reference;
            return '';
        }
        return $reference;
    }

    /**
     * Namespaces are paths
     *
     * @param string $reference
     * @return string
     */
    protected function checkNamespace($reference)
    {
        if (strpos($reference, '\\') === false) return $reference;

        $parts = explode('\\', $reference);
        $parts = array_filter($parts);
        $reference = array_pop($parts); // last part may be more than a class

        // our classes are in inc
        if ($parts[0] == 'dokuwiki') $parts[0] = 'inc';

        $this->path = join(' ', $parts);

        return $reference;
    }

    /**
     * Is there something called on a class?
     */
    protected function checkClassPrefix($reference)
    {
        if (
            strpos($reference, '::') === false &&
            strpos($reference, '->') === false
        ) {
            return $reference;
        }
        list($class, $reference) = preg_split('/(::|->)/', $reference, 2);

        $this->path .= ' ' . $class;
        $this->def = $reference;
        return '';
    }

    /**
     * Clearly a variable
     *
     * @param string $reference
     * @return string
     */
    protected function checkVariable($reference)
    {
        if ($reference[0] == '$') {
            $this->def = $reference;
            return '';
        }
        return $reference;
    }

    /**
     * It's a function
     *
     * @param string $reference
     * @return string
     */
    protected function checkFunction($reference)
    {
        if (substr($reference, -2) == '()') {
            $this->def = $reference;
            return '';
        }
        if (preg_match('/\(.+?\)$/', $reference)) {
            [$reference, /* $arguments */] = explode('(', $reference, 2);
            $this->def = $reference;
            return '';
        }
        return $reference;
    }

    /**
     * Upercase followed by lowercase letter, must be a class
     *
     * Those are in their own files, so add it to the path
     * @param $reference
     * @return mixed|string
     */
    protected function checkPSRClass($reference)
    {
        if (preg_match('/^[A-Z][a-z]/', $reference)) {
            $this->def = $reference;
            $this->path .= ' ' . $reference;
            return '';
        }
        return $reference;
    }
}
