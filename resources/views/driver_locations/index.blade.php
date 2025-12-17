@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">{{ __('driver_locations.driver_locations')}}</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
             @include('flash::message')
             
             <!-- Map and Details Section -->
             <div class="row">
                 <!-- Map Column -->
                 <div class="col-lg-10">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fa fa-map-marker"></i>
                            {{ __('driver_locations.summary')}}
                            <div class="card-header-actions">
                                <button id="refreshMap" class="btn btn-sm btn-primary mr-2">
                                    <i class="fa fa-refresh"></i> Refresh Map
                                </button>
                                <button id="showAllDrivers" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-globe"></i> Show All Drivers
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0 d-flex flex-column">
                            <div id="map" style="height: 600px; width: 100%; flex: 1;"></div>
                            <div class="p-3 border-top">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i> 
                                    Click on driver markers to see details. Map updates automatically every 2 minutes.
                                </small>
                            </div>
                        </div>
                    </div>
                 </div>

                 <!-- Driver Details Column -->
                 <div class="col-lg-2">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fa fa-users"></i>
                            All Driver Details
                            <span class="badge badge-info ml-2" id="driverCount">0</span>
                        </div>
                        <div class="card-body p-0 d-flex flex-column">
                            <div id="allDriverDetails" style="height: 600px; overflow-y: auto;">
                                <div class="text-center p-4 text-muted">
                                    <i class="fa fa-spinner fa-spin"></i> Loading driver details...
                                </div>
                            </div>
                        </div>
                    </div>
                 </div>
                <div class="col-lg-12" style="margin-top: 20px;">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-align-justify"></i>
                            {{ __('Driver Locations')}}
                            <div class="card-header-actions">
                                <span class="badge badge-info mr-2" id="selectedCount">0 selected</span>
                                <button id="findDriversOnMap" class="btn btn-sm btn-primary mr-2">
                                    <i class="fa fa-search"></i> {{ __('driver_locations.find_drivers_on_map')}}
                                </button>
                                <button id="clearSelection" class="btn btn-sm btn-warning">
                                    <i class="fa fa-times"></i> Clear Selection
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('driver_locations.table')
                        </div>
                    </div>
                </div>
                
             </div>
         </div>
    </div>
@endsection

@push('styles')
<style>
    .map-legend {
        position: absolute;
        bottom: 10px;
        left: 10px;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
    }
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        padding: 2px 0;
        cursor: pointer;
    }
    .legend-item:hover {
        background-color: #f8f9fa;
    }
    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        margin-right: 8px;
        border: 2px solid #fff;
        flex-shrink: 0;
    }
    .legend-driver-name {
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }
    .driver-detail-card {
        border-left: 4px solid;
        margin: 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
        background: white;
    }
    .driver-detail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .driver-detail-header {
        padding: 10px 12px;
        color: white;
        border-radius: 4px 0 0 0;
    }
    .driver-detail-content {
        padding: 12px;
        background-color: #f8f9fa;
    }
    .driver-detail-item {
        margin-bottom: 6px;
        display: flex;
        align-items: flex-start;
    }
    .driver-detail-label {
        font-weight: bold;
        font-size: 11px;
        color: #555;
        min-width: 80px;
        flex-shrink: 0;
    }
    .driver-detail-value {
        font-size: 11px;
        color: #333;
        flex: 1;
    }
    .driver-active {
        border-left-color: #28a745;
    }
    .driver-inactive {
        border-left-color: #6c757d;
    }
    .no-drivers-message {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    .driver-status-badge {
        font-size: 10px;
        padding: 2px 6px;
    }
    .card.h-100 {
        height: 100%;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let map;
    let markers = [];
    let infoWindow;
    let autoRefreshInterval;
    let allDriverData = [];
    let driverColors = {};

    // Extended color palette for driver markers
    const colorPalette = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', 
        '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
        '#F8C471', '#82E0AA', '#F1948A', '#D7BDE2', '#F9E79F',
        '#ABEBC6', '#AED6F1', '#FAD7A0', '#E8DAEF', '#A3E4D7'
    ];

    // Initialize the map
    function initMap() {
        const defaultCenter = { lat: 3.1390, lng: 101.6869 };
        
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 11,
            center: defaultCenter,
            mapTypeControl: true,
            streetViewControl: false,
            fullscreenControl: true,
            styles: [
                {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }
            ]
        });

        infoWindow = new google.maps.InfoWindow();
        loadDriverLocations();
        autoRefreshInterval = setInterval(loadDriverLocations, 120000);
    }

    function updateMapLegend(drivers) {
        // Remove existing legend if any
        const existingLegend = document.querySelector('.map-legend');
        if (existingLegend) {
            existingLegend.remove();
        }

        const legend = document.createElement('div');
        legend.className = 'map-legend';
        legend.innerHTML = '<h6 style="margin-bottom: 8px; font-size: 14px;">Drivers</h6>';

        // Add each driver to legend
        drivers.forEach((driver, index) => {
            const driverName = driver[1];
            const driverId = driver[2];
            const color = getDriverColor(driverId);
            
            const legendItem = document.createElement('div');
            legendItem.className = 'legend-item';
            legendItem.innerHTML = `
                <div class="legend-color" style="background-color: ${color};"></div>
                <div class="legend-driver-name" title="${driverName}">${driverName}</div>
            `;
            
            // Click on legend item to focus on driver
            legendItem.addEventListener('click', function() {
                const marker = markers.find(m => m.driverData.id === driverId);
                if (marker) {
                    map.setCenter(marker.getPosition());
                    map.setZoom(15);
                    infoWindow.close();
                    infoWindow.setContent(createInfoContent(marker.driverData));
                    infoWindow.open(map, marker);
                    
                    marker.setAnimation(google.maps.Animation.BOUNCE);
                    setTimeout(() => {
                        marker.setAnimation(null);
                    }, 1500);
                }
            });
            
            legend.appendChild(legendItem);
        });

        map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(legend);
    }

    function getDriverColor(driverId) {
        if (!driverColors[driverId]) {
            const colorIndex = Object.keys(driverColors).length % colorPalette.length;
            driverColors[driverId] = colorPalette[colorIndex];
        }
        return driverColors[driverId];
    }

    function clearMarkers() {
        markers.forEach(marker => marker.setMap(null));
        markers = [];
    }

    function loadDriverLocations() {
        ShowLoad();
        
        $.ajax({
            type: "GET",
            url: "{{ route('driverLocations.getDriverSummary') }}",
            cache: false,
            success: function(data) {
                allDriverData = data;
                clearMarkers();
                addMarkersToMap(data);
                updateMapLegend(data);
                updateAllDriverDetails(data);
                HideLoad();
            },
            error: function(jqXHR, status, error) {
                console.error('Error loading driver locations:', error);
                noti('e', 'Error', 'Failed to load driver locations');
                HideLoad();
            }
        });
    }

    function updateAllDriverDetails(drivers) {
        const container = $('#allDriverDetails');
        const driverCount = $('#driverCount');
        
        driverCount.text(drivers.length);
        
        if (drivers.length === 0) {
            container.html(`
                <div class="no-drivers-message">
                    <i class="fa fa-users fa-3x mb-3"></i>
                    <p>No driver locations found</p>
                </div>
            `);
            return;
        }

        let html = '';
        
        drivers.forEach((driver, index) => {
            const position = driver[0];
            const driverName = driver[1];
            const driverId = driver[2];
            const lorryNo = driver[3];
            const date = driver[4];
            const color = getDriverColor(driverId);
            
            const locationDate = new Date(date);
            const today = new Date();
            const isToday = locationDate.toDateString() === today.toDateString();
            const statusClass = isToday ? 'driver-active' : 'driver-inactive';
            const statusBadgeClass = isToday ? 'badge-success' : 'badge-secondary';
            
            html += `
                <div class="driver-detail-card ${statusClass}" data-driver-id="${driverId}">
                    <div class="driver-detail-header" style="background: linear-gradient(135deg, ${color} 0%, ${darkenColor(color, 20)} 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong style="font-size: 13px;">${driverName}</strong>
                        </div>
                        <small style="opacity: 0.9;">${lorryNo}</small>
                    </div>
                    <div class="driver-detail-content">
                        <div class="driver-detail-item">
                            <div class="driver-detail-label">Lorry:</div>
                            <div class="driver-detail-value">${lorryNo}</div>
                        </div>
                        <div class="driver-detail-item">
                            <div class="driver-detail-label">Last Update:</div>
                            <div class="driver-detail-value">${date}</div>
                        </div>
                        <div class="driver-detail-item">
                            <div class="driver-detail-label">Coordinates:</div>
                            <div class="driver-detail-value">${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}</div>
                        </div>
                        <div class="text-center mt-2">
                            <button class="btn btn-sm btn-outline-primary focus-driver" data-driver-id="${driverId}" style="border-color: ${color}; color: ${color}; font-size: 11px;">
                                <i class="fa fa-search"></i> Focus on Map
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
        
        // Add click handlers for focus buttons
        $('.focus-driver').on('click', function() {
            const driverId = $(this).data('driver-id');
            focusOnDriver(driverId);
        });
    }

    function focusOnDriver(driverId) {
        const marker = markers.find(m => m.driverData.id === driverId);
        if (marker) {
            map.setCenter(marker.getPosition());
            map.setZoom(15);
            infoWindow.close();
            infoWindow.setContent(createInfoContent(marker.driverData));
            infoWindow.open(map, marker);
            
            marker.setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(() => {
                marker.setAnimation(null);
            }, 1500);
        }
    }

    function createInfoContent(driverData) {
        const locationDate = new Date(driverData.date);
        const today = new Date();
        const isToday = locationDate.toDateString() === today.toDateString();
        
        return `
            <div style="min-width: 250px; padding: 5px;">
                <h6 style="margin-bottom: 8px; color: ${driverData.color};">
                    <i class="fa fa-user"></i> ${driverData.name}
                </h6>
                <div style="font-size: 14px;">
                    <div><strong>Lorry:</strong> ${driverData.lorryNo}</div>
                    <div><strong>Last Update:</strong> ${driverData.date}</div>
                    <div><strong>Location:</strong> ${driverData.position.lat.toFixed(6)}, ${driverData.position.lng.toFixed(6)}</div>
                </div>
                <div class="text-center mt-2">
                    <a href="https://www.google.com/maps?q=${driverData.position.lat},${driverData.position.lng}" 
                       target="_blank" class="btn btn-sm btn-outline-primary" style="border-color: ${driverData.color}; color: ${driverData.color};">
                        <i class="fa fa-external-link"></i> Open in Google Maps
                    </a>
                </div>
            </div>
        `;
    }

    function addMarkersToMap(drivers) {
        const bounds = new google.maps.LatLngBounds();
        
        drivers.forEach((driver, index) => {
            const position = driver[0];
            const driverName = driver[1];
            const driverId = driver[2];
            const lorryNo = driver[3];
            const date = driver[4];
            
            const markerColor = getDriverColor(driverId);
            
            const marker = new google.maps.Marker({
                position: position,
                map: map,
                title: `${driverName} (${lorryNo})`,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 12,
                    fillColor: markerColor,
                    fillOpacity: 0.9,
                    strokeColor: '#ffffff',
                    strokeWeight: 3,
                }
            });

            // Store driver data in marker
            marker.driverData = {
                name: driverName,
                id: driverId,
                lorryNo: lorryNo,
                date: date,
                position: position,
                color: markerColor
            };

            marker.addListener('click', () => {
                infoWindow.close();
                infoWindow.setContent(createInfoContent(marker.driverData));
                infoWindow.open(map, marker);
            });

            markers.push(marker);
            bounds.extend(position);
        });

        if (drivers.length > 0) {
            map.fitBounds(bounds);
            if (map.getZoom() > 15) {
                map.setZoom(15);
            }
        } else {
            map.setCenter({ lat: 3.1390, lng: 101.6869 });
            map.setZoom(11);
        }
    }

    // Helper function to darken color
    function darkenColor(color, percent) {
        const num = parseInt(color.replace("#", ""), 16);
        const amt = Math.round(2.55 * percent);
        const R = (num >> 16) - amt;
        const G = (num >> 8 & 0x00FF) - amt;
        const B = (num & 0x0000FF) - amt;
        return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
    }

    // Refresh map button
    $('#refreshMap').on('click', function() {
        loadDriverLocations();
        noti('s', 'Success', 'Map refreshed successfully');
    });

    // Show all drivers button
    $('#showAllDrivers').on('click', function() {
        clearMarkers();
        addMarkersToMap(allDriverData);
        updateMapLegend(allDriverData);
        updateAllDriverDetails(allDriverData);
        noti('s', 'Success', 'Showing all drivers on map');
    });

    // Initialize map
    if (typeof google !== 'undefined') {
        initMap();
    } else {
        window.initMap = initMap;
    }

    // Clean up
    $(window).on('beforeunload', function() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    });
});
</script>

<!-- Include Google Maps API -->
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ config('app.google_api') }}&callback=initMap&libraries=geometry">
</script>
@endpush