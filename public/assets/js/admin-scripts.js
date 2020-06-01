jQuery(document).ready(function() {

  jQuery(".submit-page").click(function(){
    var content = $(".content.editor").html();
    $("#content-textarea").html(content);
  });

  jQuery(".media-library .copy-link a").click(function(){
    var src = $(this).attr('id');
    var dummy = $('<input>').val(src).appendTo('body').select()
    document.execCommand("copy");
    alert("Copied the text: " + src);
  });

  // Render html content in the contenteditable editor
  var html = jQuery(".editor").text();
  $(".editor").html(html);

  // Insert html content from editable div into hidden textarea
  jQuery(".page-form").submit(function(){
    var content = $(".editor").html();
    $("#content").html(content);
  });

  jQuery("#remove-image a").click(function(){
    $(".featured-image").empty();
    $(".hidden-media-fields").append("<input type='hidden' name='featured_image' value=''>");
    $(".featured-image-wrap td").css("padding", "0px");
  });

  jQuery("#checkbox-confirm").change(function(){
    $(".close-btn").attr("disabled", ! $(this).is(':checked'));
  });

  jQuery(".delete-page-btn").click(function() {
    var id = $(this).attr("id");
    var bool = confirm("Are you sure you want to delete this page?");
    if(bool) {
      window.location.href = "/pages/" + id + "/delete-page";
    }
    else {
      window.location.href = "/pages/" + id + "/edit-page";
    }
  });

  jQuery('#myModal').on('shown.bs.modal', function () {
    jQuery('#myInput').trigger('focus');
  });

  jQuery(".media-images").click(function(){
    $(".img-thumbnail").removeClass("img-thumbnail");
    $(this).toggleClass("img-thumbnail");
  });

  jQuery(".set-image").click(function() {
    var src = jQuery(".img-thumbnail").attr("src");
    $(".hidden-media-fields").append("<input type='hidden' name='featured_image' value='" + src + "'>");
    $(".close-modal-window").trigger("click");

    var image = jQuery(".featured-image-holder .img-thumbnail").attr("src");

    if(image == "") {
      $(".featured-image-holder").append("<img src='" + src + "' class='img-thumbnail mt-3 img-fluid'>");
    }
    else {
      $(".featured-image-holder").empty();
      $(".featured-image-holder").append("<img src='" + src + "' class='img-thumbnail mt-3 img-fluid'>");
    }

  });

  var max_fields      = 100; //maximum input boxes allowed
  var wrapper         = jQuery(".recipe-steps-list"); //Fields wrapper
  var add_button      = jQuery(".btn-increment-steps"); //Add button ID

  var x = 1; //initlal text box count

  jQuery(add_button).click(function(e){ //on add input button click

      e.preventDefault();

      if(x < max_fields){ //max input box allowed

          x++; //text box increment

          jQuery(wrapper).append('<li class="py-1"><table width="100%"><tr><td width="90%"><input type="text" name="steps[]" class="form-control" /></td><td width="10%"><a href="#" class="remove_field">Remove</a></td></tr></table></li>'); //add input box
      }
  });

  // Handles the set available dates repeater field
  jQuery(wrapper).on("click",".remove_field", function(e){
      e.preventDefault(); jQuery(this).parents('li').remove(); x--;
  })

});
