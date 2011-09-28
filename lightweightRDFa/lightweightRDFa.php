<?php

/**
 * Generate RDFa from Internal Links
 *
 * add the following line to LocalSettings.php
 * require_once( "$IP/extensions/SemanticInternalLink/SemanticInternalLink.php" );
 */

$wgExtensionCredits['other'][] = array(
    'name'            => 'SemanticInternalLink',
    'url'             => 'http://example.com',
    'description'     => 'Generate RDFa in internal links',
    'version'         => '0.0.0.0.1'
);

//testing global variables for namespace ...
//if you set html5 cannot set namespaces
$wgHtml5 = false;
$wgMimeType = 'text/xml';
//$wgHtml5Version='HTML+RDFa 1.0';
$wgDocType='-//W3C//DTD XHTML+RDFa 1.0//EN';
$wgDTD='http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd';
$wgAllowRdfaAttributes = true;
$wgXhtmlNamespaces['foaf'] = 'http://xmlns.com/foaf/0.1/';

//$wgXhtmlNamespaces = array('rdfa');

$wgHooks['LinkBegin'][] = 'semanticInternalLinks';
$wgHooks['OutputPageBodyAttributes'][] = 'rdfaAbout';

function semanticInternalLinks($skin, $target, &$text, &$customAttribs, &$query, &$options, &$ret) {

    $parts = explode('::', $target->mTextform);

    if (count($parts) == 2) {
        // We might have a semantic link (contains '::')
        // see http://semantic-mediawiki.org/wiki/Help:Properties_and_types#Turning_links_into_properties
        // special cases:
        // title contains :: e.g. c++::operator -> use :prop::c++::operator
        // multiple properties?

        $newtitle = Title::newFromText($parts[1], $target->mNamespace);
        $customAttribs['rel'] = $parts[0];
        $wgXhtmlNamespaces[$parts[0]] = $parts[0];

        //This is somehow calling itself since the hook is called form Link() function but the second time this function goes to 'else'
        //It could be avoided if $target was by reference (&), then we could change $target and retun true;
        $ret = $skin->Link($newtitle,  $text, $customAttribs, $query, $options);
        return false;
    } else {
        // Non-semantic link pipe it through as is
        return true;
    }
}

function rdfaAbout( $out, $sk, &$bodyAttrs ) {
    if ($out->isArticle()) {
        $bodyAttrs['about']='http://...' . $out->getPageTitle();
        $bodyAttrs['xmlns:ns1']='http://ns1';
    }
    
    return true;

}
