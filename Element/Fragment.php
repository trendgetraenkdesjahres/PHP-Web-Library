<?php

namespace Element;

use DOMDocument;

/**
 * Class Fragment
 *
 * Represents an HTML DocumentFragment based on PHP DOMDocument for easier handling.
 */
class Fragment extends AbstractElement
{
    /**
     * Fragment constructor.
     *
     * @param string|Element ...$content The content, including sub-elements or strings.
     */
    final public function __construct(Element|string|null ...$content)
    {
        # init
        $this->dom = new DOMDocument();;

        # create fragment
        $this->node = $this->dom->createDocumentFragment();

        # add sub elements and strings
        $this->append_elements_to_node($content);
    }
}
