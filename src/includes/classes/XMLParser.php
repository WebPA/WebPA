<?php
/**
 * Class  : XMLParser
 *
 * Creates the following general array structure for each element
 * <code>
 * parent-tag => array  ('tag' => array ('_attributes' => {attrs} ,
 *                     '_data'      => {data} ,
 *                     'sub-tag1'   => array (...) ,
 *                     'sub-tag2'   => array (...) ,
 *                     ...
 *                    )
 *            )
 *
 * XML element types supported
 * <tag />
 * Gives: {attrs} = null
 *          {data} = null
 *
 * <tag attr1="a" attr2="b" />
 * Gives: {attrs} = array ( 'attr1' => 'a', 'attr2' => 'b' )
 *          {data} = null
 *
 * <tag attr1="a">some value</tag>
 * Gives: {attrs} = array ( 'attr1' => 'a' )
 *          {data} = 'some value'
 *
 * etc...
 * </code>
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

class XMLParser
{
    // Public Vars
    public $xml_data = '';

    public $xml_array;

    // Private Vars
    private $_parser;            // reference to the XML parser object

    private $_parent;            // used by ->parse()

    private $_stack;             // used by ->parse()

    private $_last_opened_tag;   // used by ->parse()

    private $_cdata_tags = [];     // tags which should be handled as CDATA

    private $data;

    /**
     * CONSTRUCTOR for the XML parser
     */
    public function __construct()
    {
        $this->_init();
    }

    // /XMLParser()

    /**
     * DESTRUCTOR for the xml parser
     */
    public function destroy()
    {
        if (is_resource($this->_parser)) {
            xml_parser_free($this->_parser);
        }
        $this->_parser = null;
    }

    // /->destroy()

    /*
    * ================================================================================
    * Public Methods
    * ================================================================================
    */

    /**
     * function to set the data tags
     * @param string $tags
     */
    public function set_cdata_tags($tags)
    {
        $this->_cdata_tags = (array) $tags;
    }

    // /->set_cdata_tags()

    /**
     * function to clear
     */
    public function clear()
    {
        $this->_init();
    }

    // /->clear()

    /**
     * Parse the given XML document into a PHP array.
     *
     * @param string $xml_data XML document to parse
     *
     * @return array PHP array representing XML structure
     */
    public function parse($xml_data)
    {
        $this->_parser = xml_parser_create('');

        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_object($this->_parser, $this);
        xml_set_element_handler($this->_parser, '_tag_open', '_tag_close');
        xml_set_character_data_handler($this->_parser, '_tag_data');

        $this->xml_data = $xml_data;
        $this->xml_array = [];
        $this->_stack = [];
        $this->_parent =& $this->xml_array;

        $this->data = '';
        $this->_last_opened_tag = null;
        $this->_cdata_tags = (array) $this->_cdata_tags;


        return xml_parse($this->_parser, $this->xml_data, true) ? $this->xml_array : null;
    }

    /**
     * Generate an xml document from the given array
     *
     * @param array $data xml array structure
     * @return string  xml document
     */
    public function generate_xml(&$data, $level = 0, $prior_key = null)
    {
        if ($level == 0) {
            ob_start();
            $prior_key = null;
            echo "<?xml version=\"1.0\" ?>\n";
        }

        foreach ($data as $key => $value) {
            $key = (string) $key;
            // If the array key is NOT attributes or data, then it might be more tags (if it is attributes/data, just ignore it)
            if (($key != '_attributes') && ($key != '_data')) {
                // This tag may contain others, so process them
                if ((is_array($value)) && (array_key_exists(0, $value))) {
                    $this->generate_xml($value, $level, $key);
                } else {
                    $tag = $prior_key ? $prior_key : $key;
                    $tab_indent = str_repeat("\t", $level);
                    echo "{$tab_indent}<{$tag}";

                    // Get a list of all the sub-arrays
                    $sub_array = array_flip(array_keys((array) $value));

                    // If the tag has attributes, show them
                    // For backwards compatibility reasons, we do a check on if the sub_array is empty. Previously this was an in_array() check which always returned true
                    if (!empty($sub_array) && !empty($value['_attributes'])) {
                        foreach ($value['_attributes'] as $attr_name => $attr_value) {
                            echo ' ' . $attr_name . '="' . htmlspecialchars($attr_value) . '"';
                        }
                    }

                    // Get the tag's value
                    $tag_data = (array_key_exists('_data', (array) $value)) ? $value['_data'] : null;

                    // Remove the attributes/data from the sub-array list
                    unset($sub_array['_attributes'], $sub_array['_data']);
                    $sub_array = array_flip(array_values($sub_array));

                    // If there are still sub-arrays then they're probably tags, so process them
                    if (count($sub_array) > 0) {
                        echo ">\n";  // ->generate_xml returns the xml by reference, so need to do this in two halves
                        echo $this->generate_xml($value, $level + 1) . "{$tab_indent}</$tag>\n";
                    } else {  // need to process the tag value
                        // If the tag has no value, show an empty tag
                        if (!$tag_data) {
                            echo " />\n";
                        } else {
                            // If this tag has been highlighted as a CDATA tag, use CDATA
                            if (in_array($tag, $this->_cdata_tags)) {
                                echo "><![CDATA[{$tag_data}]]></$tag>\n";
                            } else {  // Else, just use the value
                                echo '>' . htmlspecialchars($tag_data) . "</$tag>\n";
                            }
                        }
                    }
                }
            }
        }

        if ($level == 0) {
            $str = &ob_get_contents();

            ob_end_clean();

            return $str;
        }
    }

    // ->generate_xml()


    /*
    * ================================================================================
    * Private Methods
    * ================================================================================
    */

    /*
    * --------------------------------------------------------------------------------
    * xml_parser event handlers
    * --------------------------------------------------------------------------------
    */

    /**
     * tag-open event handler
     * @param string $parser
     * @param string $tag
     * @param string $attributes
     */
    public function _tag_open($parser, $tag, $attributes)
    {
        $this->data = '';
        $this->_last_opened_tag = $tag;

        // If this tag already exists in the array
        if ((is_array($this->_parent)) && (array_key_exists($tag, $this->_parent))) {
            // If there's already an array of these tags, this will just be added to the array
            if (is_array($this->_parent[$tag]) and array_key_exists(0, $this->_parent[$tag])) {
                $key = $this->_count_numeric_items($this->_parent[$tag]);
            } else {
                // Need to create the array of tags
                $arr = [&$this->_parent[$tag]];
                $this->_parent[$tag] = &$arr;
                $key = 1;
            }
            $this->_parent = &$this->_parent[$tag];
        } else {
            $key = $tag;
        }

        $this->_parent[$key]['_attributes'] = (!empty($attributes)) ? $attributes : null;
        $this->_parent[$key]['_data'] = null;

        $this->_parent = &$this->_parent[$key];
        $this->_stack[] = &$this->_parent;
    }

    /**
     * tag-data event handler
     * @param string $parser
     * @param string $data
     */
    public function _tag_data($parser, $data)
    {
        //you don't need to store whitespace in between tags
        if ($this->_last_opened_tag != null) {
            $this->data .= $data;
        }
    }

    // /->_tag_data()

    /*
    * tag-close event handler
    * @param string $parser
    * @param string $tag
    */
    public function _tag_close($parser, $tag)
    {
        if ($this->_last_opened_tag == $tag) {
            $this->_parent['_data'] = $this->data;
            $this->_last_opened_tag = null;
        }
        array_pop($this->_stack);
        if ($this->_stack) {
            $this->_parent = &$this->_stack[count($this->_stack) - 1];
        }
    }

    // /->_tag_close()

    /*
    * --------------------------------------------------------------------------------
    * Other methods
    * --------------------------------------------------------------------------------
    */

    /**
     * count the numeric items
     * @param array $array
     * @return boolean
     */
    public function _count_numeric_items(&$array)
    {
        return is_array($array) ? count(array_filter(array_keys($array), 'is_numeric')) : 0;
    }

    // /->_count_numeric_items()
    
    public function _init()
    {
        $this->xml_data = '';
        $this->xml_array = null;

        $this->_parent = null;
        $this->_stack = null;
        $this->_last_opened_tag = null;

        $this->_cdata_tags = [];
    }

    // /->_init()
}// /class: XMLParser
