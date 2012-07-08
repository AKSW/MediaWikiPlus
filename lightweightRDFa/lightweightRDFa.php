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

//HTML Namespaces

/* HTML5 
$wgHtml5 = true;
$wgHtml5Version='HTML+RDFa 1.0';
*/
/* Normal RDFa */
$wgDocType='-//W3C//DTD XHTML+RDFa 1.0//EN';
$wgDTD='http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd';


// Hooks
$wgHooks['LinkBegin'][] = 'internalRDFaLinks';
$wgHooks['OutputPageBodyAttributes'][] = 'rdfaAbout';
$wgHooks['BeforePageDisplay'][] = 'beforePageDisplay';

$rdfaResourceTemplate = array(
  'localBasePath' => dirname( __FILE__ ) . '/modules',
  'remoteExtPath' => 'lightweightRDFa/modules',
  'group' => 'ext.lightweightRDFa',
);
$wgResourceModules += array(
  'ext.lightweightRDFa.button' => $rdfaResourceTemplate + array(
    'scripts' => 'ext.lightweightRDFa.button.js',
//  'styles'  => 'ext.lightweightRDFa.WikiEditorRDFaButton.css',
    'messages' => array(
//      'vector-collapsiblenav-more',
    ),
    'dependencies' => array(
//TODO check dependencies
      'ext.wikiEditor',
    ),
  ),
);

//$wgOut->addModules( 'ext.lightweightRDFa' );

// prints the rdfaAbout attribute on the page <body> element
function rdfaAbout( $out, $sk, &$bodyAttrs ) {
  //TODO check what to do if not an article
  //if ($out->isArticle()) {
  global $wgScriptPath;
  global $wgServer;
  $title = Title::newFromText($out->getPageTitle());
  
  $bodyAttrs['about']=$title->getFullURL();
  $bodyAttrs['xmlns:prop']=$wgServer . $wgScriptPath . '/property#'; // domainname + path
  //}
  return true;
}

// hooks on the link before it is created as HTML
function internalRDFaLinks($skin, $target, &$text, &$customAttribs, &$query, &$options, &$ret) {

  //config
  $rdfaEscape = '^^'; //we need ':' but mediawiki escapes it before this hook
  $rdfaCreate = '::';
  $rdfaEscapeLn = strlen($rdfaEscape);
  $rdfaCreateLn = strlen($rdfaCreate);
  
  //current link
  $link_ref = $target->getText();
  $link_size = strlen($link_ref);
  $link_text = $text;

  //too young to rdfa
  if ($link_size <= $rdfaCreateLn || $link_size <= $rdfaEscapeLn)
    return true;

  //returning link, previously escaped
  //contains $rdfaCreate but we must ignore it
  if (isset($customAttribs['ignore'])) {
    unset($customAttribs['ignore']);
    return true;
  }

  //the new title if we embed rdfa or escape it
  $newTitle = '';
  //will hold the new link attributes
  $newAttribs = array();
  
  //escape link when it starts with $rdfaEscape (we already checked the strlen)
  if (substr($link_ref, 0, $rdfaEscapeLn) === $rdfaEscape) {
    //remove $rdfaEscape from $link_ref    
    $newtitle = Title::newFromText(substr($link_ref,$rdfaEscapeLn), $target->getNamespace());
    //because $skin->Link will call this recursively, set 'ignore' for next call
    $newAttribs['ignore'] = 'true';
  }
  else {
    $parts = explode($rdfaCreate, $link_ref);
    $count = count($parts);
  
    //normal link
    if ($count == 1)
      return true;
      
    //TODO check how we should handle multiple properties (for now support only single)
    if ($count > 2)
      //normal link 
      return true;

    //From now on we create RDFa...
    $newtitle = Title::newFromText($parts[1], $target->getNamespace());

    //remove whitespace, non-alpanumeric(but allow unicode letters), to lowercase, to camel case, remove spaces
    $relAttr = str_replace(' ', '',ucwords(strtolower(preg_replace('/\s\s+/', ' ', $parts[0]))));
    $newAttribs['rel'] = 'prop:'.$relAttr;
    $newAttribs['title'] = $relAttr;
  }
  
  //escaped or with RDFa  
  
  //check html text in case of link with no title (first letter might change case)
  if ($text == NULL || lcfirst($link_ref) === lcfirst($text))
      $text = $newtitle->getText();

  //It could be avoided if $target was by reference (&), then we could change $target and retun true;
  $ret = $skin->Link($newtitle,  $text, $newAttribs);//, $NULL, NULL);
  return false;
}

function beforePageDisplay( $out, $skin ) {
//  if ( $skin instanceof SkinVector ) {
			// Add modules for enabled features
//				if ( isset( $feature['modules'] ) && self::isEnabled( $name ) ) {
//   var_dump($out);
    $out->addModules('ext.lightweightRDFa');
    $out->addModules('ext.lightweightRDFa.button');
//				}
 // }
  return true;
}

/* for PHP Version < 5.3.0 */
if(function_exists('lcfirst') === false) {
  function lcfirst($str) {
    $str[0] = strtolower($str[0]);
      return $str;
  }
}

