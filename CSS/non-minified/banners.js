$(document).ready(function()
{
    code();
});
//*******************************************************************************************
function code(element)
{
    if($('select[name=colorScheme]').length <= 0)
    return;
    var theme = $('select[name=colorScheme]').val();
    var imageSource = $('input[name=image]').val() +  theme + '.png';
    document.getElementById("imagePreview").src = imageSource;
    $('#htmlCode').val('<a href="' + $('input[name=link]').val() + '" target="_blank"><img src="' + imageSource + '" border="0" width="468" height="60" alt=""/></a>');
    $('#bbCode').val('[url=' + $('input[name=link]').val() + '][img]' + imageSource + '[/img][/url]');
}

