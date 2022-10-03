<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false&key=AIzaSyDu6OXrBUAEyFxAzY8yz7EGiJiM8AJ6V_Q"></script>
<script>
	
    
    $(document).ready(function() {
        
        var mapOptions = {
            zoom: 10, //level zoom
            center: {lat:-6.1750359, lng:106.827192}, //posisi tengah peta
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            disableDoubleClickZoom: true
        };
        
        $('.map').each(function() {
            var id = $(this).attr('id');
            if (!id) {
                id = 'map_' + uniqid();
            }
            
            $(this).attr('id', id);
            
            var map = new google.maps.Map(document.getElementById(id), mapOptions);	
            var inputLat = $($(this).attr('input-lat'));
            var inputLng = $($(this).attr('input-lng'));
            
            var lat = parseFloat(inputLat.val());
            var lng = parseFloat(inputLng.val());
            
            if (!lat || !lng) {
                lat = -6.1750359; inputLat.val(lat);
		        lng = 106.827192; inputLng.val(lng);
            }
            
            map.setOptions({center:{lat:lat, lng:lng}});
        
            var marker = new google.maps.Marker({
                position: {lat:lat, lng:lng}, 
                draggable:true,
                animation: google.maps.Animation.DROP,
                map: map
            });
            
            var setCenter = function(lat, lng)
            {
                map.setOptions({center:{lat:lat, lng:lng}});
                marker.setOptions({position:{lat:lat, lng:lng}});
                inputLat.val(lat);
                inputLng.val(lng);
            }

            google.maps.event.addListener(marker, 'dragend', function() {
                var lat = marker.position.lat();
                var lng = marker.position.lng();
                setCenter(lat, lng);
            });

            google.maps.event.addListener(map, 'dblclick', function(event) {
                var lat = event.latLng.lat();
                var lng = event.latLng.lng();
                setCenter(lat, lng);
            });

            inputLat.change(function() {
                var lat = parseFloat(inputLat.val());
                var lng = parseFloat(inputLng.val());
                setCenter(lat, lng);
            });
            inputLng.change(function() {
                var lat = parseFloat(inputLat.val());
                var lng = parseFloat(inputLng.val());
                setCenter(lat, lng);
            });
        });  
    });
</script>