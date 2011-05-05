<?php
/**
 * SimpleTpl.php
 *
 * Holds the SimpleTpl class
 *
 * PHP Version: 5.2.10
 *
 * @category File
 * @package  SimpleTpl
 * @author   meza <meza@meza.hu>
 * @license  GPLv3 <http://www.gnu.org/licenses/>
 * @version  SVN: $Id: SimpleTpl.php 14 2009-12-04 21:21:41Z meza $
 * @link     http://www.meza.hu
 */

/**
 * The SimpleTpl class is responsible for parsing the template files
 *
 * PHP Version: 5.2.10
 *
 * @category Class
 * @package  SimpleTpl
 * @author   meza <meza@meza.hu>
 * @license  GPLv3 <http://www.gnu.org/licenses/>
 * @link     http://www.meza.hu
 */
class SimpleTpl
{

    /**
     * @var TplFactory
     */
    private $_factory;

    /**
     * @var TplBlock
     */
    private $_block;


    /**
     * Creates a TplBlock from a file
     *
     * @param string   $fileName    The filename to work from
     * @param bool     $clearSpaces True if all empty lines should be deleted.
     * @param TplBlock $block       block
     *
     * @return TplBlock
     */
    private function _createBlock($fileName, $clearSpaces, TplBlock $block=null)
    {
        if (null !== $block) {
            return $block;
        }

        $this->_factory = new TplFactory(dirname($fileName));
        return $this->_factory->fromFile($fileName, $clearSpaces);

    }//end _createBlock()


    /**
     * Creates a template object
     *
     * @param string   $fileName    The filename to work from
     * @param bool     $clearSpaces True if all empty lines should be deleted.
     * @param TplBlock $block       block
     */
    public function __construct($fileName, $clearSpaces=true, TplBlock $block=null)
    {
        $this->_block = $this->_createBlock($fileName, $clearSpaces, $block);

    }//end __construct()


    /**
     * Returns a chid block by block name
     *
     * @param string $blockName the name of the block
     *
     * @return TplBlock
     */
    public function __get($blockName)
    {
        return $this->_block->getChildBlock($blockName);

    }//end __get()


    /**
     * Assigns $value to $var.
     * The scope of the variable is always block, and children.
     *
     * @param string $var   The variable name to assign to
     * @param string $value The value to assign to the name
     * 
     * @return <type>
     */
    public function assign($var, $value)
    {
        return $this->_block->assign($var, $value);

    }//end assign()


    /**
     * Parses the tempalte
     *
     * @return string
     */
    public function parse()
    {
        return $this->_block->parse();

    }//end parse()


    /**
     * Returns the parsed template.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_block->flush();

    }//end __toString()


    /**
     * Returns the parsed template, and prints it out by default.
     *
     * @param bool $toPrint false to only return it.
     *
     * @return string
     */
    public function out($toPrint=true)
    {
        $this->_block->parse();
        $result = $this->__toString();
        if (true === $toPrint) {
            echo $result;
        }

        return $result;

    }//end out()


}//end class

?>