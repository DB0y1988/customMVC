jQuery(document).ready(function() {

  // Start the homepage slider
  jQuery('.carousel').carousel();

  // Render html content in the contenteditable editor
  var html = jQuery(".page-content").text();
  $(".page-content").html(html);


});
