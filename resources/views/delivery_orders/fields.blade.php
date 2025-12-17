<!-- Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('date', __('Date')) !!}<span class="asterisk"> *</span>
    {!! Form::date('date', isset($deliveryOrder) ? $deliveryOrder->getRawOriginal('date') : \Carbon\Carbon::now()->format('Y-m-d'), [
        'class' => 'form-control', 
        'id' => 'date', 
        'autofocus',
    ]) !!}
</div>

<!-- Company Id Field (Branch) -->
<div class="form-group col-sm-6">
    {!! Form::label('company', 'Branch:') !!}<span class="asterisk"> *</span>
    {!! Form::select('company_id', $company, null, ['class' => 'form-control', 'placeholder' => 'Pick a Branch...']) !!}
</div>

<!-- Dono Field -->
<div class="form-group col-sm-6">
    {!! Form::label('dono', 'Project Number:') !!}<span class="asterisk"> *</span>
    {!! Form::text('dono', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', 'Customer:') !!}&nbsp;<a href="#" id="info_customer_id" class="pe-auto"><i class="nav-icon icon-info"></i></a>&nbsp;<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customers, null, ['class' => 'form-control selectpicker', 'placeholder' => 'Pick a Customer...','data-live-search'=>'true']) !!}
</div>

<!-- Google Places Destination Field -->
<div class="form-group col-sm-7">
    {!! Form::label('destination', 'Destination:') !!}<span class="asterisk"> *</span>
    
    <!-- Toggle between Google Places and Manual Coordinates -->
    <div class="btn-group btn-group-toggle mb-3" id="destination-toggle">
        <button type="button" class="btn btn-outline-primary active" data-value="google">
            Google Places Search
        </button>
        <button type="button" class="btn btn-outline-secondary" data-value="manual">
            Manual Coordinates
        </button>
    </div>

    <!-- Google Places Search (Visible by default) -->
    <div id="google-places-section">
        {!! Form::text('destination_search', old('destination_search', $deliveryOrder->place_name ?? $deliveryOrder->place_address ?? null), [
            'class' => 'form-control',
            'id' => 'destination_search',
            'placeholder' => 'Start typing destination name, address...',
            'autocomplete' => 'off'
        ]) !!}
        <small class="form-text text-muted">Search for a location using Google Places</small>
    </div>

    <!-- Manual Coordinates Section (Hidden by default) -->
    <div id="manual-coordinates-section" style="display: none;">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('manual_place_name', 'Location Name:') !!}
                    {!! Form::text('manual_place_name', isset($deliveryOrder) ? $deliveryOrder->place_name : null, [
                        'class' => 'form-control',
                        'id' => 'manual_place_name',
                        'placeholder' => 'Enter location name'
                    ]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('manual_place_address', 'Location Address:') !!}
                    {!! Form::text('manual_place_address', isset($deliveryOrder) ? $deliveryOrder->place_address : null, [
                        'class' => 'form-control',
                        'id' => 'manual_place_address',
                        'placeholder' => 'Enter full address'
                    ]) !!}
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('manual_place_latitude', 'Latitude:') !!}
                    {!! Form::text('manual_place_latitude', isset($deliveryOrder) ? $deliveryOrder->place_latitude : null, [
                        'class' => 'form-control',
                        'id' => 'manual_place_latitude',
                        'placeholder' => 'E.g., 3.139003',
                        'step' => 'any'
                    ]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('manual_place_longitude', 'Longitude:') !!}
                    {!! Form::text('manual_place_longitude', isset($deliveryOrder) ? $deliveryOrder->place_longitude : null, [
                        'class' => 'form-control',
                        'id' => 'manual_place_longitude',
                        'placeholder' => 'E.g., 101.686855',
                        'step' => 'any'
                    ]) !!}
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <button type="button" class="btn btn-sm btn-primary" id="update-manual-location">
                <i class="fa fa-map-marker-alt"></i> Update Location on Map
            </button>
            <small class="form-text text-muted">Enter coordinates and click to update the map</small>
        </div>
    </div>
    
    <!-- Hidden fields to store final destination data (will be populated by JavaScript) -->
    {!! Form::hidden('place_name', isset($deliveryOrder) ? $deliveryOrder->place_name : null, ['id' => 'place_name']) !!}
    {!! Form::hidden('place_address', isset($deliveryOrder) ? $deliveryOrder->place_address : null, ['id' => 'place_address']) !!}
    {!! Form::hidden('place_latitude', isset($deliveryOrder) ? $deliveryOrder->place_latitude : null, ['id' => 'place_latitude']) !!}
    {!! Form::hidden('place_longitude', isset($deliveryOrder) ? $deliveryOrder->place_longitude : null, ['id' => 'place_longitude']) !!}
    {!! Form::hidden('google_place_id', isset($deliveryOrder) ? $deliveryOrder->google_place_id : null, ['id' => 'google_place_id']) !!}
    {!! Form::hidden('destinate_id', isset($deliveryOrder) ? $deliveryOrder->destinate_id : null, ['id' => 'destinate_id']) !!}
    {!! Form::hidden('is_manual_coordinates', 0, ['id' => 'is_manual_coordinates']) !!}
</div>

<!-- Display selected location details -->
<div id="selected-location-details" class="mt-3 p-3 border rounded" style="{{ (isset($deliveryOrder) && $deliveryOrder->place_latitude && $deliveryOrder->place_longitude) ? 'display: block;' : 'display: none;' }};">
    <div class="row">
        <div class="col-md-6">
            <h6>📍 Selected Location:</h6>
            <p><strong>Name:</strong> <span id="display-name">{{ isset($deliveryOrder) ? ($deliveryOrder->place_name ?? 'N/A') : 'N/A' }}</span></p>
            <p><strong>Address:</strong> <span id="display-address">{{ isset($deliveryOrder) ? ($deliveryOrder->place_address ?? 'N/A') : 'N/A' }}</span></p>
            <p><strong>Coordinates:</strong> <span id="display-coordinates">
                @if(isset($deliveryOrder) && $deliveryOrder->place_latitude && $deliveryOrder->place_longitude)
                    {{ number_format($deliveryOrder->place_latitude, 6) }}, {{ number_format($deliveryOrder->place_longitude, 6) }}
                @else
                    N/A
                @endif
            </span></p>
            <p><strong>Source:</strong> <span id="display-source">{{ isset($deliveryOrder) && $deliveryOrder->google_place_id ? 'Google Places' : 'Manual Coordinates' }}</span></p>
        </div>
        <div class="col-md-6">
            <h6>🗺️ Location Map:</h6>
            <div id="location-map" style="height: 200px; width: 100%; border-radius: 4px; border: 1px solid #ddd;"></div>
            <small class="form-text text-muted">Interactive map showing the exact location</small>
        </div>
    </div>
</div>

<!-- Item Id Field -->
<div class="form-group col-sm-6">
    <p></p>
    {!! Form::label('productItems', 'Product:') !!}<span class="asterisk"> *</span>
    {!! Form::select('product_id', $productItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Product...']) !!}
</div>

<!-- Total Order Field -->
<div class="form-group col-sm-6">
    {!! Form::label('total_order', 'Total Order:') !!}
    {!! Form::text('total_order', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Progress Total Field -->
<!-- <div class="form-group col-sm-6">
    {!! Form::label('progress_total', 'Progress Total:') !!}
    {!! Form::text('progress_total', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div> -->

<!-- Progress Total Field -->
<div class="form-group col-sm-6">
    {!! Form::label('strength_at', 'Strength At 28 days:') !!}
    {!! Form::text('strength_at', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Progress Total Field -->
<div class="form-group col-sm-6">
    {!! Form::label('slump', 'Specific Slump:') !!}
    {!! Form::text('slump', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Status Field -->
@if(isset($deliveryOrder))
    <div class="form-group col-sm-6">
        {!! Form::label('status', 'Status:') !!}<span class="asterisk"> *</span>
        @if($deliveryOrder->status == 0)
            {{ Form::select('status', $status, null, ['class' => 'form-control', 'readonly' => 'readonly', 'disabled' => 'disabled']) }}
            {!! Form::hidden('status', $deliveryOrder->status) !!}
            <small class="text-muted">Status cannot be changed while pending approval</small>
        @else
            {{ Form::select('status', $status, null, ['class' => 'form-control']) }}
        @endif
    </div>
@else
    <!-- Hide status field when creating new DO and set default value -->
    {!! Form::hidden('status', 0) !!}
@endif

<!-- Remark Field -->
<div class="form-group col-sm-6">
    {!! Form::label('remark', 'Remark:') !!}
    {!! Form::text('remark', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('deliveryOrders.index') }}" class="btn btn-secondary">Cancel</a>
</div>

@push('styles')
<style>
    /* Google Places dropdown styling */
    .pac-container {
        background-color: #fff;
        z-index: 1051 !important;
        border-radius: 4px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        font-family: inherit;
    }

    .pac-item {
        padding: 10px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        font-size: 14px;
    }

    .pac-item:hover {
        background-color: #f8f9fa;
    }

    .pac-item-query {
        font-size: 14px;
        color: #333;
        font-weight: bold;
    }

    #selected-location-details {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }

    #location-map {
        min-height: 200px;
        background-color: #f8f9fa;
    }

    .asterisk {
        color: red;
    }

    .selectpicker {
        width: 100%;
    }

    /* Map controls styling */
    .gm-style .gm-style-iw-c {
        border-radius: 8px;
        padding: 12px;
    }

    .gm-style .gm-style-iw-t::after {
        background: #007bff;
    }
</style>
@endpush

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
        $(document).ready(function () {
            HideLoad();
            $('.form-group:has(input[name="dono"])').hide();

            const companyPrefixes = {};
            @foreach($companies as $company)
                companyPrefixes[{{ $company->id }}] = '{{ $company->do_prefix }}';
            @endforeach
            
            // Function to get next DO number via AJAX
            function getNextDONumber(companyId) {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: '{{ route("deliveryOrders.getNextDONumber") }}',
                        method: 'GET',
                        data: {
                            company_id: companyId
                        },
                        success: function(response) {
                            if (response.success) {
                                resolve(response.do_number);
                            } else {
                                reject(response.message);
                            }
                        },
                        error: function(xhr) {
                            console.error('Error getting DO number:', xhr.responseText);
                            reject('Error generating DO number');
                        }
                    });
                });
            }

            // Check if we're in edit mode and company is already selected
            function checkEditMode() {
                const companyId = $('select[name="company_id"]').val();
                const donoInput = $('input[name="dono"]');
                const donoFieldGroup = $('.form-group:has(input[name="dono"])');
                
                // If company is selected (edit mode), show DO field and populate data
                if (companyId) {
                    donoFieldGroup.show();
                    
                    // If DO number is empty, generate one
                    if (!donoInput.val() || donoInput.val() === '') {
                        donoInput.val('Generating...');
                        
                        getNextDONumber(companyId)
                            .then(doNumber => {
                                donoInput.val(doNumber);
                            })
                            .catch(error => {
                                const currentPrefix = companyPrefixes[companyId];
                                donoInput.val(currentPrefix + '-');
                                console.error('AJAX failed, using prefix only:', error);
                            });
                    }
                }
            }
            
            // Run check on page load for edit mode
            checkEditMode();
            
            // Auto-fill DO number when company is selected
            $(document).on('change', 'select[name="company_id"]', function() {
                const companyId = $(this).val();
                const donoInput = $('input[name="dono"]');
                const donoFieldGroup = $('.form-group:has(input[name="dono"])');

                if (companyId && companyPrefixes[companyId]) {
                    // Only auto-fill if the field is empty or contains the current prefix
                    donoFieldGroup.show();

                    const currentValue = donoInput.val();
                    const currentPrefix = companyPrefixes[companyId];
                    
                    if (!currentValue || currentValue.startsWith(currentPrefix)) {
                        // Show loading
                        donoInput.val('Generating...');
                        
                        // Get sequential DO number from server
                        getNextDONumber(companyId)
                            .then(doNumber => {
                                donoInput.val(doNumber);
                            })
                            .catch(error => {
                                // If AJAX fails, just show the prefix
                                donoInput.val(currentPrefix + '-');
                                console.error('AJAX failed, using prefix only:', error);
                            });
                    }
                }
            });
        });

        function getInputCode(t,v){
            var label = '<label for="dono">'+t+'</label>';
            var input = '<input class="form-control" type="text" disabled value="'+v+'">';
            return label + input;
        }

        function getTableCode(data)
        {
            var tbl  = document.createElement("table");
            tbl.className = "table table-sm table-striped";
            var tr = tbl.insertRow(-1);
            $.each( data[0], function( key, value ) {
                var td = tr.insertCell();
                td.appendChild(document.createTextNode(key.charAt(0).toUpperCase() + key.slice(1)));
            });
            for (var i = 0; i < data.length; ++i)
            {
                var tr = tbl.insertRow();
                $.each( data[i], function( key, value ) {
                    var td = tr.insertCell();
                    td.appendChild(document.createTextNode(value.toString()));
                });
            }
            return tbl;
        }
    </script>

    <!-- Load Google Maps JavaScript API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBP7mUubx-qS7U0V4WUW6u9jIWp3NNCIsE&libraries=places&callback=initAutocomplete" async defer></script>

    <script>
    // Global variables for map and marker
    let map;
    let marker;
    let autocomplete;
    let isManualMode = false;

    // Initialize Google Places Autocomplete
    function initAutocomplete() {
        console.log('🔄 Initializing Google Places Autocomplete');

        // Check if there's existing destination data on page load
        let placeName = $('#place_name').val();
        let placeAddress = $('#place_address').val();
        let lat = $('#place_latitude').val();
        let lng = $('#place_longitude').val();

        console.log('📄 Page load destination data:', { placeName, placeAddress, lat, lng });

        // Only initialize map if ALL required fields have values
        if (placeName && placeAddress && lat && lng) {
            console.log('🎯 Found existing destination data, displaying map');
            let place = {
                name: placeName,
                formatted_address: placeAddress,
                geometry: {
                    location: {
                        lat: function() { return parseFloat(lat); },
                        lng: function() { return parseFloat(lng); }
                    }
                }
            };

            // Display details & map
            displaySelectedLocation(place);
            initMap(parseFloat(lat), parseFloat(lng), placeName, placeAddress);
        } else {
            console.log('❌ No existing destination data found');
            // Hide location details if no complete destination data
            hideLocationDetails();
        }

        var input = document.getElementById('destination_search');
        
        if (!input) {
            console.error('❌ Destination search input not found!');
            return;
        }
        
        // Create autocomplete instance
        autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['establishment', 'geocode'],
            fields: ['name', 'formatted_address', 'geometry', 'place_id', 'types']
        });
        
        // When a place is selected
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            
            if (!place.geometry) {
                alert("No details available for this location: '" + place.name + "'");
                return;
            }
            
            console.log('📍 Google Places selected:', place);
            
            // Populate hidden fields with Google Places data
            updateHiddenFields(
                place.name || '',
                place.formatted_address || '',
                place.geometry.location.lat(),
                place.geometry.location.lng(),
                place.place_id,
                'google_' + place.place_id.substring(0, 10),
                false // Not manual coordinates
            );
            
            // Display selected location details to user
            displaySelectedLocation(place, 'Google Places');
            
            // Initialize and display the map
            initMap(place.geometry.location.lat(), place.geometry.location.lng(), place.name, place.formatted_address);
        });

        // Initialize destination method toggle
        initDestinationToggle();

        console.log('✅ Google Places Autocomplete initialized');
    }

    // Initialize destination method toggle
    function initDestinationToggle() {
        const toggleButtons = document.querySelectorAll('#destination-toggle button');
        const googleSection = document.getElementById('google-places-section');
        const manualSection = document.getElementById('manual-coordinates-section');
        
        if (!toggleButtons.length) {
            console.error('Destination toggle buttons not found');
            return;
        }
        
        // Add click handlers to toggle buttons
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                
                // Update button states
                toggleButtons.forEach(btn => {
                    btn.classList.remove('active', 'btn-primary');
                    btn.classList.add('btn-outline-secondary');
                });
                
                this.classList.remove('btn-outline-secondary');
                this.classList.add('active', 'btn-primary');
                
                // Show/hide sections
                if (value === 'google') {
                    googleSection.style.display = 'block';
                    manualSection.style.display = 'none';
                    isManualMode = false;
                    document.getElementById('is_manual_coordinates').value = 0;
                    
                    // Clear manual fields
                    document.getElementById('manual_place_name').value = '';
                    document.getElementById('manual_place_address').value = '';
                    document.getElementById('manual_place_latitude').value = '';
                    document.getElementById('manual_place_longitude').value = '';
                } else {
                    googleSection.style.display = 'none';
                    manualSection.style.display = 'block';
                    isManualMode = true;
                    document.getElementById('is_manual_coordinates').value = 1;
                    
                    // Copy existing data to manual fields if available
                    copyToManualFields();
                }
            });
        });
        
        // Set initial state based on existing data
        const hasGooglePlaceId = document.getElementById('google_place_id').value;
        if (!hasGooglePlaceId) {
            // Switch to manual mode if no Google Place ID
            const manualButton = document.querySelector('#destination-toggle button[data-value="manual"]');
            if (manualButton) {
                // Trigger click on manual button
                manualButton.click();
            }
        }
    }

    // Copy existing data to manual fields
    function copyToManualFields() {
        const placeName = document.getElementById('place_name').value;
        const placeAddress = document.getElementById('place_address').value;
        const placeLat = document.getElementById('place_latitude').value;
        const placeLng = document.getElementById('place_longitude').value;
        
        if (placeName) document.getElementById('manual_place_name').value = placeName;
        if (placeAddress) document.getElementById('manual_place_address').value = placeAddress;
        if (placeLat) document.getElementById('manual_place_latitude').value = placeLat;
        if (placeLng) document.getElementById('manual_place_longitude').value = placeLng;
    }

    // Update hidden fields with destination data
    function updateHiddenFields(name, address, lat, lng, googlePlaceId = '', destinateId = '', isManual = false) {
        document.getElementById('place_name').value = name;
        document.getElementById('place_address').value = address;
        document.getElementById('place_latitude').value = lat;
        document.getElementById('place_longitude').value = lng;
        document.getElementById('google_place_id').value = googlePlaceId;
        document.getElementById('destinate_id').value = destinateId;
        document.getElementById('is_manual_coordinates').value = isManual ? 1 : 0;
    }

    // Function to display selected location
    function displaySelectedLocation(place, source = 'Google Places') {
        console.log('📍 Displaying selected location:', place);
        
        $('#display-name').text(place.name || 'N/A');
        $('#display-address').text(place.formatted_address || 'N/A');
        $('#display-source').text(source);
        
        if (place.geometry && place.geometry.location) {
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            $('#display-coordinates').text(lat.toFixed(6) + ', ' + lng.toFixed(6));
        } else {
            $('#display-coordinates').text('N/A');
        }
        
        $('#selected-location-details').show();
        console.log('✅ Location details should now be visible');
    }

    // Function to hide location details
    function hideLocationDetails() {
        console.log('👻 Hiding location details');
        $('#selected-location-details').hide();
        
        // Clear the display fields
        $('#display-name').text('');
        $('#display-address').text('');
        $('#display-coordinates').text('');
        $('#display-source').text('');
        
        // Clear the map
        const mapElement = document.getElementById('location-map');
        if (mapElement) {
            mapElement.innerHTML = '';
        }
    }

    // Function to clear all destination fields
    function clearDestinationFields() {
        console.log('🧹 Clearing destination fields');
        $('#place_name').val('');
        $('#place_address').val('');
        $('#place_latitude').val('');
        $('#place_longitude').val('');
        $('#google_place_id').val('');
        $('#destinate_id').val('');
        $('#destination_search').val('');
        $('#manual_place_name').val('');
        $('#manual_place_address').val('');
        $('#manual_place_latitude').val('');
        $('#manual_place_longitude').val('');
    }

    // Function to initialize and display the map
    function initMap(latitude, longitude, name, address) {
        console.log('🗺️ Initializing map with:', { latitude, longitude, name, address });
        
        const mapElement = document.getElementById('location-map');
        
        if (!mapElement) {
            console.error('❌ Map element not found!');
            return;
        }
        
        // Clear existing map if any
        mapElement.innerHTML = '';
        
        const location = { lat: latitude, lng: longitude };
        
        console.log('🗺️ Creating Google Map at:', location);
        
        // Create map
        map = new google.maps.Map(mapElement, {
            zoom: 15,
            center: location,
            mapTypeControl: false,
            streetViewControl: true,
            fullscreenControl: true,
            zoomControl: true,
            styles: [
                {
                    "featureType": "administrative",
                    "elementType": "labels.text.fill",
                    "stylers": [{"color": "#444444"}]
                },
                {
                    "featureType": "landscape",
                    "elementType": "all",
                    "stylers": [{"color": "#f2f2f2"}]
                },
                {
                    "featureType": "poi",
                    "elementType": "all",
                    "stylers": [{"visibility": "off"}]
                },
                {
                    "featureType": "road",
                    "elementType": "all",
                    "stylers": [{"saturation": -100}, {"lightness": 45}]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "all",
                    "stylers": [{"visibility": "simplified"}]
                },
                {
                    "featureType": "road.arterial",
                    "elementType": "labels.icon",
                    "stylers": [{"visibility": "off"}]
                },
                {
                    "featureType": "transit",
                    "elementType": "all",
                    "stylers": [{"visibility": "off"}]
                },
                {
                    "featureType": "water",
                    "elementType": "all",
                    "stylers": [{"color": "#46bcec"}, {"visibility": "on"}]
                }
            ]
        });
        
        // Create marker
        marker = new google.maps.Marker({
            position: location,
            map: map,
            title: name,
            animation: google.maps.Animation.DROP
        });
        
        // Create info window
        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div style="padding: 8px;">
                    <h6 style="margin: 0 0 5px 0; color: #007bff;">${name}</h6>
                    <p style="margin: 0; font-size: 12px; color: #666;">${address}</p>
                    <p style="margin: 5px 0 0 0; font-size: 11px; color: #999;">
                        Lat: ${latitude.toFixed(6)}, Lng: ${longitude.toFixed(6)}
                    </p>
                </div>
            `
        });
        
        // Open info window automatically
        infoWindow.open(map, marker);
        
        // Add click listener to marker
        marker.addListener('click', () => {
            infoWindow.open(map, marker);
        });
        
        // Add click listener to map to close info window
        map.addListener('click', () => {
            infoWindow.close();
        });
        
        console.log('✅ Map initialized successfully');
    }

    // Add click handler for manual location update button
    document.addEventListener('DOMContentLoaded', function() {
        const updateButton = document.getElementById('update-manual-location');
        if (updateButton) {
            updateButton.addEventListener('click', function() {
                updateManualLocation();
            });
        }
        
        // Also set up enter key handlers for manual fields
        const manualLatInput = document.getElementById('manual_place_latitude');
        const manualLngInput = document.getElementById('manual_place_longitude');
        
        if (manualLatInput) {
            manualLatInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    updateManualLocation();
                    return false;
                }
            });
        }
        
        if (manualLngInput) {
            manualLngInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    updateManualLocation();
                    return false;
                }
            });
        }
    });

    // Function to update location from manual coordinates
    function updateManualLocation() {
        const name = document.getElementById('manual_place_name')?.value;
        const address = document.getElementById('manual_place_address')?.value;
        const latStr = document.getElementById('manual_place_latitude')?.value;
        const lngStr = document.getElementById('manual_place_longitude')?.value;
        
        // Validate coordinates
        if (!latStr || !lngStr) {
            alert('Please enter both latitude and longitude');
            return;
        }
        
        const lat = parseFloat(latStr);
        const lng = parseFloat(lngStr);
        
        if (isNaN(lat) || isNaN(lng)) {
            alert('Please enter valid numeric coordinates');
            return;
        }
        
        if (lat < -90 || lat > 90) {
            alert('Latitude must be between -90 and 90 degrees');
            return;
        }
        
        if (lng < -180 || lng > 180) {
            alert('Longitude must be between -180 and 180 degrees');
            return;
        }
        
        // Update hidden fields
        updateHiddenFields(
            name || 'Custom Location',
            address || 'No address provided',
            lat,
            lng,
            '', // No Google Place ID for manual coordinates
            'manual_' + Date.now(), // Generate unique manual ID
            true // This is manual coordinates
        );
        
        // Create place object for display
        const place = {
            name: name || 'Custom Location',
            formatted_address: address || 'No address provided',
            geometry: {
                location: {
                    lat: function() { return lat; },
                    lng: function() { return lng; }
                }
            }
        };
        
        // Display selected location
        displaySelectedLocation(place, 'Manual Coordinates');
        
        // Update map
        initMap(lat, lng, place.name, place.formatted_address);
    }

    // Prevent form submission when pressing Enter on search field
    const destinationSearch = document.getElementById('destination_search');
    if (destinationSearch) {
        destinationSearch.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                return false;
            }
        });
    }

    // Fallback initialization
    window.addEventListener('load', function() {
        // Check if google.maps is available
        if (typeof google !== 'undefined' && google.maps) {
            // If initAutocomplete wasn't called by the callback, call it now
            if (typeof initAutocomplete === 'function') {
                setTimeout(function() {
                    initAutocomplete();
                }, 1000);
            }
        }
    });

    // Customer selection change handler
    $(document).on('change', 'select[name="customer_id"]', function() {
        const customerId = $(this).val();
        console.log('🔍 Customer selected - ID:', customerId);
        
        if (customerId) {
            console.log('🔄 Fetching destination data for customer:', customerId);
            
            // Fetch customer destination data via AJAX
            $.ajax({
                url: '{{ route("customers.getDestination") }}',
                method: 'GET',
                data: {
                    customer_id: customerId
                },
                success: function(response) {
                    console.log('✅ AJAX Success - Full Response:', response);
                    
                    if (response.success && response.has_destination) {
                        // Auto-populate destination fields
                        updateHiddenFields(
                            response.place_name,
                            response.place_address,
                            response.place_latitude,
                            response.place_longitude,
                            response.google_place_id,
                            response.destinate_id,
                            !response.google_place_id // isManual = true if no google_place_id
                        );
                        
                        // Update the search field display
                        $('#destination_search').val(response.place_name || response.place_address);
                        
                        // Copy to manual fields if needed
                        copyToManualFields();
                        
                        // Display the location details and map
                        const place = {
                            name: response.place_name,
                            formatted_address: response.place_address,
                            geometry: {
                                location: {
                                    lat: function() { return parseFloat(response.place_latitude); },
                                    lng: function() { return parseFloat(response.place_longitude); }
                                }
                            }
                        };
                        
                        const source = response.google_place_id ? 'Google Places' : 'Manual Coordinates';
                        displaySelectedLocation(place, source);
                        
                        initMap(
                            parseFloat(response.place_latitude), 
                            parseFloat(response.place_longitude), 
                            response.place_name, 
                            response.place_address
                        );
                        
                        // Auto-switch to manual mode if no Google Place ID
                        if (!response.google_place_id) {
                            const manualButton = document.querySelector('#destination-toggle button[data-value="manual"]');
                            if (manualButton) {
                                manualButton.click();
                            }
                        }
                        
                    } else {
                        // Customer has no destination - clear all fields and hide location details
                        clearDestinationFields();
                        hideLocationDetails();
                    }
                },
                error: function(xhr, status, error) {
                    clearDestinationFields();
                    hideLocationDetails();
                }
            });
        } else {
            // No customer selected - clear all fields
            clearDestinationFields();
            hideLocationDetails();
        }
    });
    </script>
@endpush

<div class="modal fade" id="infoModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="infoModelLabel">Modal title</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group col-sm-12">
                <label for="dono">DO Number:</label>
                <input class="form-control" type="text" disabled value="value">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>