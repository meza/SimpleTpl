<?php
/**
 * TplFactory.php
 *
 * Holds the TplFactory class
 *
 * PHP Version: 5.2.10
 *
 * @category File
 * @package  SimpleTpl
 * @author   meza <meza@meza.hu>
 * @license  GPLv3 <http://www.gnu.org/licenses/>
 * @version  SVN: $Id: TplFactory.php 14 2009-12-04 21:21:41Z meza $
 * @link     http://www.meza.hu
 */

/**
 * The TplFactory class is responsible for creating the template blocks
 *
 * PHP Version: 5.2.10
 *
 * @category Class
 * @package  SimpleTpl
 * @author   meza <meza@meza.hu>
 * @license  GPLv3 <http://www.gnu.org/licenses/>
 * @link     http://www.meza.hu
 */
class TplFactory
{

    const BLOCKS = "/<!--begin:\s*(.*)-->(.*)<!--end:\s*\\1-->/is";
    const FILE   = "/{FILE \"(.*)\"}/";

    /**
     * @var string directory
     */
    private $_dir;


    /**
     * Constructs a factory
     *
     * @param string $dir The directory
     */
    public function __construct($dir)
    {
        $this->_dir = $dir;

    }//end __construct()


    /**
     * Calculates the path from the tempalte dir
     *
     * @param string $fileName The filename
     *
     * @return string The qualified filename
     */
    private function _qualify($fileName)
    {
        if ('./' === substr($fileName, 0, 2)) {
            $retval = $this->_dir.'/'.substr($fileName, 2);
            return $retval;
        }

        return $fileName;

    }//end _qualify()


    /**
     * Gets the $fileName's contents
     *
     * @param string $fileName The filename
     *
     * @return string/bool The file contents, os false if the file does not exist
     */
    protected function getFileContents($fileName)
    {
        $qualifiedFileName = $this->_qualify($fileName);
        if (false === file_exists($qualifiedFileName)) {
            return false;
        }

        return file_get_contents($qualifiedFileName);

    }//end getFileContents()


    /**
     * The callback being called for every file inclusion
     *
     * @param array $matches The token-filename pairs
     *
     * @return string the fullToken if file is nonexistent, the contents otherwise
     */
    private function _includeCallback(array $matches)
    {
        list($fullToken, $fileName) = $matches;
        $contents                   = $this->getFileContents($fileName);
        if (false === $contents) {
            return $fullToken;
        }

        return $contents;

    }//end _includeCallback()


    /**
     * Parse a $tplString for file inclusions
     *
     * @param string $tplString The string to parse
     *
     * @return string The template with the included files
     */
    public function includeFiles($tplString)
    {
        $callback = array(
                     $this,
                     '_includeCallback',
                    );
        return preg_replace_callback(self::FILE, $callback, $tplString);

    }//end includeFiles()


    /**
     * Creates a TplBlock from fileName
     *
     * @param string $fileName    The filename to parse
     * @param bool   $clearSpaces True to remove empty lines
     *
     * @throws Exception if the file is not found.
     *
     * @return TplBlock
     */
    public function fromFile($fileName, $clearSpaces=true)
    {
        $contents = $this->getFileContents($fileName);
        if (false === $contents) {
            throw new Exception(_('File not found: ').$fileName);
        }

        return $this->createBlock($contents, $clearSpaces);

    }//end fromFile()


    /**
     * Creates a TplBlock object, parses the $tplString
     *
     * @param string $tplString   The template string
     * @param bool   $clearSpaces True to remove empty lines
     *
     * @return TplBlock
     */
    public function createBlock($tplString, $clearSpaces=true)
    {
        $expandedTplString = $this->includeFiles($tplString);
        $len               = strlen($expandedTplString);
        if ($len > 7000) {
            $num = (int) (($len / 7000) * 100000);

            ini_set('pcre.backtrack_limit', (string) $num);
        }

        return $this->_build($expandedTplString, $clearSpaces);

    }//end createBlock()


    /**
     * Creates a TplBlock object, parses the $tplString
     *
     * @param string $tplString   The template string
     * @param bool   $clearSpaces True to remove empty lines
     *
     * @return TplBlock
     */
    private function _build($tplString, $clearSpaces)
    {
        $parsed   = $tplString;
        $children = array();

        preg_match_all(self::BLOCKS, $tplString, $blocks, PREG_SET_ORDER);

        foreach ($blocks as $block) {
            list($full, $name, $content) = $block;

             // TODO Instead of placeholders search & replace implement,
             // ome kind of logical segmentation of the raw template text.
            $parsed          = str_replace($full, '##'.$name.'##', $parsed);
            $children[$name] = $this->createBlock($content, $clearSpaces);
        }

        $retval = new TplBlock($children, $parsed, $clearSpaces);
        return $retval;

    }//end _build()


}//end class

?>