<!-- Internship Project: CRM Map Visualization -->
<!-- Developed by İrem Kabaoğlu -->
<!-- Displays customer service cases on Google Maps using coordinates stored in MySQL -->
<!DOCTYPE html>
<html>
<head>
    <title>Kayıt Sonuçları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <style>
        html, body {
            height: 100%;
            margin:0;
        }
        #map{
            margin-top: 20px; 
        }
        .daterange-container {
            width: 80%; 
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="daterange-container">
    <input type="text" name="daterange" />
    <button type="button" id="btn-filter" class="btn btn-primary" >Filtrele</button>
    </div>
  
    <div id="map" style="height:100% ;width:100%;"></div>

    <div class="modal" id="case-modal" tabindex="-1">
        <div class="modal-dialog modal-xl  modal-body">
            <div class="modal-content " >
            <div class="modal-header" style="background-color: #FFD700;">
                <h5 class="modal-title" id="title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="case-detail">
                


            </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key" defer></script>
        
    <script>
        let map;
        let markers = [];

        async function initMap() {
            const mapCenter = { lat: 38.791396, lng: 35.500603 };
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 6,
                center: mapCenter,
            });
 
            var locations = getLocations();
 
            //placeMarker(map, locations);
        }
       
        function getLocations(){

            var locations = [
                <?php foreach ($coordinats as $coord): ?>
                    {
                        info: {
                            id: "<?= $coord['case_id'];?>",
                            title: "<?= $coord['ticketnumber'];?>"
                        },
                        lat: <?= $coord['latitude'] ?>,
                        long: <?= $coord['longitude'] ?>
                    },
                <?php endforeach; ?>
                 
            ]
                return locations;
        }
       
        
            async function placeMarker(map, data){
            var marker;  
            
            for (var i = 0; i < markers.length; i++) {
                markers[i].setMap(null);
            }
            markers = [];

            for (var i = 0; i < data.length; i++) {
                var location_data = data[i];
                var lat = location_data["lat"];
                var long = location_data["long"];
                var info = location_data["info"];
                var info_id = info["id"];
                var title = info["title"];
       
                var latlngset = new google.maps.LatLng(lat, long);
                marker = new google.maps.Marker({
                    map: map,
                    title: info_id,
                    position: latlngset
                });
               
                google.maps.event.addListener(marker, 'click', function(){
                    loadModalContent(this.title);
                });
                markers.push(marker);
            }
            
                var show_info_link = '<a href="#" class="map-link" data-case-id="'+ info_id + '">' + title + '</a>';
                var contentString =  '<div class="card text-center" style="width: 20rem;">' +
                                        '<div class="card-block">' +
                                            '<h4 class="card-title">'+ show_info_link + '</h4>' +
                                        '</div>' +
                                    '</div>';

                var infowindow = new google.maps.InfoWindow();

                // Marker'a tıklandığında modal açma olayı
                google.maps.event.addListener(marker, 'click', (function(marker,content,infowindow) {
               
                    return function() {
                       console.log('2 '+info_id);
                        loadModalContent(info_id);
                    
                    };
                })(marker, contentString, infowindow));
                
                markers.push(marker);
            } 
        

        function loadModalContent(case_id) {
            $.ajax({
                url: "<?= base_url('get-case-data') ?>",
                dataType: "html",
                type: 'POST',
                data: { case_id: case_id, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
                success: function(response) {
                    $("#case-detail").html(response);
                    $("#title").text(response.title); 
                    $("#case-modal").modal("show"); 
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error);
                }
            });
        }
       
        $(function() {
        var locale = {
            "format": "DD/MM/YYYY",
            "separator": " - ",
            "applyLabel": "Uygula",
            "cancelLabel": "Temizle",
            "fromLabel": "Başlangıç",
            "toLabel": "Bitiş",
            "customRangeLabel": "Özel Aralık",
            "weekLabel": "H",
            "daysOfWeek": ["Pzt", "Sal", "Çar", "Per", "Cum", "Cmt", "Paz"],
            "monthNames": ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"],
            "firstDay": 1
        };

        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            startDate: moment().startOf('month'),
            endDate: moment(),
            locale: locale
        }, function(start, end, label) {
            console.log("Seçilen tarih aralığı: " + start.format('YYYY-MM-DD') + ' ile ' + end.format('YYYY-MM-DD'));
        });
        });

        $('#btn-filter').on('click', function() {
            var selectedRange = $('input[name="daterange"]').val();
            console.log(selectedRange);
            $.ajax({
                url: "<?= base_url('get_record') ?>",
                dataType: "json",
                type: 'POST',
                data: { range: selectedRange,  '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
                success: function(response) {
                    //console.log(response);
                    var locations = getLocations2(response);
                    
                    placeMarker(map, locations);
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error);
                }
            });

        });


        function getLocations2(response) {
            var locations = [];
            response.forEach(function(coord) {
                var location = {
                    info: {
                        id: coord.case_id,
                        title: coord.ticketnumber
                    },
                    lat: parseFloat(coord.latitude),
                    long: parseFloat(coord.longitude)
                };
                locations.push(location);
                
            });
            return locations;
        }


    </script>

  




</body>
</html>