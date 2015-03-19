<?php

namespace Sabre\DAV\Xml\Property;

use Sabre\DAV;
use Sabre\Xml\XmlSerializable;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;

/**
 * Represents {DAV:}lockdiscovery property.
 *
 * This property is defined here:
 * http://tools.ietf.org/html/rfc4918#section-15.8
 *
 * This property contains all the open locks on a given resource
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class LockDiscovery implements XmlSerializable {

    /**
     * locks
     *
     * @var \Sabre\DAV\Locks\LockInfo[]
     */
    public $locks;

    /**
     * Hides the {DAV:}lockroot element from the response.
     *
     * It was reported that showing the lockroot in the response can break
     * Office 2000 compatibility.
     *
     * @var bool
     */
    static public $hideLockRoot = false;

    /**
     * __construct
     *
     * @param \Sabre\DAV\Locks\LockInfo[] $locks
     */
    function __construct($locks) {

        $this->locks = $locks;

    }

    /**
     * The serialize method is called during xml writing.
     *
     * It should use the $writer argument to encode this object into XML.
     *
     * Important note: it is not needed to create the parent element. The
     * parent element is already created, and we only have to worry about
     * attributes, child elements and text (if any).
     *
     * Important note 2: If you are writing any new elements, you are also
     * responsible for closing them.
     *
     * @param Writer $writer
     * @return void
     */
    function xmlSerialize(Writer $writer) {

        foreach($this->locks as $lock) {

            $writer->startElement('{DAV:}activelock');

            $writer->startElement('{DAV:}lockscope');
            if ($lock->scope === 'shared') {
                $writer->writeElement('{DAV:}shared');
            } else {
                $writer->writeElement('{DAV:}exclusive');
            }

            $writer->endElement(); // {DAV:}lockscope

            $writer->startElement('{DAV:}locktype');
            $writer->writeElement('{DAV:}write');
            $writer->endElement(); // {DAV:}locktype

            if (!self::$hideLockRoot) {
                $writer->startElement('{DAV:}lockroot');
                $writer->writeElement('{DAV:}href', $writer->contextUri . $lock->uri);
                $writer->endElement(); // {DAV:}lockroot
            }
            $writer->writeElement('{DAV:}depth', ($lock->depth == DAV\Server::DEPTH_INFINITY?'infinity':$lock->depth));
            $writer->writeElement('{DAV:}timeout','Second-' . $lock->timeout);

            $writer->startElement('{DAV:}locktoken');
            $writer->writeElement('{DAV:}href', 'opaquelocktoken:' . $lock->token);
            $writer->endElement(); // {DAV:}locktoken

            $writer->writeElement('{DAV:}owner', $lock->owner);
            $writer->endElement(); // {DAV:}activelock

        }

    }

}

