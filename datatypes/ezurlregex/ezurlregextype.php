<?php
/**
 * File contains eZURLRegexType class
 *
 * @copyright Copyright (C) 2010 eZ Systems AS & André R. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU GPLv2
 * @version //autogentag//
 * @author ar
 * @package extension
 * @subpackage ezurlregex
 */

/**
 * class eZURLRegexType ezurlregextype.php
 * ingroup eZDatatype
 * Extends eZURL datatype to add support for using regex to validate url.
*/
class eZURLRegexType extends eZURLType
{
    const DATA_TYPE_STRING = 'ezurlregex';

    /**
     * Identifier for regex match (key in regex[] from ezurlregex.ini)
     *
     * @var null|string
     */
    protected $urlRegexMatch = null;

    /**
     * Initializes with a url id and a description.
     */
    function eZURLRegexType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezi18n( 'kernel/classes/datatypes', 'URLRegex', 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
        $this->MaxLenValidator = new eZIntegerValidator();
    }

    /**
     * Validates the input and returns true if the input was
     * valid for this datatype.
     */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $attributeId = $contentObjectAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $base . '_ezurl_url_' . $attributeId )  &&
             $http->hasPostVariable( $base . '_ezurl_text_' . $attributeId ) )
        {
            $url = $http->PostVariable( $base . '_ezurl_url_' . $attributeId );
            $text = $http->PostVariable( $base . '_ezurl_text_' . $attributeId );
            if ( ( trim( $url ) !== '' ) )
            {
                if ( ( $urlMatch = $this->matchUrlRegex( $url ) ) !== false )
                {
                    $this->urlRegexMatch = $urlMatch;
                    // Remove all url-object links to this attribute.
                    eZURLObjectLink::removeURLlinkList( $attributeId, $contentObjectAttribute->attribute('version') );
                    return eZInputValidator::STATE_ACCEPTED;
                }
                else
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes', 'Url does not match!' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }
            else if ( !$contentObjectAttribute->validateIsRequired() )
            {
                return eZInputValidator::STATE_ACCEPTED;
            }
        }
        $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes', 'Input required.' ) );
        return eZInputValidator::STATE_INVALID;
    }

    /**
     * Match url agains regex in ezurlregex.ini and return match index or false if none
     *
     * @param string $url
     * @return false|string|int
     */
    protected function matchUrlRegex( $url )
    {
        $ini = eZINI::instance( 'ezurlregex.ini' );
        if ( $ini->hasVariable( 'UrlRegex', 'List' ) )
        {
            foreach( $ini->variable( 'UrlRegex', 'List' ) as $urlKey => $pattern )
            {
                if ( preg_match( $pattern, $url, $matches ) )
                {
                    return $urlKey . '|' . $matches[1];
                }
            }
        }
        else
        {
            eZDebug::writeWarning( 'ezurlregex.ini[UrlRegex]List[] is empty, returning false!', __METHOD__ );
        }
        return false;
    }

    /**
     *Fetches the http post var url input and stores it in the data instance.
     */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $attributeId = $contentObjectAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $base . '_ezurl_url_' . $attributeId )  &&
             $http->hasPostVariable( $base . '_ezurl_text_' . $attributeId ) )
        {
            $url = $http->postVariable( $base . '_ezurl_url_' . $attributeId );
            $text = str_replace( '|', '', $http->postVariable( $base . '_ezurl_text_' . $attributeId ) );

            if ( $this->urlRegexMatch !== null )
            {
                $text .= '|' . $this->urlRegexMatch;
            }

            $contentObjectAttribute->setAttribute( 'data_text', $text );

            $contentObjectAttribute->setContent( $url );
            return true;
        }
        return false;
    }

    /**
     * Returns the meta data used for storing search indeces.
     */
    function metaData( $contentObjectAttribute )
    {
        return $this->cleanDataText( $contentObjectAttribute );
    }

    /**
     * Returns the content of the url for use as a title
     */
    function title( $contentObjectAttribute, $name = null )
    {
        return $this->cleanDataText( $contentObjectAttribute );
    }

    /**
     *  Function to remove regex match data from data_text
     */
    protected function cleanDataText( eZContentObjectAttribute $contentObjectAttribute )
    {
        $string = $contentObjectAttribute->attribute( 'data_text' );
        if ( isset( $string[1] ) && strpos( $string, '|' ) )
        {
             $string = explode( '|', $string );
             return $string[0];
        }
        return $string;
    }
}

eZDataType::register( eZURLRegexType::DATA_TYPE_STRING, 'eZURLRegexType' );

?>
