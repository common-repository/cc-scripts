jQuery( document ).ready( function( $ ) {
  group = "input:checkbox[class]";

  $( group ).on('change', function( event ) {
    var checked = $( this ).prop( "checked" );

    $( "input:checkbox[class='" + $( this ).attr( "class" ) + "']" ).prop( "checked", false );
    $( this ).prop( "checked", checked );
  } );
} );