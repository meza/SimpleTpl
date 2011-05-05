<?php
/**
 * TplBlock.php
 *
 * Holds the TplBlock class
 *
 * PHP Version: 5.2.10
 *
 * @category File
 * @package  SimpleTpl
 * @author   meza <meza@meza.hu>
 * @license  GPLv3 <http://www.gnu.org/licenses/>
 * @version  SVN: $Id: TplBlock.php 14 2009-12-04 21:21:41Z meza $
 * @link     http://www.meza.hu
 */

/**
 * The TplBlock class is responsible for parsing the template file's block
 *
 * PHP Version: 5.2.10
 *
 * @category Class
 * @package  SimpleTpl
 * @author   meza <meza@meza.hu>
 * @license  GPLv3 <http://www.gnu.org/licenses/>
 * @link     http://www.meza.hu
 */
class TplBlock
{

    /**
     * @var array of children elements
     */
    private $_children = array();

    /**
     * @var array of variables in the block
     */
    private $_vars = array();

    /**
     * @var array of child blocks in this block
     */
    private $_blocks = array();

    /**
     * @var string output buffer
     */
    private $_buffer = '';

    /**
     * @var string the template of the block
     */
    private $_contents = '';

    /**
     * @var bool to trim empty lines or not
     */
    private $_clearSpaces = false;

    /**
     * @var string base dir of the template
     */
    private $_fileDir;


    /**
     * Constructs a block
     *
     * @param array  $children    Child blocks
     * @param string $contents    Template string
     * @param bool   $clearSpaces True to remove empty lines
     */
    public function __construct(array $children, $contents, $clearSpaces=true)
    {
        $this->_children    = $children;
        $this->_contents    = $contents;
        $this->_clearSpaces = $clearSpaces;

    }//end __construct()


    /**
     * Retrieves a child block
     *
     * @param string $blockName The name of the block
     *
     * @return TplBlock
     */
    public function getChildBlock($blockName)
    {
        return $this->_children[$blockName];

    }//end getChildBlock()


    /**
     * Retrieves a child block
     * 
     * @param string $blockName The name of the block
     * 
     * @return TplBlock
     */
    public function __get($blockName)
    {
        return $this->getChildBlock($blockName);

    }//end __get()


    /**
     * Assigns a complex $@alue to a $key
     *
     * @param string       $key   The key to assign to
     * @param array/object $value The value.
     *
     * @return TplBlock WHY?
     */
    private function _assignComplex($key, $value)
    {
        foreach ($value as $subKey => $subValue) {
            $this->assign($key.'.'.$subKey, $subValue);
        }

        return $this;

    }//end _assignComplex()


    /**
     * Assigns a $value to a $key
     *
     * @param string $key   The name of the variable
     * @param mixed  $value The value being assigned to the key
     *
     * @return TplBlock WHY?
     */
    public function assign($key, $value='')
    {
        if ((true === is_array($value)) || (true === is_object($value))) {
            return $this->_assignComplex($key, $value);
        }

        $this->_vars[$key] = $value;

        foreach ($this->_children as $name => $block) {
            $block->assign($key, $value);
        }

        return $this;

    }//end assign()


    /**
     * Optimizes the contents. Clears spaces if it should.
     *
     * @param string $contents The contents to optimize
     *
     * @return string the optimized contents
     */
    private function _optimize($contents)
    {
        if (true === $this->_clearSpaces) {
            return preg_replace('/\\n\s+\\n/s', "\n", $contents);
        }

        return $contents;

    }//end _optimize()


    /**
     * Replaces a variable placeholder with it's assigned value
     *
     * @param string $tplString Template string
     * 
     * @return string result
     */
    private function _bindVariables($tplString)
    {
        $result = $tplString;
        foreach ($this->_vars as $var => $value) {
            $result = str_replace('{'.$var.'}', $value, $result);
        }

        return $result;

    }//end _bindVariables()


    /**
     * Expands blocks
     * 
     * @param string $tplString The template string
     *
     * @return string The expanded template string
     *
     * @todo document
     */
    private function _expandBlocks($tplString)
    {
        $result = $tplString;
        foreach ($this->_children as $name => $block) {
            $snapshot = $block->flush();
            $result   = str_replace('##'.$name.'##', $snapshot, $result);
        }

        return $result;

    }//end _expandBlocks()


    /**
     * Removes unused subblock templates
     *
     * @param string $tplString content template
     *
     * @return string The cleaned template
     */
    private function _deleteUnused($tplString)
    {
        return preg_replace('/##(.*)##/', '', $tplString);

    }//end _deleteUnused()


    /**
     * Creates a snapshot of the template with
     *
     * @return string
     */
    private function _snapshot()
    {
        $result = $this->_contents;
        $result = $this->_bindVariables($result);
        $result = $this->_expandBlocks($result);
        $result = $this->_deleteUnused($result);
        $result = $this->_optimize($result);

        return $result;

    }//end _snapshot()


    /**
     * Takes a snapshot of the block and adds it to the buffer
     *
     * @return string buffer
     */
    public function parse()
    {
        $this->_buffer .= $this->_snapshot();
        return $this;

    }//end parse()


    /**
     * Returns the current content of the buffer and empties it;
     * 
     * @return string
     */
    public function flush()
    {
        $result        = $this->_buffer;
        $this->_buffer = '';

        return $result;

    }//end flush()


}//end class

?>