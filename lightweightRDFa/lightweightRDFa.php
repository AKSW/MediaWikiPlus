<?php

/**
 * Generate RDFa from Internal Links
 *
 * add the following line to LocalSettings.php
 * require_once( "$IP/extensions/lightweightRDFa/lightweightRDFa.php" );
 */

$wgExtensionCredits['other'][] = array(
    'name'            => 'LightweightRDFa',
    'url'             => 'http://askw.com',
    'description'     => 'lightweight RDFa extension',
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

$wgHooks['LinkBegin'][] = 'internalRDFaLinks';
$wgHooks['OutputPageBodyAttributes'][] = 'rdfaAbout';

function rdfaAbout( $out, $sk, &$bodyAttrs ) {
    //if ($out->isArticle()) {
        $bodyAttrs['about']='http://domain/path/' . $out->getPageTitle(); // domainname + path + articlename
        $bodyAttrs['xmlns:wiki']='http://domain/path/'; // domainname + path
    //}
    return true;
}

function internalRDFaLinks($skin, $target, &$text, &$customAttribs, &$query, &$options, &$ret) {

    $parts = explode('::', $target->mTextform);

    if (count($parts) == 2) {
        // We might have a semantic link (contains '::')
        // see http://semantic-mediawiki.org/wiki/Help:Properties_and_types#Turning_links_into_properties
        // TODO special cases:
        // title contains :: e.g. c++::operator -> use :prop::c++::operator
        // multiple properties?

        $newtitle = Title::newFromText($parts[1], $target->mNamespace);
        //check html text in case of link with no title
        if ($target->mTextform == $text)
            $text = $newtitle->mTextform;
            
        //remove whitespace, non-alpanumeric, to lowercase, to camel case, remove spaces
        $relAttr = preg_replace('/\s\s+/', ' ', $parts[0]);
        //remove non-alpanumeric... but allow unicode letters
        $relAttr = str_replace(' ', '',ucwords(strtolower($relAttr)));
        $customAttribs['rel'] = 'wiki:'.$relAttr;
        
        //It could be avoided if $target was by reference (&), then we could change $target and retun true;
        $ret = $skin->Link($newtitle,  $text, $customAttribs, $query, $options);
        return false;
    } else {
        // Non-semantic link pipe it through as is
        return true;
    }
}
