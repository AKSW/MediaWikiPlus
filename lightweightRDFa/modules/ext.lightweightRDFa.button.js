
var addRDFaButton = function() {
  //add button code
  $( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
    'section':'main',
    'group':'insert',
    'tools': {
      'rdfa': {
        label: 'RDFa', // or use labelMsg for a localized label, see above
        type: 'button',
        icon: 'http://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Gnome-face-smile.svg/22px-Gnome-face-smile.svg.png',
        action: {
          type: 'encapsulate',
          options: {
            pre: ":)" // text to be inserted
          }
        }
      }
    }
  } );

};
 
/* Check if we are in edit mode and the required modules are available and then customize the toolbar */
if ( $.inArray( mw.config.get( 'wgAction' ), ['edit', 'submit'] ) !== -1 ) {
  mw.loader.using( 'ext.wikiEditor.toolbar', function () {
    $(document).ready( addRDFaButton );
  });
}


