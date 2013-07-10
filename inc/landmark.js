function enableLandmark()
{
	navigator.geolocation.getCurrentPosition(function(position) {
	
	  var infopos = "  > Lat : "+position.coords.latitude +"  ";
	  infopos += "Lon: "+position.coords.longitude+"  ";
	  infopos += "Alt : "+position.coords.altitude +"  ";
	  $("#infolandmark").html(infopos);  
	  $('input[name=lf_land_lat]').val(position.coords.latitude);
	  $('input[name=lf_land_lon]').val(position.coords.longitude);
	  $('input[name=lf_land_alt]').val(position.coords.altitude);
	  $('textarea[name=lf_description]').val($('textarea[name=lf_description]').val()+" "+$('input[name=lf_url]').val());
	  $('input[name=tmp_landmark_url]').val($('input[name=lf_url]').val());
	  $('input[name=lf_url]').val("http://www.openstreetmap.org/?mlat="+position.coords.latitude+"&mlon="+position.coords.longitude+"&zoom=18&layers=Q");
	  
	});
}
function disableLandmark()
{
	$("#infolandmark").html("");
	$('input[name=lf_land_lat]').val("");
	$('input[name=lf_land_lon]').val("");
	$('input[name=lf_land_alt]').val("");
	$('input[name=lf_url]').val($('input[name=tmp_landmark_url]').val());
}
$(document).ready(function() {
		$('#landmark').change(function() {
		if($('#landmark').attr('checked'))
		{
			enableLandmark();
		}
		else
		{
			disableLandmark();
		}
	});
});