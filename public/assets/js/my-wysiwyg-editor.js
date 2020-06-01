jQuery(document).ready(function() {

  var colorPalette = ['000000', '333333', '495057', '007bff', '0066FF', '0000CC', '00CC00', 'CC0000', 'dc3545', 'FFFFFF'];

  var forePalette = $('.fore-palette');
  var backPalette = $('.back-palette');

  for (var i = 0; i < colorPalette.length; i++) {
    forePalette.append('<a href="javascript:void(0);" title="#' + colorPalette[i] + '" data-command="forecolor" data-value="' + '#' + colorPalette[i] + '" style="background-color:' + '#' + colorPalette[i] + ';" class="palette-item"></a>');
    backPalette.append('<a href="javascript:void(0);" data-command="backcolor" data-value="' + '#' + colorPalette[i] + '" style="background-color:' + '#' + colorPalette[i] + ';" class="palette-item"></a>');
  }

  jQuery('.toolbar .toolbar-group a').click(function(e) {

    var command = $(this).data('command');

    if (command == 'h1' || command == 'h2' || command == "h3" || command == 'p') {
      document.execCommand('formatBlock', false, command);
    }
    if (command == 'createlink' || command == 'insertimage') {
      url = prompt('Enter the link here: ', 'http:\/\/');
      document.execCommand($(this).data('command'), false, url);
    }
    else document.execCommand($(this).data('command'), false, null);
  });

  jQuery('.fore-palette a').click(function(f) {
      document.execCommand($(this).data('command'), false, $(this).data('value'));
  });

  jQuery('.back-palette a').click(function(f) {
      document.execCommand($(this).data('command'), false, $(this).data('value'));
  });

});
