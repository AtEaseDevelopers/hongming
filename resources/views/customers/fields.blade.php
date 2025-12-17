<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', __('customers.code')) !!}:<span class="asterisk"> *</span>
    {!! Form::text('code', null, ['class' => 'form-control', 'maxlength' => 255, 'autofocus']) !!}
</div>

<!-- Company Field -->
<div class="form-group col-sm-6">
    {!! Form::label('company', __('customers.company')) !!}:<span class="asterisk"> *</span>
    {!! Form::text('company', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', __('customers.phone')) !!}:
    {!! Form::text('phone', null, ['class' => 'form-control', 'maxlength' => 20]) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', __('customers.address')) !!}:
    {!! Form::text('address', null, ['class' => 'form-control', 'maxlength' => 65535]) !!}
</div>

<!-- Google Places Destination Field -->
<div class="form-group col-sm-7">
    {!! Form::label('destination_search', 'Default Destination:') !!}
    
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
        {!! Form::text('destination_search', old('destination_search', isset($customer) ? ($customer->place_name ?? $customer->place_address) : null), [
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
                    {!! Form::text('manual_place_name', isset($customer) ? $customer->place_name : null, [
                        'class' => 'form-control',
                        'id' => 'manual_place_name',
                        'placeholder' => 'Enter location name'
                    ]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('manual_place_address', 'Location Address:') !!}
                    {!! Form::text('manual_place_address', isset($customer) ? $customer->place_address : null, [
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
                    {!! Form::text('manual_place_latitude', isset($customer) ? $customer->place_latitude : null, [
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
                    {!! Form::text('manual_place_longitude', isset($customer) ? $customer->place_longitude : null, [
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
    {!! Form::hidden('place_name', isset($customer) ? $customer->place_name : null, ['id' => 'place_name']) !!}
    {!! Form::hidden('place_address', isset($customer) ? $customer->place_address : null, ['id' => 'place_address']) !!}
    {!! Form::hidden('place_latitude', isset($customer) ? $customer->place_latitude : null, ['id' => 'place_latitude']) !!}
    {!! Form::hidden('place_longitude', isset($customer) ? $customer->place_longitude : null, ['id' => 'place_longitude']) !!}
    {!! Form::hidden('google_place_id', isset($customer) ? $customer->google_place_id : null, ['id' => 'google_place_id']) !!}
    {!! Form::hidden('destinate_id', isset($customer) ? $customer->destinate_id : null, ['id' => 'destinate_id']) !!}
    {!! Form::hidden('is_manual_coordinates', 0, ['id' => 'is_manual_coordinates']) !!}
</div>

<!-- Display selected location details -->
<div id="selected-location-details" class="mt-3 p-3 pb-4 border rounded" style="{{ (isset($customer) && $customer->hasDestination()) ? 'display: block;' : 'display: none;' }};">
    <div class="row">
        <div class="col-md-6">
            <h6>📍 Selected Location:</h6>
            <p><strong>Name:</strong> <span id="display-name">{{ isset($customer) ? ($customer->place_name ?? 'N/A') : 'N/A' }}</span></p>
            <p><strong>Address:</strong> <span id="display-address">{{ isset($customer) ? ($customer->place_address ?? 'N/A') : 'N/A' }}</span></p>
            <p><strong>Coordinates:</strong> <span id="display-coordinates">
                @if(isset($customer) && $customer->place_latitude && $customer->place_longitude)
                    {{ number_format($customer->place_latitude, 6) }}, {{ number_format($customer->place_longitude, 6) }}
                @else
                    N/A
                @endif
            </span></p>
            <p><strong>Source:</strong> <span id="display-source">{{ isset($customer) && $customer->google_place_id ? 'Google Places' : 'Manual Coordinates' }}</span></p>
        </div>
        <div class="col-md-6">
            <h6>🗺️ Location Map:</h6>
            <div id="location-map" style="height: 200px; width: 100%; border-radius: 4px; border: 1px solid #ddd;"></div>
            <small class="text-muted">Interactive map showing the exact location</small>
        </div>
    </div>
</div>

<!-- Sst Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sst', __('customers.ssm')) !!}:
    {!! Form::text('sst', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Tin Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tin', __('customers.tin')) !!}:
    {!! Form::text('tin', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', __('customers.status')) !!}:<span class="asterisk"> *</span>
    {{ Form::select('status', [
        1 => __('customers.active'),
        0 => __('customers.unactive'),
    ], null, ['class' => 'form-control']) }}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('customers.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">{{ __('customers.cancel') }}</a>
</div>

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
        $(document).ready(function () {
            HideLoad();
        });
    </script>

    <!-- Load Google Maps JavaScript API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBP7mUubx-qS7U0V4WUW6u9jIWp3NNCIsE&libraries=places&callback=initCustomerAutocomplete" async defer></script>

    <script>
    // Global variables for map and marker
    let customerMap;
    let customerMarker;
    let customerAutocomplete;
    let isManualMode = false;

    // Initialize Google Places Autocomplete for customer form
    function initCustomerAutocomplete() {
        var input = document.getElementById('destination_search');
        
        if (!input) {
            console.error('Destination search input not found');
            return;
        }
        
        // Create autocomplete instance
        customerAutocomplete = new google.maps.places.Autocomplete(input, {
            types: ['establishment', 'geocode'],
            fields: ['name', 'formatted_address', 'geometry', 'place_id', 'types']
        });
        
        // When a place is selected
        customerAutocomplete.addListener('place_changed', function() {
            var place = customerAutocomplete.getPlace();
            
            if (!place.geometry) {
                alert("No details available for this location: '" + place.name + "'");
                return;
            }
            
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
            displayCustomerSelectedLocation(place, 'Google Places');
            
            // Initialize and display the map
            initCustomerMap(place.geometry.location.lat(), place.geometry.location.lng(), place.name, place.formatted_address);
        });

        // Initialize destination method toggle
        initDestinationToggle();
        
        // Initialize with existing data if editing
        initializeWithExistingData();
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

    // Initialize with existing data if editing
    function initializeWithExistingData() {
        let placeName = document.getElementById('place_name')?.value;
        let placeAddress = document.getElementById('place_address')?.value;
        let lat = document.getElementById('place_latitude')?.value;
        let lng = document.getElementById('place_longitude')?.value;

        if (placeName && placeAddress && lat && lng) {
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

            // Determine source
            const googlePlaceId = document.getElementById('google_place_id')?.value;
            const source = googlePlaceId ? 'Google Places' : 'Manual Coordinates';
            
            displayCustomerSelectedLocation(place, source);
            initCustomerMap(parseFloat(lat), parseFloat(lng), placeName, placeAddress);
        }
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

    // Initialize and display the map for customer form
    function initCustomerMap(latitude, longitude, name, address) {
        const mapElement = document.getElementById('location-map');
        
        if (!mapElement) {
            console.error('Location map element not found');
            return;
        }
        
        // Clear previous map if exists
        mapElement.innerHTML = '';
        
        const location = { lat: latitude, lng: longitude };
        
        customerMap = new google.maps.Map(mapElement, {
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
        
        customerMarker = new google.maps.Marker({
            position: location,
            map: customerMap,
            title: name,
            animation: google.maps.Animation.DROP
        });
        
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
        
        infoWindow.open(customerMap, customerMarker);
        
        customerMarker.addListener('click', () => {
            infoWindow.open(customerMap, customerMarker);
        });
        
        customerMap.addListener('click', () => {
            infoWindow.close();
        });
    }

    // Display selected location details for customer form
    function displayCustomerSelectedLocation(place, source = 'Google Places') {
        document.getElementById('display-name').textContent = place.name || 'N/A';
        document.getElementById('display-address').textContent = place.formatted_address || 'N/A';
        document.getElementById('display-coordinates').textContent = 
            place.geometry.location.lat().toFixed(6) + ', ' + place.geometry.location.lng().toFixed(6);
        document.getElementById('display-source').textContent = source;
        
        document.getElementById('selected-location-details').style.display = 'block';
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
        displayCustomerSelectedLocation(place, 'Manual Coordinates');
        
        // Update map
        initCustomerMap(lat, lng, place.name, place.formatted_address);
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
            // If initCustomerAutocomplete wasn't called by the callback, call it now
            if (typeof initCustomerAutocomplete === 'function') {
                setTimeout(function() {
                    initCustomerAutocomplete();
                }, 1000);
            }
        }
    });
    </script>
@endpush